<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Component;
use App\Models\InventoryPurchaseOrder;
use App\Models\PoDetail;
use App\Models\PoDelivery;
use App\Models\PoDeliveryItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryPurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending'); // default pending

        $purchaseOrders = InventoryPurchaseOrder::with(['user', 'supplier', 'approvedBy', 'archivedBy'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('inventory_purchase_orders.index', compact('purchaseOrders', 'status'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $users = User::where('status', 'active')->get();
        $branches = Branch::all();

        // Get current branch
        $currentBranchId = current_branch_id();

        // ✅ Fetch components + quantity from branch_components
        $components = Component::with(['supplier', 'category', 'unit'])
            ->leftJoin('branch_components', function ($join) use ($currentBranchId) {
                $join->on('components.id', '=', 'branch_components.component_id')
                    ->where('branch_components.branch_id', '=', $currentBranchId);
            })
            ->select(
                'components.*',
                'branch_components.onhand as branch_quantity'
            )
            ->get();

        return view('inventory_purchase_orders.create', compact(
            'suppliers',
            'users',
            'components',
            'branches',
            'currentBranchId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'department' => 'nullable|string|max:255',
            'prf_reference_number' => 'nullable|string|max:255',
            'type_of_request' => 'nullable|string|max:255',
            'select_origin' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:components,id',
            'components.*.unit_cost' => 'required|numeric|min:0',
            'components.*.qty' => 'required|integer|min:1',
            'attachment' => 'nullable|file|max:5120', // optional, max 5MB
        ]);

        // 🔹 Use branch_id in PO numbering pattern: PO-[BranchID]-000001
        $branchId = current_branch_id();

        // 🔹 Find last numeric sequence for this branch robustly (handles leading zeros)
        $maxSeq = \DB::table('inventory_purchase_orders')
            ->where('po_number', 'like', 'PO-' . $branchId . '-%')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(po_number, '-', -1) AS UNSIGNED)) as max_seq")
            ->value('max_seq');

        $nextNumber = ($maxSeq ? (int) $maxSeq + 1 : 1);
        $validated['po_number'] = 'PO-' . $branchId . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        $validated['status'] = 'pending';

        // 🔹 Handle file upload if exists
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('purchase_orders', 'public');
            $validated['attachment'] = $filePath;
        }

        // 🔹 TAX RATE (changeable)
        $taxRate = 0.12;

        // 🔹 Create the Purchase Order
        $purchaseOrder = InventoryPurchaseOrder::create($validated);

        $components = Component::with('branchStocks')->whereIn('id', collect($request->components)->pluck('id'))->get()->keyBy('id');

        // 🔹 Loop through components added. Do NOT increase component onhand here since PO is still pending.
        foreach ($request->components as $compData) {
            $component = $components[$compData['id']];

            $qty = (int) $compData['qty'];
            $unitCost = (float) $compData['unit_cost'];

            // ✅ Get branch stock
            $branchStock = $component->branchStocks()
                ->where('branch_id', $branchId)
                ->first();

            // ✅ If not exists, create it
            if (!$branchStock) {
                $branchStock = $component->branchStocks()->firstOrCreate(
                    ['branch_id' => $branchId],
                    ['onhand' => 0]
                );
            }

            $onhand = $branchStock->onhand;

            // ✅ Compute
            $subTotal = $qty * $unitCost;
            $tax = $subTotal * $taxRate;

            // ✅ Save
            $purchaseOrder->details()->create([
                'component_id' => $component->id,
                'qty' => $qty,
                'unit_cost' => $unitCost,
                'tax' => $tax,
                'sub_total' => $subTotal,
                'onhand' => $onhand,
            ]);

            $component->update([
                'cost' => $unitCost,
            ]);
        }
        return redirect()->route('inventory_purchase_orders.index')
            ->with('success', 'Purchase Order created successfully with attachment and components updated.');
    }

    public function show($id)
    {
        $purchaseOrder = InventoryPurchaseOrder::with([
            'supplier',
            'user',
            'details.component'
        ])->findOrFail($id);

        return view('inventory_purchase_orders.show', compact('purchaseOrder'));
    }

    public function getDetails($id)
{
    $po = InventoryPurchaseOrder::with([
        'details.component.unit',
        'details.component.supplier',
        'details.component.category',
        'user',
        'supplier'
    ])->findOrFail($id);

    // Transform details so frontend has exactly what it expects
    $poArray = $po->toArray(); // convert to array first

    $poArray['details'] = collect($poArray['details'])->map(function ($d) {
        return [
            'component' => [
                'name' => $d['component']['name'] ?? '—',
                'code' => $d['component']['code'] ?? '—',
                'unit' => $d['component']['unit']['name'] ?? '—',
            ],
            'qty' => $d['qty'] ?? 0,
            'unit_cost' => $d['unit_cost'] ?? 0,
            'sub_total' => $d['sub_total'] ?? 0,
        ];
    });

    return response()->json($poArray);
}

    public function edit($id)
    {
        $purchaseOrder = InventoryPurchaseOrder::with([
            'details.component' => function ($query) {
                $query->with([
                    'unit',
                    'supplier',
                    'category',
                    'branchStockForCurrent' // ✅ load branch stocks from Component model
                ]);
            },
            'supplier',
            'user'
        ])->findOrFail($id);

        $suppliers = Supplier::where('status', 'active')->get();
        $users = User::where('status', 'active')->get();

        // all components for selection
        $components = Component::with(['unit', 'supplier', 'category', 'branchStockForCurrent'])->get();

        return view('inventory_purchase_orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'users',
            'components'
        ));
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = InventoryPurchaseOrder::findOrFail($id);

        // ✅ Validate input (matches store)
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'department' => 'nullable|string|max:255',
            'prf_reference_number' => 'nullable|string|max:255',
            'type_of_request' => 'nullable|string|max:255',
            'select_origin' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',

            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:components,id',
            'components.*.unit_cost' => 'required|numeric|min:0',
            'components.*.qty' => 'required|integer|min:1',

            'attachment' => 'nullable|file|max:5120',
        ]);

        $branchId = current_branch_id();
        $taxRate = 0.12;

        // 🔹 Handle attachment
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('purchase_orders', 'public');
            $validated['attachment'] = $filePath;
        }

        // 🔹 Update main PO
        $purchaseOrder->update($validated);

        // 🔹 Preload existing details keyed by component_id
        $existingDetails = $purchaseOrder->details()->get()->keyBy('component_id');

        // 🔹 Preload components keyed by ID
        $submittedComponents = collect($request->components)->keyBy('id');
        $components = Component::with('branchStocks')
            ->whereIn('id', $submittedComponents->keys())
            ->get()
            ->keyBy('id');

        // 🔹 Loop through submitted components
        foreach ($submittedComponents as $compId => $compData) {
            $component = $components[$compId];

            $qty = (int) $compData['qty'];
            $unitCost = (float) $compData['unit_cost'];

            // ✅ Get or create branch stock
            $branchStock = $component->branchStocks()->firstOrCreate(
                ['branch_id' => $branchId],
                ['onhand' => 0]
            );
            $onhand = $branchStock->onhand;

            $subTotal = $qty * $unitCost;
            $tax = $subTotal * $taxRate;

            if ($existingDetails->has($compId)) {
                // ✅ Update only if values changed
                $detail = $existingDetails[$compId];
                if (
                    $detail->qty != $qty ||
                    $detail->unit_cost != $unitCost ||
                    $detail->tax != $tax ||
                    $detail->sub_total != $subTotal ||
                    $detail->onhand != $onhand
                ) {
                    $detail->update([
                        'qty' => $qty,
                        'unit_cost' => $unitCost,
                        'tax' => $tax,
                        'sub_total' => $subTotal,
                        'onhand' => $onhand,
                    ]);
                }

                // ✅ Remove from $existingDetails to mark as processed
                $existingDetails->forget($compId);

            } else {
                // ✅ Insert new detail
                $purchaseOrder->details()->create([
                    'component_id' => $compId,
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'tax' => $tax,
                    'sub_total' => $subTotal,
                    'onhand' => $onhand,
                ]);
            }

            // ✅ Update latest cost
            $component->update(['cost' => $unitCost]);
        }

        // 🔹 Delete removed components
        foreach ($existingDetails as $detail) {
            $detail->delete();
        }

        return redirect()->route('inventory_purchase_orders.index')
            ->with('success', 'Purchase Order updated successfully.');
    }

    public function uploadAttachments(Request $request)
    {
        $validated = $request->validate([
            'po_id' => 'required|exists:inventory_purchase_orders,id',
            'attachments.*' => 'required|file|max:5120', // 5MB each
        ]);

        $purchaseOrder = InventoryPurchaseOrder::findOrFail($validated['po_id']);
        $uploadedFiles = [];

        // Handle each uploaded file
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('purchase_order_attachments', 'public');
                $uploadedFiles[] = $path;
            }
        }

        // If you only had 1 attachment before, let's store multiple in JSON
        $existing = $purchaseOrder->attachments ? json_decode($purchaseOrder->attachments, true) : [];
        $allFiles = array_merge($existing, $uploadedFiles);

        $purchaseOrder->update([
            'attachments' => json_encode($allFiles),
        ]);

        return back()->with('success', 'Files attached successfully.');
    }

    public function getAttachments($id)
    {
        $po = \App\Models\InventoryPurchaseOrder::findOrFail($id);

        // Make sure we're using the correct column name: `attachments`
        $attachments = [];

        if ($po->attachments) {
            $decoded = json_decode($po->attachments, true);

            if (is_array($decoded)) {
                $attachments = $decoded;
            } else {
                // Handle case where it’s just a string
                $attachments = [$po->attachments];
            }
        }

        return response()->json(['attachments' => $attachments]);
    }

    public function approve($id)
    {
        $po = InventoryPurchaseOrder::findOrFail($id);
            $po->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

        return redirect()->route('inventory_purchase_orders.index')
            ->with('success', 'Purchase Order approved successfully.');
    }

    public function disapprove($id)
    {
        $po = InventoryPurchaseOrder::findOrFail($id);
        $po->update([
            'status' => 'disapproved',
            'approved_by' => auth()->id(), // optionally track who disapproved
            'approved_at' => now(),
        ]);

        return redirect()->route('inventory_purchase_orders.index')
            ->with('warning', 'Purchase Order disapproved.');
    }

    public function getInvoiceData($id)
    {
        $po = \App\Models\InventoryPurchaseOrder::with(['details.component', 'user', 'supplier'])->find($id);

        if (!$po) {
            return response()->json(['message' => 'PO not found'], 404);
        }

        return response()->json($po);
    }

    public function archive($id)
    {
        $po = InventoryPurchaseOrder::findOrFail($id);
        
        $po->update([
            'status' => 'archived',
            'archived_by' => auth()->id(),
            'archived_at' => now(),
        ]);

        return redirect()->route('inventory_purchase_orders.index', ['status' => 'archived'])
            ->with('warning', 'Purchase Order moved to archive.');
    }
    
    /**
     * Store logged stock receipts for a PO.
     * Accepts: date_of_receipt (nullable), delivery_dr (nullable), items: [{detail_id, qty_received}]
     */
    public function storeLogStocks(Request $request, $id)
    {
        $validated = $request->validate([
            'date_of_receipt' => 'nullable|date',
            'delivery_dr' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.detail_id' => 'required|exists:po_details,id',
            'items.*.qty_received' => 'required|integer|min:0',
        ]);

        $po = InventoryPurchaseOrder::with('details')->findOrFail($id);

        DB::beginTransaction();
        try {
            // update top-level delivery_dr only if the column exists on the model
            if (!empty($validated['delivery_dr']) && array_key_exists('delivery_dr', $po->getAttributes())) {
                $po->delivery_dr = $validated['delivery_dr'];
            }

            if (!empty($validated['date_of_receipt'])) {
                // store as received_at if column exists, otherwise leave
                if (array_key_exists('received_at', $po->getAttributes())) {
                    $po->received_at = $validated['date_of_receipt'];
                }
            }
            // Create delivery header for this submission. Use delivery_dr from payload and ensure uniqueness.
            $deliveryReceipt = $validated['delivery_dr'] ?? ($validated['delivery_receipt'] ?? null);
            if (! $deliveryReceipt) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Delivery receipt is required.'], 422);
            }

            if (PoDelivery::where('delivery_receipt', $deliveryReceipt)->exists()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Delivery receipt already exists.'], 422);
            }

            $poDelivery = PoDelivery::create([
                'inventory_purchase_order_id' => $po->id,
                'user_id' => auth()->id() ?? null,
                'delivery_receipt' => $deliveryReceipt,
                'received_at' => $validated['date_of_receipt'] ?? now(),
            ]);

            foreach ($validated['items'] as $it) {
                $detail = PoDetail::where('id', $it['detail_id'])
                    ->where('inventory_purchase_order_id', $po->id)
                    ->first();

                if (! $detail) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'PO detail not found or does not belong to PO.'], 404);
                }

                $qtyReceived = max(0, (int) $it['qty_received']);

                // don't allow received_qty to exceed requested qty
                $remaining = max(0, $detail->qty - ($detail->received_qty ?? 0));
                $toAdd = min($qtyReceived, $remaining);

                if ($toAdd <= 0) {
                    // nothing to add for this line
                    continue;
                }

                // record delivery item
                PoDeliveryItem::create([
                    'po_delivery_id' => $poDelivery->id,
                    'po_detail_id' => $detail->id,
                    'component_id' => $detail->component_id,
                    'qty_received' => $toAdd,
                ]);

                $detail->received_qty = ($detail->received_qty ?? 0) + $toAdd;
                $detail->save();

                // increase component onhand
                $component = Component::find($detail->component_id);
                if ($component) {
                    $component->onhand = ($component->onhand ?? 0) + $toAdd;
                    $component->save();
                }
            }

            // reload details and determine if ALL lines on the PO are fully received
            $po->load('details');
            $allReceived = $po->details->every(function ($d) {
                return (int) ($d->received_qty ?? 0) >= (int) ($d->qty ?? 0);
            });

            if ($allReceived) {
                $po->status = 'completed';
            }

            $po->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Stocks logged successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to log stocks: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to log stocks.'], 500);
        }
    }

    public function generateNextDrNumber(Request $request)
    {
        $branchId = $request->get('branch_id');

        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'Branch ID is required.'], 422);
        }

        // Find the latest DR number for this branch
        $latestDr = \App\Models\PoDelivery::where('delivery_receipt', 'like', 'DR-' . $branchId . '-%')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(delivery_receipt, '-', -1) AS UNSIGNED)) as max_seq")
            ->value('max_seq');

        $nextSeq = $latestDr ? ((int) $latestDr + 1) : 1;
        $nextDrNumber = 'DR-' . $branchId . '-' . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'next_dr_number' => $nextDrNumber,
        ]);
    }

}
