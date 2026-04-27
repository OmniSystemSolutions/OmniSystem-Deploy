@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Create Purchase Ordering (PO)</h3>

    <div class="card mb-4">
        <div class="card-body">
            <h5><strong>Purchase Order - {{ $purchaseOrder->po_number }}</strong></h5>
            <p><strong>Requestor’s Name:</strong> {{ $purchaseOrder->user->name ?? 'N/A' }}</p>
            <p><strong>Department:</strong> {{ $purchaseOrder->department ?? '-' }}</p>
            <p><strong>PRF Reference #:</strong> {{ $purchaseOrder->prf_reference_number ?? '-' }}</p>
            <p><strong>Proforma Reference #:</strong> {{ $purchaseOrder->proforma_reference_number ?? '-' }}</p>
            <p><strong>Type of Request:</strong> {{ $purchaseOrder->type_of_request ?? '-' }}</p>
            <p><strong>Origin:</strong> {{ ucfirst($purchaseOrder->select_origin) }}</p>
            <p><strong>Date of Request:</strong> {{ $purchaseOrder->created_at->format('F d, Y') }}</p>
            <p><strong>Time of Request:</strong> {{ $purchaseOrder->created_at->format('g:i A') }}</p>
            <p><strong>Ship To:</strong> {{ $purchaseOrder->branch->name ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- Product Selection -->
    <div class="card mb-4">
        <div class="card-header">Select Items</div>
        <div class="card-body">
            <table class="table table-hover" id="componentsTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Supplier</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($components as $component)
                    <tr>
                        <td><input type="checkbox" class="component-checkbox" data-id="{{ $component->id }}" data-name="{{ $component->name }}" data-sku="{{ $component->code }}" data-supplier="{{ $component->supplier->supplier_name ?? 'Open' }}" data-category="{{ $component->category->name ?? 'N/A' }}" data-brand="{{ $component->brand ?? '-' }}" data-unit="{{ $component->unit }}"></td>
                        <td>{{ $component->name }}</td>
                        <td>{{ $component->code }}</td>
                        <td>{{ $component->supplier->supplier_name ?? 'Open' }}</td>
                        <td>{{ $component->category->name ?? 'N/A' }}</td>
                        <td>{{ $component->brand ?? '-' }}</td>
                        <td>{{ $component->unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary -->
    <div class="card">
        <div class="card-header">Summary</div>
        <div class="card-body">
            <table class="table" id="summaryTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Supplier</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Unit</th>
                        <th>Cost per Unit</th>
                        <th>Qty</th>
                        <th>Tax</th>
                        <th>Sub-Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.component-checkbox');
    const summaryBody = document.querySelector('#summaryTable tbody');
    const selectAll = document.getElementById('selectAll');

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => {
            cb.checked = selectAll.checked;
            cb.dispatchEvent(new Event('change'));
        });
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (this.checked) {
                const row = `
                    <tr data-id="${this.dataset.id}">
                        <td>${this.dataset.name}</td>
                        <td>${this.dataset.sku}</td>
                        <td>${this.dataset.supplier}</td>
                        <td>${this.dataset.category}</td>
                        <td>${this.dataset.brand}</td>
                        <td>${this.dataset.unit}</td>
                        <td><input type="number" class="form-control cost" value="0"></td>
                        <td><input type="number" class="form-control qty" value="1"></td>
                        <td class="tax">₱0.00</td>
                        <td class="subtotal">₱0.00</td>
                        <td><button class="btn btn-danger btn-sm remove-row">Remove</button></td>
                    </tr>`;
                summaryBody.insertAdjacentHTML('beforeend', row);
            } else {
                summaryBody.querySelector(`tr[data-id="${this.dataset.id}"]`)?.remove();
            }
        });
    });

    // Remove row
    summaryBody.addEventListener('click', e => {
        if (e.target.classList.contains('remove-row')) {
            const id = e.target.closest('tr').dataset.id;
            document.querySelector(`.component-checkbox[data-id="${id}"]`).checked = false;
            e.target.closest('tr').remove();
        }
    });
});
</script>
@endsection
