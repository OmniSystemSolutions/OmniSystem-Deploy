<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Component;
use App\Models\InventoryPurchaseOrder;
use App\Models\ProcurementRequest;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementRequestController extends Controller
{
    public function index()
    {
        return view('reports.procurement-request.index');
    }

    public function fetchRequests(Request $request)
{
    $prfs = ProcurementRequest::with([
            'createdBy:id,name',
            'requestedBy.employeeWorkInformations.department', // load relationship properly
            'requestingBranch:id,name'
        ])
        ->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        })
        ->when($request->filled('search'), function ($q) use ($request) {
            $q->where('reference_no', 'like', '%' . $request->search . '%')
              ->orWhere('proforma_reference_no', 'like', '%' . $request->search . '%');
        })
        ->orderBy('created_at', 'desc')
        ->paginate($request->per_page ?? 10);

    // Transform for Vue table
    $prfs->getCollection()->transform(function($prf) {
        // Get latest department via employeeWorkInformations
        $department = optional($prf->requestedBy->employeeWorkInformations->last()?->department)->name;

        return [
            'id' => $prf->id,
            'requested_datetime' => $prf->created_at->format('Y-m-d H:i'),
            'created_by' => optional($prf->createdBy)->name,
            'requested_by' => optional($prf->requestedBy)->name,
            'department' => $department,
            'prf_reference_no' => $prf->reference_no,
            'proforma_reference_no' => $prf->proforma_reference_no,
            'type' => ucfirst($prf->type),
            'origin' => ucfirst($prf->origin),
            'requesting_branch' => optional($prf->requestingBranch)->name,
            'status' => strtolower($prf->status),
            'has_canvass' => !empty($prf->details['canvass_items']),
            'items' => [
                'products' => collect($prf->details['products'] ?? [])->map(function ($i) {
                    $product = Product::find($i['id']);
                    return [
                        'subtype' => 'products',
                        'code' => $product->code ?? 'N/A',
                        'name' => $product->name ?? 'N/A',
                        'quantity' => $i['quantity'],
                        'category' => optional($product->category)->name,
                        'unit' => null,
                    ];
                }),
                'components' => collect($prf->details['components'] ?? [])->map(function ($i) {
                    $component = Component::with('unit','category')->find($i['id']);
                    return [
                        'subtype' => 'components',
                        'code' => $component->code ?? 'N/A',
                        'name' => $component->name ?? 'N/A',
                        'quantity' => $i['quantity'],
                        'category' => optional($component->category)->name,
                        'unit' => $component->unit,
                    ];
                }),
            ]
        ];
    });

    return response()->json($prfs);
}

    public function create()
    {
        $currentBranchId = current_branch_id();

        $branchPrefix = sprintf('PRF-%02d', $currentBranchId);

        $maxSeq = DB::table('procurement_requests')
            ->where('reference_no', 'like', "{$branchPrefix}-%")
            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(reference_no, "-", -1) AS UNSIGNED)) as max_seq')
            ->value('max_seq');

        $reference_no = sprintf('%s-%05d', $branchPrefix, ($maxSeq ? (int) $maxSeq + 1 : 1));

        $requestors = User::all();
        $branches = Branch::all();

        return view('reports.procurement-request.form', [
             'mode' => 'create',
             'prfs' => null,
             'referenceNo' => $reference_no,
             'requestors' => $requestors,
             'branches' => $branches,
             'currentBranchId' => $currentBranchId
             ]);
    }

    public function fetchItems(Request $request)
{
    $subtype = strtolower($request->input('subtype', 'products'));

    switch ($subtype) {
        case 'components':
            $items = Component::with(['category:id,name', 'subcategory:id,name', 'unit:id,name'])
                ->select('id', 'code', 'name', 'status', 'category_id', 'subcategory_id', 'unit_id', 'onhand')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->code,
                        'name' => $item->name,
                        'status' => $item->status,
                        'category' => $item->category,
                        'subcategory' => $item->subcategory,
                        'unit' => $item->unit ? [
                            'id' => $item->unit->id,
                            'name' => $item->unit->name,
                        ] : null,
                        'onhand' => $item->onhandForCurrentBranch(),
                    ];
                });
            break;

        case 'products':
            $items = Product::with(['category:id,name', 'subcategory:id,name'])
                ->where('type', 'simple')
                ->select('id', 'code', 'name', 'status', 'category_id', 'subcategory_id', 'quantity')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->code,
                        'name' => $item->name,
                        'status' => $item->status,
                        'category' => $item->category,
                        'subcategory' => $item->subcategory,
                        'unit' => null, // products don’t have unit
                        'onhand' => $item->onhandForCurrentBranch(),
                    ];
                });
            break;

        default:
            $items = collect();
            break;
    }

    return response()->json(['items' => $items]);
}

