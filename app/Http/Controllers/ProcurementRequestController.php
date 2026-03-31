<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Component;
use App\Models\ProcurementRequest;
use App\Models\Product;
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


        // Get next AUTO_INCREMENT value
        $nextId = DB::table('information_schema.TABLES')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'inventory_transfers')
            ->value('AUTO_INCREMENT');
            
        $reference_no = sprintf(
            '%s-%02d-%05d',
            'PRF',
            $currentBranchId,
            $nextId
        );

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
            'type' => 'required|string',
            'subtype' => 'nullable|string',
            'origin' => 'required|string',
            'proforma_reference_no' => 'nullable|string',
            'items' => 'required|array',
        ]);

        $prf = ProcurementRequest::create([
            'reference_no' => $data['reference_no'],
            'requested_datetime' => $data['requested_datetime'],
            'requesting_branch_id' => current_branch_id(),
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
    $prf = ProcurementRequest::findOrFail($id);

    $prf->update([
        'status' => $request->status
    ]);

    return response()->json([
        'message' => 'Status updated successfully',
        'status' => $prf->status,
        'updated_at' => now()->format('Y-m-d H:i')
    ]);
}
}
