<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\KitchenMassProduction;
use App\Models\Product;
use App\Models\Station;
use App\Models\Unit;
use Illuminate\Http\Request;

class KitchenmrpController extends Controller
{
    public function index()
    {
        return view('inventory.kitchen-mrp.index');
    }

    public function fetchRequests(Request $request)
    {
        $currentBranchId = current_branch_id();

        $query = KitchenMassProduction::with([
            'product',
            'createdBy',
            'approvedBy',
            'completedBy',
            'disapprovedBy',
            'archivedBy',
        ]);

        // ✅ Direct branch filtering (FAST & CLEAN)
        $query->where('branch_id', $currentBranchId);

        // ✅ Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->search . '%')
                ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // ✅ Sorting
        $allowedSorts = ['id', 'status', 'created_at', 'updated_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSorts)
            ? $request->get('sort_by')
            : 'created_at';

        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';

        $requests = $query->orderBy($sortBy, $sortDir)
                        ->paginate($request->per_page ?? 10);

        // ✅ Transform (Structured Like Transfer)
        $requests->getCollection()->transform(function ($mrp) {

            $canApprove = $mrp->status === 'pending';
            $canComplete = $mrp->status === 'approved';
            $canDisapprove = $mrp->status === 'pending';
            $canArchive = $mrp->status === 'completed';

            return [
                'id' => $mrp->id,
                'reference_no' => $mrp->reference_no,

                // 🔹 Product Info
                'product_id' => $mrp->product_id,
                'sku' => $mrp->product?->code,
                'product_name' => $mrp->product?->name,
                'unit' => $mrp->product?->unit,
                'station' => $mrp->product?->station,

                // 🔹 Production Info
                'quantity' => $mrp->quantity,
                'status' => $mrp->status,

                // 🔹 Audit Trail
                'created_by_name'    => $mrp->createdBy?->name,
                'approved_by_name'    => $mrp->approvedBy?->name,
                'completed_by_name'   => $mrp->completedBy?->name,
                'disapproved_by_name' => $mrp->disapprovedBy?->name,
                'archived_by_name'    => $mrp->archivedBy?->name,

                'approved_datetime'    => optional($mrp->approved_datetime)?->format('Y-m-d H:i:s'),
                'completed_datetime'   => optional($mrp->completed_datetime)?->format('Y-m-d H:i:s'),
                'disapproved_datetime' => optional($mrp->disapproved_datetime)?->format('Y-m-d H:i:s'),
                'archived_datetime'    => optional($mrp->archived_datetime)?->format('Y-m-d H:i:s'),

                'created_at' => $mrp->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $mrp->updated_at->format('Y-m-d H:i:s'),

                // 🔹 Permissions (for buttons)
                'can_approve' => $canApprove,
                'can_complete' => $canComplete,
                'can_disapprove' => $canDisapprove,
                'can_archive' => $canArchive,
            ];
        });

        return response()->json($requests);
    }

     public function create()
    {
        $branchId = current_branch_id();

        // 🔥 Get next ID preview (for display only)
        $nextId = \App\Models\KitchenMassProduction::max('id') + 1;

        // 🔹 All components, used as recipe ingredients
            $components = Component::all();

            // 🔹 Only branch products
    $products = Product::with(['unit', 'station', 'recipes'])
        ->where('type', 'simple')
        ->whereHas('branchStocks', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->where('status', 'active')
        ->get();

        $previewReferenceNo = 'MRP-' . $branchId . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('inventory.kitchen-mrp.form', [
            'massProduction' => null,

            // 🔹 Auto Reference Preview
            'referenceNo' => $previewReferenceNo,

            // 🔹 Only stations for current branch (if applicable)
            'stations' => Station::all(),

            // 🔹 Units
            'units' => Unit::all(),

            'components' => $components,

            // 🔹 Only branch products (VERY IMPORTANT)
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:1',
        ]);

        $massProduction = KitchenMassProduction::create([
            'reference_no' => $request->reference_no,
            'product_id' => $request->product_id,
            'quantity'   => $request->quantity,
            'status'     => 'pending', // default status
            'remarks'      => $request->remarks,
            'created_by'   => auth()->id(),
            'branch_id'  => current_branch_id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mass production request created successfully.',
            'data'    => $massProduction
        ]);
    }

    public function edit($id)
{
    $massProduction = KitchenMassProduction::with([
    'product.unit',
    'product.station',
    'product.recipes.component' // component info for each recipe
])->findOrFail($id);

    $branchId = current_branch_id();

    // Products for dropdown
    $products = Product::with(['unit', 'station'])
        ->where('type', 'simple')
        ->whereHas('branchStocks', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->where('status', 'active')
        ->get();

    // 🔹 Components for ingredients (no "for_sale" restriction)
    $components = \App\Models\Component::all();

    return view('inventory.kitchen-mrp.form', [
        'massProduction' => $massProduction,
        'referenceNo'    => $massProduction->reference_no,
        'products'       => $products,
        'components'     => $components, // ⚡ pass this
        'isLogProcess' => false,
    ]);
}

    public function update(Request $request, $id)
{
    $massProduction = KitchenMassProduction::findOrFail($id);

    // 🔹 Validation
    $request->validate([
        'product_id'        => 'required|exists:products,id',
        'quantity'          => 'required|numeric|min:1',
        'remarks'           => 'nullable|string|max:255',
        'status'            => 'nullable|string',
        'processed_items'   => 'nullable|json',
    ]);

    // 🔹 Basic update
    $massProduction->product_id = $request->product_id;
    $massProduction->quantity   = $request->quantity;
    $massProduction->remarks    = $request->remarks;

    // 🔹 Log Process Mode
    if ($request->status === 'completed') {

        $massProduction->status = 'completed';

        // Decode processed items (used + additional)
        $processedItems = json_decode($request->processed_items ?? '[]', true);

        // Save all additional items for record keeping
        $massProduction->additional_items = $processedItems;

        // 🔹 Deduct stock and record InventoryDeduction
        foreach ($processedItems as $item) {

        $componentId = $item['component_id'] ?? null;
        $deductQty   = $item['quantity'] ?? 0;

        if (!$componentId || $deductQty <= 0) continue;

        $component = \App\Models\Component::find($componentId); // ✅ single model
        if (!$component) continue;

        $prevQuantity = $component->onhand ?? 0;

        // Deduct stock safely
        $component->onhand = $prevQuantity - $deductQty;
        $component->save();

        // Record inventory deduction
        \App\Models\InventoryDeduction::create([
            'component_id'      => $componentId,
            'quantity_deducted' => $deductQty,
            'prev_quantity'     => $prevQuantity,
            'new_quantity'      => $component->onhand,
            'deduction_type'    => 'mass_production',
            'notes'             => 'Log Processed Goods: ' . $massProduction->reference_no,
            'user_id'           => auth()->id(),
        ]);
    }
    }

    $massProduction->save();

    return response()->json([
        'success' => true,
        'message' => 'Mass Production updated successfully!',
        'data'    => $massProduction
    ]);
}

    public function updateStatus(Request $request, $id)
    {
        $mass_production = KitchenMassProduction::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,completed,disapproved,archived',
        ]);

        $userId = auth()->id();
        $now = now();

        $updateData = [
            'status' => $validated['status'],
        ];

        $byName = null;
        $formattedTime = null;

        switch ($validated['status']) {
            case 'approved':
                $updateData['approved_by'] = $userId;
                $updateData['approved_datetime'] = $now;
                $byName = auth()->user()->name;
                $formattedTime = $now->format('g:i A');
                break;

            case 'completed':
                $updateData['completed_by'] = $userId;
                $updateData['completed_datetime'] = $now;
                $byName = auth()->user()->name;
                $formattedTime = $now->format('g:i A');
                break;

            case 'disapproved':
                $updateData['disapproved_by'] = $userId;
                $updateData['disapproved_datetime'] = $now;
                $byName = auth()->user()->name;
                $formattedTime = $now->format('g:i A');
                break;

            case 'archived':
                $updateData['archived_by'] = $userId;
                $updateData['archived_datetime'] = $now;
                $byName = auth()->user()->name;
                $formattedTime = $now->format('g:i A');
                break;
        }

        $mass_production->update($updateData);

        return response()->json([
            'message' => 'Mass Production status updated successfully',
            'status' => $validated['status'],
            'by_name' => $byName,
            'datetime' => $formattedTime,
        ]);
    }

    public function isLogProcess($id)
    {
        $massProduction = KitchenMassProduction::with([
            'product.unit',
            'product.station',
            'product.recipes.component'
        ])->findOrFail($id);

        $branchId = current_branch_id();

        $products = Product::with(['unit', 'station'])
            ->where('type', 'simple')
            ->whereHas('branchStocks', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where('status', 'active')
            ->get();

        $components = \App\Models\Component::all();

        return view('inventory.kitchen-mrp.form', [
            'massProduction' => $massProduction,
            'referenceNo'    => $massProduction->reference_no,
            'products'       => $products,
            'components'     => $components,
            'isLogProcess'   => true, // 👈 THIS IS THE ONLY DIFFERENCE
        ]);
    }
}
