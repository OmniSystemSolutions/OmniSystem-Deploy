<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchComponent;
use App\Models\BranchProduct;
use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use App\Models\InventoryTransferSendOut;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransferController extends Controller
{
    public function index()
    {
        return view('inventory.transfer.index');
    }
    
    public function fetchTransfers(Request $request)
    {
        $currentBranchId = current_branch_id();

        $query = InventoryTransfer::with([
    'sourceBranch',
    'destinationBranch',
    'requester',
    'approvedBy',
    'inTransitBy',
    'completedBy',
    'disapprovedBy',
    'archivedBy',
    'items',
    'sendOuts',
]);

// ✅ Branch visibility filter
    $query->where(function ($q) use ($currentBranchId) {
        $q->where('source_id', $currentBranchId)
          ->orWhere('destination_id', $currentBranchId);
    });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('reference_no', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('transfer_type')) {
            $query->where('transfer_type', $request->transfer_type);
        }

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->source_id);
        }

        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        // Sorting with allowed fields
        $allowedSorts = ['id', 'reference_no', 'status', 'created_at', 'updated_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSorts) ? $request->get('sort_by') : 'created_at';
        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';

        $transfers = $query->orderBy($sortBy, $sortDir)
                        ->paginate($request->per_page ?? 10);

        $transfers->getCollection()->transform(function ($transfer) {
    $currentBranchId = current_branch_id();

    $totalRequested = $transfer->items->sum('quantity_requested');
    $totalSent = $transfer->items->sum('quantity_sent');

    $canAddStocks = (
        $transfer->status === 'in_transit'
        && (int) $transfer->destination_id === (int) $currentBranchId
        && $totalSent > 0
    );

    $canSendStocks = (
        $transfer->status === 'approved'
        && (int) $transfer->source_id === (int) $currentBranchId
    );

    $canSendAdditionalStocks = (
        $transfer->status === 'in_transit'
        && (int) $transfer->source_id === (int) $currentBranchId
        && $totalSent < $totalRequested
    );

    return [
    'id' => $transfer->id,
    'reference_no' => $transfer->reference_no,

    // 🔹 Base
    'requested_by' => $transfer->requester?->name,
    'requested_datetime' => optional($transfer->requested_datetime)->format('Y-m-d H:i:s'),

    // 🔹 Dynamic "BY" names
    'approved_by_name'     => $transfer->approvedBy?->name,
    'in_transit_by_name'   => $transfer->inTransitBy?->name,
    'completed_by_name'    => $transfer->completedBy?->name,
    'disapproved_by_name'  => $transfer->disapprovedBy?->name,
    'archived_by_name'     => $transfer->archivedBy?->name,

    'transfer_type' => $transfer->transfer_type,
    'status' => $transfer->status,

    'total_requested' => $totalRequested,
    'total_sent' => $totalSent,

    'is_partial' => (
        $transfer->status === 'in_transit'
        && $totalSent > 0
        && $totalSent < $totalRequested
    ),

    'source_branch' => $transfer->sourceBranch?->name,
    'destination_branch' => $transfer->destinationBranch?->name,
    'attached_file' => $transfer->attached_file,

    'created_at' => $transfer->created_at->format('Y-m-d H:i:s'),
    'updated_at' => $transfer->updated_at->format('Y-m-d H:i:s'),

    'can_approve' => $transfer->canApproveForBranch($currentBranchId),
    'can_add_stocks' => $canAddStocks,
    'can_send_stocks' => $canSendStocks,
    'can_send_additional_stocks' => $canSendAdditionalStocks,
    'pending_send_outs_count' => $canAddStocks
        ? $transfer->sendOuts->whereNull('received_by')->count()
        : 0,
];
});




        return response()->json($transfers);
    }


    public function fetchItems(Request $request)
    {
        $type     = strtolower($request->input('type', 'components'));
        $branchId = current_branch_id();

        if ($type === 'products') {
            $items = BranchProduct::with(['product.category:id,name', 'unit:id,name'])
                ->where('branch_id', $branchId)
                ->where('type', 'simple')
                ->get()
                ->map(fn ($bp) => [
                    'id'       => $bp->product_id,
                    'code'     => $bp->product?->code,
                    'name'     => $bp->product?->name,
                    'category' => $bp->product?->category,
                    'unit'     => $bp->unit?->name,
                    'onhand'   => $bp->quantity,
                ]);
        } else {
            $items = BranchComponent::with(['component.category:id,name', 'component.unit:id,name'])
                ->where('branch_id', $branchId)
                ->get()
                ->map(fn ($bc) => [
                    'id'       => $bc->component_id,
                    'code'     => $bc->component?->code,
                    'name'     => $bc->component?->name,
                    'category' => $bc->component?->category,
                    'unit'     => $bc->component?->unit?->name,
                    'onhand'   => $bc->onhand,
                ]);
        }

        return response()->json(['items' => $items]);
    }

    public function create(Request $request)
    {
        // Get transfer type (request | send)
        $transferType = $request->get('transfer_type', 'request');

        // Safety check
        if (!in_array($transferType, ['request', 'send'])) {
            abort(404);
        }

         // ✅ ACTIVE branch (from header / session)
        $currentBranchId = current_branch_id();


        $nextId = (InventoryTransfer::max('id') ?? 0) + 1;

        // ✅ Reference prefix logic
        $prefix = $transferType === 'send' ? 'TSO' : 'TR';

        // Example: TSO-01-00023 or TR-01-00023
        $referenceNo = sprintf(
            '%s-%02d-%05d',
            $prefix,
            $currentBranchId,
            $nextId
        );

        // ✅ EXCLUDE current active branch
        $branches = Branch::where('id', '!=', $currentBranchId)
                    ->orderBy('name')
                    ->get(['id', 'name']);

        return view('inventory.transfer.form', [
            'mode' => 'create',
            'transferType' => $transferType,
            'referenceNo' => $referenceNo,
            'branches' => $branches,          // for destination dropdown
            'currentBranchId' => $currentBranchId,
            'transfer' => null,
        ]);
    }

   public function store(Request $request)
{
    // Base rules
    $rules = [
        'reference_no' => 'required|string',
        'requested_datetime' => 'required|date',
        'transfer_type' => 'required|in:request,send',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'nullable|exists:products,id',
        'items.*.component_id' => 'nullable|exists:components,id',

        // ✅ allow decimal, NOT integer
        'items.*.quantity' => 'required|numeric|min:0.01',
    ];

    // Conditional rules
    if ($request->transfer_type === 'send') {
        $rules['destination_id'] = 'required|exists:branches,id';
    }

    if ($request->transfer_type === 'request') {
        $rules['source_id'] = 'required|exists:branches,id';
    }

    $validated = $request->validate($rules);

    DB::transaction(function () use ($validated) {

        if ($validated['transfer_type'] === 'send') {
            $sourceId = current_branch_id();
            $destinationId = $validated['destination_id'];
        } else {
            $sourceId = $validated['source_id'];
            $destinationId = current_branch_id();
        }

        $transfer = InventoryTransfer::create([
            'reference_no' => $validated['reference_no'],
            'requested_by' => auth()->id(),
            'requested_datetime' => Carbon::parse($validated['requested_datetime']),
            'destination_id' => $destinationId,
            'source_id' => $sourceId,
            'transfer_type' => $validated['transfer_type'],
            'status' => 'pending',
        ]);

        foreach ($validated['items'] as $item) {

            // 🔒 Ensure only ONE of product/component
            if (empty($item['product_id']) === empty($item['component_id'])) {
                throw new \Exception(
                    'Each item must have either product_id or component_id.'
                );
            }

            InventoryTransferItem::create([
                'inventory_transfer_id' => $transfer->id,
                'product_id' => $item['product_id'] ?? null,
                'component_id' => $item['component_id'] ?? null,

                // ✅ NEW CORRECT COLUMNS
                'quantity_requested' => number_format(
                    (float) $item['quantity'],
                    2,
                    '.',
                    ''
                ),

                // ✅ always initialize
                'quantity_sent' => '0.00',
            ]);
        }
    });

    return response()->json([
        'message' => 'Transfer created successfully'
    ]);
}




   public function edit($id)
    {
        // Load transfer with its items and related products/components
        $transfer = InventoryTransfer::with([
            'items.product.category',
            'items.component.category',
        ])->findOrFail($id);

        $currentBranchId = current_branch_id();

        $branches = Branch::where('id', '!=', $currentBranchId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('inventory.transfer.form', [
            'mode' => 'edit',
            'transferType' => $transfer->transfer_type,
            'referenceNo' => $transfer->reference_no,
            'requested_datetime' => $transfer->requested_datetime,
            'branches' => $branches,
            'transfer' => $transfer, // contains items with product/component
        ]);
    }

    public function update(Request $request, $id)
{
    $transfer = InventoryTransfer::findOrFail($id);

    $validated = $request->validate([
        'requested_datetime' => 'required|date',
        'transfer_type' => 'required|in:request,send',

        // Conditional branches
        'destination_id' => 'nullable|exists:branches,id',
        'source_id' => 'nullable|exists:branches,id',

        'items' => 'required|array|min:1',
        'items.*.product_id' => 'nullable|exists:products,id',
        'items.*.component_id' => 'nullable|exists:components,id',
        'items.*.quantity' => 'required|integer|min:1',
    ]);

    DB::transaction(function () use ($transfer, $validated) {

        // -------------------------------
        // Determine source & destination
        // -------------------------------
        if ($validated['transfer_type'] === 'send') {
            if (empty($validated['destination_id'])) {
                throw new \Exception('Destination branch is required for send transfers.');
            }

            $sourceId = current_branch_id();
            $destinationId = $validated['destination_id'];

        } else { // request
            if (empty($validated['source_id'])) {
                throw new \Exception('Source branch is required for request transfers.');
            }

            $sourceId = $validated['source_id'];
            $destinationId = current_branch_id();
        }

        // -------------------------------
        // Update transfer
        // -------------------------------
        $transfer->update([
            'requested_datetime' => Carbon::parse($validated['requested_datetime']),
            'source_id' => $sourceId,
            'destination_id' => $destinationId,
        ]);

        // -------------------------------
        // Replace items
        // -------------------------------
        $transfer->items()->delete();

        foreach ($validated['items'] as $item) {

    $productId   = $item['product_id']   ?? null;
    $componentId = $item['component_id'] ?? null;

    // Ensure only one is set
    if (
        (!$productId && !$componentId) ||
        ($productId && $componentId)
    ) {
        throw new \Exception(
            'Each item must have either product_id or component_id, not both.'
        );
    }

    InventoryTransferItem::create([
        'inventory_transfer_id' => $transfer->id,
        'product_id'   => $productId,
        'component_id' => $componentId,
        'quantity'     => $item['quantity'],
    ]);
}

    });

    return response()->json(['message' => 'Transfer updated successfully']);
}

    public function updateStatus(Request $request, $id)
{
    $transfer = InventoryTransfer::findOrFail($id);

    $validated = $request->validate([
        'status' => 'required|in:pending,approved,in_transit,completed,disapproved,archived',
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

        case 'in_transit':
            $updateData['in_transit_by'] = $userId;
            $updateData['in_transit_datetime'] = $now;
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

    $transfer->update($updateData);

    return response()->json([
        'message' => 'Transfer status updated successfully',
        'status' => $validated['status'],
        'by_name' => $byName,
        'datetime' => $formattedTime,
    ]);
}

public function sendOutForm($id)
{
   $transfer = InventoryTransfer::with([
        'items.product.category',
        'items.product.unit',
        'items.product.branchStockForCurrent',
        'items.component.category',
        'items.component.unit',
        'items.component.branchStockForCurrent',
        'destinationBranch',
    ])->findOrFail($id);

    // ✅ ACTIVE branch (from header / session)
        $currentBranchId = current_branch_id();


        $nextId = (DB::table('inventory_transfer_send_outs')->max('id') ?? 0) + 1;

        $delivery_no = sprintf(
            '%s-%02d-%05d',
            'DR',
            $currentBranchId,
            $nextId
        );

   return view('inventory.transfer.send_out_form', [
            'reference_no' => $transfer->reference_no,
            'requested_datetime' => $transfer->requested_datetime,
            'destination_name' => $transfer->destinationBranch?->name,
            'delivery_no' => $delivery_no,
            'transfer' => $transfer, // contains items with product/component
        ]);
}

   public function storeSendOut(Request $request, $id)
{
    $data = $request->validate([
        'inventory_transfer_id' => 'required|exists:inventory_transfers,id',
        'delivery_request_no'   => 'required|string|unique:inventory_transfer_send_outs,delivery_request_no',
        'personel_name'         => 'required|string',
        'items_onload'          => 'required|array|min:1',
        'items_onload.*.inventory_transfer_item_id' => 'required|exists:inventory_transfer_items,id',
        'items_onload.*.type'   => 'required|in:product,component',
        'items_onload.*.quantity' => 'required|numeric|min:0',
    ]);

    $hasAtLeastOnePositiveQty = collect($data['items_onload'])
        ->contains(fn ($item) => bccomp(
            number_format((float) $item['quantity'], 2, '.', ''),
            '0',
            2
        ) === 1);

    if (!$hasAtLeastOnePositiveQty) {
        abort(422, 'At least one item must have a quantity greater than 0.');
    }

    return DB::transaction(function () use ($data, $id) {

        /** ✅ INIT */
        $itemsOnloadWithStock = [];

        foreach ($data['items_onload'] as $itemData) {

            $qtyToSend = number_format((float) $itemData['quantity'], 2, '.', '');

            if (bccomp($qtyToSend, '0', 2) <= 0) {
                continue;
            }

            $transferItem = InventoryTransferItem::with('component')
                ->lockForUpdate()
                ->findOrFail($itemData['inventory_transfer_item_id']);

            $currentSent = number_format(
                (float) ($transferItem->quantity_sent ?? 0),
                2,
                '.',
                ''
            );

            $newSent = bcadd($currentSent, $qtyToSend, 2);

            if (bccomp($newSent, $transferItem->quantity_requested, 2) === 1) {
                abort(422, "Quantity exceeds requested amount for item ID {$transferItem->id}");
            }

            $transferItem->update([
                'quantity_sent' => $newSent,
            ]);

            $prevOnhand = null;
            $newOnhand  = null;

            /** 🔻 COMPONENT STOCK */
            if ($itemData['type'] === 'component') {

                $component = $transferItem->component;

                if (!$component) {
                    abort(404, 'Component not found.');
                }

                /** ✅ SNAPSHOT BEFORE MUTATION */
                $prevOnhand = number_format(
                    (float) $component->getOriginal('onhand'),
                    2,
                    '.',
                    ''
                );

                if (bccomp($prevOnhand, $qtyToSend, 2) < 0) {
                    abort(422, "Insufficient stock for component: {$component->name}");
                }

                $newOnhand = bcsub($prevOnhand, $qtyToSend, 2);

                $component->onhand = $newOnhand;
                $component->save();
            }

            /** ✅ ENRICH JSON */
            $itemsOnloadWithStock[] = array_merge($itemData, [
                'prev_onhand' => $prevOnhand,
                'new_onhand'  => $newOnhand,
            ]);
        }

        /** ✅ CREATE SEND-OUT WITH FINAL JSON */
        $sendOut = InventoryTransferSendOut::create([
            'inventory_transfer_id' => $data['inventory_transfer_id'],
            'delivery_request_no'   => $data['delivery_request_no'],
            'personel_name'         => $data['personel_name'],
            'items_onload'          => $itemsOnloadWithStock,
        ]);

        /** 🔁 REFRESH TRANSFER */
        $transfer = InventoryTransfer::with('items')
            ->lockForUpdate()
            ->findOrFail($id);

        $totalRequested = $transfer->items->sum('quantity_requested');
        $totalSent      = $transfer->items->sum('quantity_sent');

        $isFullyMet = $transfer->items->every(
            fn ($item) => bccomp(
                (string) ($item->quantity_sent ?? 0),
                (string) $item->quantity_requested,
                2
            ) >= 0
        );

        $updateData = ['status' => 'in_transit'];

        if (is_null($transfer->in_transit_by)) {
            $updateData['in_transit_by'] = auth()->id();
            $updateData['in_transit_datetime'] = now();
        }

        $transfer->update($updateData);

        return response()->json([
            'success' => true,
            'data' => [
                'send_out'       => $sendOut,
                'total_sent'     => $totalSent,
                'total_request'  => $totalRequested,
                'status'         => $isFullyMet ? 'in_transit' : 'in_transit:partial',
                'fully_met'      => $isFullyMet,
            ]
        ]);
    });
}



public function receiveTransfer($id)
{
    $sendOut = DB::transaction(function () use ($id) {

        $transfer = InventoryTransfer::with('items')
            ->lockForUpdate()
            ->findOrFail($id);

        if (
            $transfer->status !== 'in_transit'
            || (int) $transfer->destination_id !== (int) current_branch_id()
        ) {
            abort(403, 'Cannot receive this transfer.');
        }

        foreach ($transfer->items as $item) {
            $sentQty = number_format((float) $item->quantity_sent, 2, '.', '');

            if (bccomp($sentQty, '0', 2) <= 0) continue;

            $branchComponent = BranchComponent::firstOrCreate(
                [
                    'branch_id'    => $transfer->destination_id,
                    'component_id' => $item->component_id,
                ],
                ['onhand' => '0.00']
            );

            $branchComponent->onhand = bcadd(
                number_format((float) $branchComponent->onhand, 2, '.', ''),
                $sentQty,
                2
            );

            $branchComponent->save();
        }

        // Ensure send-out exists
        $sendOut = InventoryTransferSendOut::firstOrCreate(
            ['inventory_transfer_id' => $transfer->id],
            [
                'delivery_request_no' => $transfer->reference_no,
                'personel_name'      => $transfer->requester?->name ?? 'N/A',
                'items_onload'       => $transfer->items->map(fn($i) => [
                    'component_id'  => $i->component_id,
                    'quantity_sent' => $i->quantity_sent,
                ])->toArray(),
            ]
        );

        $sendOut->update([
            'received_by'       => auth()->id(),
            'received_datetime' => now(),
        ]);

        return $sendOut;
    });

    return response()->json([
        'message' => 'Stocks successfully added to inventory.',
        'send_out_id' => $sendOut->id,
    ]);
}

public function getPendingSendOuts($id)
{
    $transfer = InventoryTransfer::with([
        'items.product',
        'items.component',
    ])->findOrFail($id);

    if (
        $transfer->status !== 'in_transit'
        || (int) $transfer->destination_id !== (int) current_branch_id()
    ) {
        abort(403, 'Cannot view send-outs for this transfer.');
    }

    $itemMap = $transfer->items->keyBy('id');

    $sendOuts = InventoryTransferSendOut::where('inventory_transfer_id', $id)
        ->whereNull('received_by')
        ->orderBy('created_at')
        ->get()
        ->map(function ($so) use ($itemMap) {
            $items = collect($so->items_onload ?? [])->map(function ($entry) use ($itemMap) {
                $transferItem = $itemMap->get($entry['inventory_transfer_item_id'] ?? null);
                $isProduct = $transferItem && $transferItem->product_id;
                $source = $isProduct ? $transferItem->product : $transferItem?->component;
                return [
                    'type'     => $entry['type'] ?? ($isProduct ? 'product' : 'component'),
                    'name'     => $source?->name ?? 'N/A',
                    'code'     => $source?->code ?? 'N/A',
                    'quantity' => $entry['quantity'] ?? 0,
                ];
            })->filter(fn($i) => bccomp((string)$i['quantity'], '0', 2) > 0)->values();

            return [
                'id'                  => $so->id,
                'delivery_request_no' => $so->delivery_request_no,
                'personel_name'       => $so->personel_name,
                'created_at'          => optional($so->created_at)->format('Y-m-d H:i:s'),
                'items'               => $items,
            ];
        });

    return response()->json(['send_outs' => $sendOuts]);
}

public function receiveSendOut($sendOutId)
{
    $result = DB::transaction(function () use ($sendOutId) {

        $sendOut = InventoryTransferSendOut::with('transfer.items')
            ->lockForUpdate()
            ->findOrFail($sendOutId);

        if (!is_null($sendOut->received_by)) {
            abort(422, 'This delivery has already been received.');
        }

        $transfer = $sendOut->transfer;

        if (
            $transfer->status !== 'in_transit'
            || (int) $transfer->destination_id !== (int) current_branch_id()
        ) {
            abort(403, 'Cannot receive this delivery.');
        }

        $itemMap = $transfer->items->keyBy('id');

        foreach ($sendOut->items_onload ?? [] as $entry) {
            $qty = number_format((float) ($entry['quantity'] ?? 0), 2, '.', '');
            if (bccomp($qty, '0', 2) <= 0) continue;

            $transferItem = $itemMap->get($entry['inventory_transfer_item_id'] ?? null);
            if (!$transferItem) continue;

            if ($transferItem->product_id) {
                $branchProduct = BranchProduct::firstOrCreate(
                    ['branch_id' => $transfer->destination_id, 'product_id' => $transferItem->product_id],
                    ['quantity' => '0.00', 'price' => '0.00', 'type' => 'simple']
                );
                $branchProduct->quantity = bcadd(
                    number_format((float) $branchProduct->quantity, 2, '.', ''),
                    $qty, 2
                );
                $branchProduct->save();
            } elseif ($transferItem->component_id) {
                $branchComponent = BranchComponent::firstOrCreate(
                    ['branch_id' => $transfer->destination_id, 'component_id' => $transferItem->component_id],
                    ['onhand' => '0.00']
                );
                $branchComponent->onhand = bcadd(
                    number_format((float) $branchComponent->onhand, 2, '.', ''),
                    $qty, 2
                );
                $branchComponent->save();
            }
        }

        $sendOut->update([
            'received_by'       => auth()->id(),
            'received_datetime' => now(),
        ]);

        return $sendOut;
    });

    return response()->json([
        'message'     => 'Delivery received successfully.',
        'send_out_id' => $result->id,
    ]);
}

}