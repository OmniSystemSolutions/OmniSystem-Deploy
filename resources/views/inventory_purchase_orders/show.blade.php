@extends('layouts.app')

@section('content')
<div class="main-content">
    <div>
        <div class="breadcrumb">
            <h1 class="mr-3">Purchase Orders</h1>
            <ul>
                <li><a href="{{ route('inventory_purchase_orders.index') }}">Inventory Purchase Orders</a></li>
                <li>{{ $purchaseOrder->po_number }}</li>
            </ul>
        </div>
        <div class="separator-breadcrumb border-top"></div>
    </div>

    <div class="card wrapper mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="font-weight-bold mb-0">Purchase Order — {{ $purchaseOrder->po_number }}</h5>
            <span class="badge
                {{ $purchaseOrder->status === 'pending'     ? 'badge-warning'   :
                   ($purchaseOrder->status === 'approved'   ? 'badge-success'   :
                   ($purchaseOrder->status === 'completed'  ? 'badge-primary'   :
                   ($purchaseOrder->status === 'disapproved'? 'badge-danger'    : 'badge-secondary'))) }}
                px-3 py-2" style="font-size:.85rem;">
                {{ ucfirst($purchaseOrder->status) }}
            </span>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="180">PO Number</th>
                            <td>{{ $purchaseOrder->po_number }}</td>
                        </tr>
                        <tr>
                            <th>Requested By</th>
                            <td>{{ $purchaseOrder->user?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Department</th>
                            <td>{{ $purchaseOrder->department ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>PRF Reference #</th>
                            <td>{{ $purchaseOrder->prf_reference_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Type of Request</th>
                            <td>{{ $purchaseOrder->type_of_request ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Origin</th>
                            <td>{{ $purchaseOrder->select_origin ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="180">Supplier</th>
                            <td>{{ $purchaseOrder->supplier?->supplier_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Date Created</th>
                            <td>{{ $purchaseOrder->created_at?->format('F d, Y g:i A') ?? '—' }}</td>
                        </tr>
                        @if($purchaseOrder->approved_at)
                        <tr>
                            <th>Approved By</th>
                            <td>{{ $purchaseOrder->approvedBy?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Date Approved</th>
                            <td>{{ \Carbon\Carbon::parse($purchaseOrder->approved_at)->format('F d, Y g:i A') }}</td>
                        </tr>
                        @endif
                        @if($purchaseOrder->archived_at)
                        <tr>
                            <th>Archived By</th>
                            <td>{{ $purchaseOrder->archivedBy?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Date Archived</th>
                            <td>{{ \Carbon\Carbon::parse($purchaseOrder->archived_at)->format('F d, Y g:i A') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <h6 class="font-weight-bold mb-3">PO Items</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Unit</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Sub-Total</th>
                            <th class="text-right">Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrder->details as $detail)
                            <tr>
                                <td>{{ $detail->component?->name ?? '—' }}</td>
                                <td>{{ $detail->component?->code ?? '—' }}</td>
                                <td>{{ $detail->component?->category?->name ?? '—' }}</td>
                                <td>{{ $detail->component?->brand ?? '—' }}</td>
                                <td>{{ $detail->component?->unit?->name ?? '—' }}</td>
                                <td class="text-right">₱{{ number_format($detail->unit_cost, 2) }}</td>
                                <td class="text-right">{{ $detail->qty }}</td>
                                <td class="text-right">₱{{ number_format($detail->sub_total, 2) }}</td>
                                <td class="text-right">{{ $detail->received_qty ?? 0 }} / {{ $detail->qty }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($purchaseOrder->details->count() > 0)
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-right">Grand Total</th>
                            <th class="text-right">₱{{ number_format($purchaseOrder->details->sum('sub_total'), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <div class="mt-3">
                <a href="{{ route('inventory_purchase_orders.index') }}" class="btn btn-outline-secondary">
                    <i class="i-Arrow-Left mr-1"></i> Back to List
                </a>
                <a href="{{ route('inventory_purchase_orders.edit', $purchaseOrder->id) }}" class="btn btn-warning ml-2">
                    <i class="i-Edit mr-1"></i> Edit
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
