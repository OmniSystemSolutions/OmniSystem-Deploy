<?php

namespace App\Http\Controllers;

use App\Models\BranchComponent;
use App\Models\Category;
use App\Models\Component;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComponentController extends Controller
{
    /**
     * Centralized unit list — edit here to add/remove options app-wide.
     */
    // private const UNITS = [
    //     'pcs', 'box', 'pack', 'set', 'pair',
    //     'kg', 'g', 'mg',
    //     'L', 'mL',
    //     'ft', 'in', 'm', 'cm',
    //     'roll', 'sheet', 'bundle', 'bag', 'bottle', 'can', 'tube',
    // ];

    public function index(Request $request)
    {
        return view('components.index', [
            'status' => $request->get('status', 'active'),
        ]);
    }

    public function fetchComponents(Request $request)
    {
        $status   = $request->get('status', 'active');
        $perPage  = $request->get('perPage', 10);
        $search   = $request->get('search');
        $branchId = current_branch_id();

        $query = Component::query()
            ->select([
                'components.*',
                'bc.onhand',
                'bc.cost',
                'bc.price',
                'bc.for_sale',
                'bc.status as status',
            ])
            ->join('branch_components as bc', 'bc.component_id', '=', 'components.id')
            ->where('bc.branch_id', $branchId)
            ->where('components.status', $status)
            ->with(['category', 'subcategory', 'unit']);

        $query->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('components.name', 'like', "%{$search}%")
                  ->orWhereHas('category',    fn ($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('subcategory', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        });

        $components = $query
            ->orderBy('components.created_at', 'desc')
            ->paginate($perPage);

        // Map unit_id to unit name for frontend
        $components->getCollection()->transform(function ($component) {
            $component->unit = $component->unit?->name; // new field for Vue
            return $component;
        });

        return response()->json($components);
    }

    public function create()
    {
        $categories    = Category::where('status', 'active')->get();
        $subcategories = Subcategory::all();
        $suppliers     = Supplier::where('status', 'active')->get();
        $units         = Unit::all();

        return view('components.create', compact('categories', 'subcategories', 'suppliers', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string',
            'code'           => 'required|string|unique:components,code',
            'brand_name'     => 'nullable|string|max:255',
            'category_id'    => 'required|integer|exists:categories,id',
            'subcategory_id' => 'nullable|integer|exists:subcategories,id',
            'supplier_id'    => 'nullable|integer|exists:suppliers,id',
            'cost'           => 'required|numeric',
            'price'          => 'required|numeric',
            'unit_id'       => 'required|integer|exists:units,id',
            'onhand'         => 'required|integer',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'for_sale'       => 'nullable|boolean',
        ]);

        $validated['for_sale'] = $request->has('for_sale') ? 1 : 0;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('components', 'public');
        }

        $component = Component::create($validated);

        BranchComponent::create([
            'branch_id'    => current_branch_id(),
            'component_id' => $component->id,
            'onhand'       => $component->onhand,
            'cost'         => $component->cost,
            'price'        => $component->price,
            'status'       => 'active',
            'for_sale'     => $component->for_sale,
            'supplier_id'  => $component->supplier_id,
        ]);

        return redirect()->route('components.index')->with('success', 'Component created successfully.');
    }

    public function edit($id)
    {
        $branch = current_branch_id();

        $branchComponent = BranchComponent::with('component.recipes')
            ->where('branch_id', $branch)
            ->where('component_id', $id)
            ->firstOrFail();

        $component     = $branchComponent->component;
        $categories    = Category::where('status', 'active')->get();
        $subcategories = Subcategory::all();
        $components    = Component::all();
        $suppliers     = Supplier::where('status', 'active')->get();
        $units         = Unit::all();

        return view('components.edit', compact(
            'component',
            'branchComponent',
            'categories',
            'subcategories',
            'components',
            'suppliers',
            'units'
        ));
    }

    public function update(Request $request, Component $component)
{
    $branch = current_branch_id();
// dd($request->all());
    $validated = $request->validate([
        'code'           => 'required|string|unique:components,code,' . $component->id,
        'name'           => 'required|string',
        'brand_name'     => 'nullable|string|max:255',
        'category_id'    => 'required|integer|exists:categories,id',
        'subcategory_id' => 'nullable|integer|exists:subcategories,id',
        'supplier_id'    => 'nullable|exists:suppliers,id',
        'unit_id'        => 'required|integer|exists:units,id',

        // Branch fields
        'cost'     => 'required|numeric',
        'price'    => 'required|numeric',
        'onhand'   => 'required|numeric',
        'for_sale' => 'nullable|boolean',

        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $validated['for_sale'] = $request->has('for_sale') ? 1 : 0;

    /*
    |------------------------------------------
    | IMAGE (unchanged as requested)
    |------------------------------------------
    */
    if ($request->hasFile('image')) {
        if ($component->image && Storage::disk('public')->exists($component->image)) {
            Storage::disk('public')->delete($component->image);
        }

        $validated['image'] = $request->file('image')->store('components', 'public');
    }

    /*
    |------------------------------------------
    | UPDATE GLOBAL COMPONENT DATA
    |------------------------------------------
    */
    $component->update([
        'code'           => $validated['code'],
        'name'           => $validated['name'],
        'brand_name'     => $validated['brand_name'],
        'category_id'    => $validated['category_id'],
        'subcategory_id' => $validated['subcategory_id'],
        'supplier_id'    => $validated['supplier_id'] ?? null,
        'unit_id'        => $validated['unit_id'],
        'image'          => $validated['image'] ?? $component->image,
    ]);

    /*
    |------------------------------------------
    | UPDATE BRANCH COMPONENT ONLY
    |------------------------------------------
    */
    BranchComponent::where('branch_id', $branch)
        ->where('component_id', $component->id)
        ->update([
            'cost'     => $validated['cost'],
            'price'    => $validated['price'],
            'onhand'   => $validated['onhand'],
            'for_sale' => $validated['for_sale'],
        ]);

    return redirect()
        ->route('components.index')
        ->with('success', 'Component updated successfully!');
}

    public function destroy($id)
    {
        Component::findOrFail($id)->delete();

        return redirect()->route('components.index')->with('success', 'Component deleted successfully.');
    }

    public function archive(Component $component)
    {
        $component->update(['status' => 'archived']);

        return response()->json([
            'message'      => 'Component moved to archive successfully.',
            'status'       => 'success',
            'component_id' => $component->id,
        ]);
    }

    public function restore(Component $component)
    {
        $component->update(['status' => 'active']);

        return response()->json([
            'message'      => 'Component restored to active successfully.',
            'status'       => 'success',
            'component_id' => $component->id,
        ]);
    }

    public function stockCard($id)
    {
        $component = Component::findOrFail($id);
        $movements = collect();

        /* 1) AUDITS */
        $auditItems = \App\Models\InventoryAuditItem::where('component_id', $id)
            ->whereHas('audit', fn ($q) => $q->where('status', 'completed'))
            ->with('audit')
            ->get()
            ->map(fn ($item) => [
                'entry_datetime' => $item->created_at,
                'activity'       => 'AUDIT',
                'reference_no'   => $item->audit ? $item->audit->reference_no : "AUDIT-{$item->id}",
                'qty_balance'    => $item->quantity ?? 0,
                'cost_per_unit'  => $component->cost ?? 0,
            ]);

        /* 2) DEDUCTIONS */
        $deductions = \App\Models\InventoryDeduction::where('component_id', $id)
            ->get()
            ->map(fn ($d) => [
                'entry_datetime' => $d->created_at,
                'activity'       => $d->order_detail_id ? 'ORDER' : 'DEDUCTION',
                'reference_no'   => $d->order_detail_id ? "ORD-{$d->order_detail_id}" : "DED-{$d->id}",
                'qty_balance'    => $d->new_quantity ?? 0,
                'cost_per_unit'  => $component->cost ?? 0,
            ]);

        /* 3) PO DETAILS */
        $poDetails = \App\Models\PoDetail::where('component_id', $id)
            ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', ['approved', 'completed']))
            ->with('purchaseOrder')
            ->get()
            ->map(fn ($item) => [
                'entry_datetime' => $item->created_at,
                'activity'       => 'PURCHASE',
                'reference_no'   => $item->purchaseOrder ? $item->purchaseOrder->po_number : "PO-{$item->id}",
                'qty_balance'    => ($item->onhand ?? 0) + ($item->received_qty ?? 0),
                'cost_per_unit'  => $component->cost ?? 0,
            ]);

        /* 4) TRANSFERS */
        $transferMovements = \App\Models\InventoryTransferSendOut::whereHas(
            'transfer', fn ($q) => $q->whereIn('status', ['in_transit', 'completed'])
        )->get()->flatMap(function ($sendOut) use ($id, $component) {
            $rows = [];
            foreach ($sendOut->items_onload as $item) {
                if (data_get($item, 'type') !== 'component') continue;
                $newOnhand      = data_get($item, 'new_onhand');
                $transferItemId = data_get($item, 'inventory_transfer_item_id');
                if ($newOnhand === null || !$transferItemId) continue;
                $transferItem = \App\Models\InventoryTransferItem::find($transferItemId);
                if (!$transferItem || $transferItem->component_id != $id) continue;
                $rows[] = [
                    'entry_datetime' => $sendOut->created_at,
                    'activity'       => 'TRANSFER OUT',
                    'reference_no'   => $sendOut->delivery_request_no,
                    'qty_balance'    => (float) $newOnhand,
                    'cost_per_unit'  => $component->cost ?? 0,
                ];
            }
            return $rows;
        });

        /* 5) Merge, sort, compute in/out */
        $prevBalance = 0;
        $movements = $auditItems
            ->concat($deductions)
            ->concat($poDetails)
            ->concat($transferMovements)
            ->sortBy('entry_datetime')
            ->values()
            ->map(function ($m) use (&$prevBalance) {
                $diff          = $m['qty_balance'] - $prevBalance;
                $m['qty_in']   = $diff > 0 ? $diff  : 0;
                $m['qty_out']  = $diff < 0 ? abs($diff) : 0;
                $prevBalance   = $m['qty_balance'];
                return $m;
            })
            ->sortByDesc('entry_datetime')
            ->values();

        return view('components.stock-card', compact('component', 'movements'));
    }

    public function verifyImport(Request $request)
{
    $rows = $request->input('rows', []);
    $errors = [];

    // Get current user's branch
    $branch = auth()->user()->branches()->first();
    $branchId = current_branch_id();
    $codes = [];

    foreach ($rows as $index => $row) {

        $rowErrors = [];

        $code = $row['code'] ?? null;
        $name = $row['name'] ?? null;

        // Required fields
        if (!$code) {
            $rowErrors[] = 'SKU is required';
        }

        if (!$name) {
            $rowErrors[] = 'Component name is required';
        }

        // Duplicate inside uploaded file
        if (in_array($code, $codes)) {
            $rowErrors[] = 'Duplicate SKU in file';
        } else {
            $codes[] = $code;
        }

        // Check if component exists
        $component = Component::where('code', $code)->first();

        if ($component && $branchId) {

            // Check if component already exists in this branch
            $exists = BranchComponent::where('branch_id', $branchId)
                ->where('component_id', $component->id)
                ->exists();

            if ($exists) {
                $rowErrors[] = 'Component already exists in this branch';
            }
        }

        if (!empty($rowErrors)) {
            $errors[$index] = $rowErrors;
        }
    }

    return response()->json([
        'success' => true,
        'errors' => $errors
    ]);
}

    public function import(Request $request)
{
    try {

        $branch = current_branch_id();

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'User has no branch assigned.'
            ]);
        }

        $rows = $request->all(); // ✅ get JSON body

        foreach ($rows as $row) {

            if (($row['status'] ?? '') !== 'ready') {
                continue;
            }

            $component = Component::firstOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                ]
            );

            $exists = BranchComponent::where('branch_id', $branch)
                ->where('component_id', $component->id)
                ->exists();

            if ($exists) {
                continue;
            }

            BranchComponent::create([
                'branch_id' => $branch,
                'component_id' => $component->id,
                'onhand' => $row['onhand'] ?? 0,
                'cost' => $row['cost'] ?? 0,
                'price' => $row['price'] ?? 0,
                'status' => 'active',
                'for_sale' => $row['for_sale'] ?? false,
            ]);
        }

        return response()->json([
            'success' => true
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
}