public function store(Request $request)
{
    DB::beginTransaction();

    try {
        $data = $request->validate([
            'reference_no' => 'required|string',
            'requested_datetime' => 'required|date',
            'requested_by' => 'required|exists:users,id',
            'requesting_branch_id' => 'required|exists:branches,id',
            'type' => 'required|string',
            'subtype' => 'nullable|string',
            'origin' => 'required|string',
            'proforma_reference_no' => 'nullable|string',
            'items' => 'required|array',
        ]);

        $prf = ProcurementRequest::create([
            'reference_no' => $data['reference_no'],
            'requested_datetime' => $data['requested_datetime'],
            'requesting_branch_id' => $data['requesting_branch_id'],
            'requested_by' => $data['requested_by'],
            'department_id' => optional(
                                    auth()->user()->employeeWorkInformations()->latest()->first()
                                )->department_id,
            'type' => $data['type'],
            'origin' => $data['origin'],
            'proforma_reference_no' => $data['proforma_reference_no'] ?? null,
            'details' => $data['items'],
            'status' => 'pending',
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Procurement Request created successfully',
            'data' => $prf
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Failed to create request',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function edit($id)
{
    $prf = ProcurementRequest::findOrFail($id);

    $requestors = User::all();
    $branches = Branch::all();
    $details =$prf->details;

    return view('reports.procurement-request.form', [
        'mode' => 'edit',
        'prfs' => $prf,
        'requestors' => $requestors,
        'branches' => $branches,
        'details' => $details, // 🔥 pass to Vue if needed
    ]);
}

public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        $prf = ProcurementRequest::findOrFail($id);

        $data = $request->validate([
            'reference_no' => 'required|string',
            'requested_datetime' => 'required|date',
            'requested_by' => 'required|exists:users,id',
            'type' => 'required|string',
            'subtype' => 'nullable|string',
            'origin' => 'required|string',
            'proforma_reference_no' => 'nullable|string',
            'items' => 'required|array',
        ]);

        $prf->update([
            'reference_no' => $data['reference_no'],
            'requested_datetime' => $data['requested_datetime'],
            'requested_by' => $data['requested_by'],
            'type' => $data['type'],
            'origin' => $data['origin'],
            'proforma_reference_no' => $data['proforma_reference_no'] ?? null,
            'details' => $data['items'],
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Procurement Request updated successfully',
            'data' => $prf
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Failed to update request',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function updateStatus(Request $request, $id)
{
    $prf = ProcurementRequest::with([
        'requestedBy.employeeWorkInformations.department',
    ])->findOrFail($id);

    $previousStatus = $prf->status;

    $prf->update(['status' => $request->status]);

    $posCreated = 0;
    if ($request->status === 'approved' && $previousStatus !== 'approved') {
        try {
            $posCreated = $this->createPurchaseOrdersFromCanvass($prf);
        } catch (\Throwable $e) {
            \Log::error('Failed to auto-create POs from canvass', [
                'prf_id' => $prf->id,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    return response()->json([
        'message'      => 'Status updated successfully',
        'status'       => $prf->status,
        'updated_at'   => now()->format('Y-m-d H:i'),
        'pos_created'  => $posCreated,
    ]);
}

private function createPurchaseOrdersFromCanvass(ProcurementRequest $prf): int
{
    $canvassItems = $prf->details['canvass_items'] ?? [];
    if (empty($canvassItems)) return 0;

    $branchId = $prf->requesting_branch_id;
    $taxRate  = 0.12;

    // Build qty lookup from original PRF details
    $qtyMap = [];
    foreach ($prf->details['components'] ?? [] as $item) {
        $qtyMap["component_{$item['id']}"] = $item['quantity'];
    }
    foreach ($prf->details['products'] ?? [] as $item) {
        $qtyMap["product_{$item['id']}"] = $item['quantity'];
    }

    $department = optional(
        optional($prf->requestedBy->employeeWorkInformations->last())->department
    )->name;

    // Group by supplier — only entries where selected_supplier = true
    $supplierGroups = [];
    foreach ($canvassItems as $canvassItem) {
        $type    = $canvassItem['type'];
        $itemId  = $canvassItem['item_id'];

        foreach ($canvassItem['entries'] ?? [] as $entry) {
            if (empty($entry['selected_supplier'])) continue;

            $supplierId = $entry['supplier_id'] ?? null;
            if (!$supplierId) continue;

            $supplierGroups[$supplierId][] = [
                'type'            => $type,
                'item_id'         => $itemId,
                'price_per_unit'  => (float) ($entry['price_per_unit'] ?? 0),
                'qty'             => (int) ($qtyMap["{$type}_{$itemId}"] ?? 1),
                'attachment_path' => $entry['attachment_path'] ?? null,
            ];
        }
    }

    if (empty($supplierGroups)) return 0;

    // One base sequence per PRF, suffix -1/-2/-3 per supplier
    $maxSeq = DB::table('inventory_purchase_orders')
        ->where('po_number', 'like', "PO-{$branchId}-%")
        ->selectRaw('MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(po_number, "-", 3), "-", -1) AS UNSIGNED)) as max_seq')
        ->value('max_seq');
    $baseSeq    = $maxSeq ? (int) $maxSeq + 1 : 1;
    $baseNumber = 'PO-' . $branchId . '-' . str_pad($baseSeq, 6, '0', STR_PAD_LEFT);

    $created       = 0;
    $supplierIndex = 1;
    foreach ($supplierGroups as $supplierId => $items) {
        $poNumber = $baseNumber . '-' . $supplierIndex++;

        // Collect attachments from all entries for this supplier
        $attachments = collect($items)
            ->pluck('attachment_path')
            ->filter()
            ->values()
            ->toArray();

        $po = InventoryPurchaseOrder::create([
            'po_number'            => $poNumber,
            'user_id'              => $prf->requested_by,
            'department'           => $department,
            'supplier_id'          => $supplierId,
            'prf_reference_number' => $prf->reference_no,
            'type_of_request'      => $prf->type,
            'select_origin'        => $prf->origin,
            'status'               => 'pending',
            'branch_id'            => $branchId,
            'attachments'          => !empty($attachments) ? json_encode($attachments) : null,
        ]);

        // Create PO details — components only (products don't map to PoDetail)
        foreach ($items as $item) {
            if ($item['type'] !== 'component') continue;

            $component  = Component::with('branchStocks')->find($item['item_id']);
            if (!$component) continue;

            $branchStock = $component->branchStocks()->where('branch_id', $branchId)->first();
            $onhand      = $branchStock ? (float) $branchStock->onhand : 0;

            $qty      = $item['qty'];
            $unitCost = $item['price_per_unit'];
            $subTotal = $qty * $unitCost;
            $tax      = $subTotal * $taxRate;

            $po->details()->create([
                'component_id' => $item['item_id'],
                'qty'          => $qty,
                'unit_cost'    => $unitCost,
                'tax'          => $tax,
                'sub_total'    => $subTotal,
                'onhand'       => $onhand,
            ]);

            $component->update(['cost' => $unitCost]);
        }

        $created++;
    }

    return $created;
}

public function canvass($id)
{
    $prf = ProcurementRequest::with([
        'requestedBy.employeeWorkInformations.department',
        'requestingBranch:id,name',
    ])->findOrFail($id);

    $suppliers = Supplier::select('id', 'fullname', 'company')->get();

    $branchId = $prf->requesting_branch_id;
    $maxSeq = DB::table('inventory_purchase_orders')
        ->where('po_number', 'like', "PO-{$branchId}-%")
        ->selectRaw('MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(po_number, "-", 3), "-", -1) AS UNSIGNED)) as max_seq')
        ->value('max_seq');
    $nextPoNumber = 'PO-' . $branchId . '-' . str_pad(($maxSeq ? (int) $maxSeq + 1 : 1), 6, '0', STR_PAD_LEFT);

    return view('reports.procurement-request.canvass', compact('prf', 'suppliers', 'nextPoNumber'));
}

public function fetchCanvassData($id)
{
    $prf = ProcurementRequest::findOrFail($id);

    $items = [];

    foreach ($prf->details['products'] ?? [] as $item) {
        $product = Product::with('category')->find($item['id']);
        if (!$product) continue;
        $items[] = [
            'type'     => 'product',
            'item_id'  => $item['id'],
            'name'     => $product->name,
            'code'     => $product->code,
            'category' => optional($product->category)->name ?? 'N/A',
            'brand'    => 'N/A',
            'unit'     => 'N/A',
            'quantity' => $item['quantity'],
        ];
    }

    foreach ($prf->details['components'] ?? [] as $item) {
        $component = Component::with('category', 'unit')->find($item['id']);
        if (!$component) continue;
        $items[] = [
            'type'     => 'component',
            'item_id'  => $item['id'],
            'name'     => $component->name,
            'code'     => $component->code,
            'category' => optional($component->category)->name ?? 'N/A',
            'brand'    => $component->brand_name ?? 'N/A',
            'unit'     => optional($component->unit)->name ?? 'N/A',
            'quantity' => $item['quantity'],
        ];
    }

    $savedCanvass = $prf->details['canvass_items'] ?? [];

    return response()->json([
        'items'         => $items,
        'canvass_items' => $savedCanvass,
    ]);
}

public function uploadCanvassAttachment(Request $request, $id)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,xls,xlsx,doc,docx|max:5120',
    ]);

    $file = $request->file('file');
    $path = $file->store("canvass-attachments/{$id}", 'public');

    return response()->json([
        'path' => $path,
        'name' => $file->getClientOriginalName(),
    ]);
}

public function storeCanvass(Request $request, $id)
{
    DB::beginTransaction();

    try {
        $prf = ProcurementRequest::findOrFail($id);

        $request->validate([
            'canvass_items'                                => 'required|array',
            'canvass_items.*.item_id'                      => 'required',
            'canvass_items.*.type'                         => 'required|string',
            'canvass_items.*.entries'                      => 'required|array|min:1',
            'canvass_items.*.entries.*.price_per_unit'     => 'required|numeric|min:0',
            'canvass_items.*.entries.*.supplier_id'        => 'required|exists:suppliers,id',
            'canvass_items.*.entries.*.attachment_path'    => 'nullable|string',
            'canvass_items.*.entries.*.attachment_name'    => 'nullable|string',
        ]);

        $details = $prf->details ?? [];
        $details['canvass_items'] = $request->canvass_items;

        $prf->update(['details' => $details]);

        DB::commit();

        return response()->json(['message' => 'Canvass submitted successfully']);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['message' => 'Failed to submit canvass', 'error' => $e->getMessage()], 500);
    }
}
}
