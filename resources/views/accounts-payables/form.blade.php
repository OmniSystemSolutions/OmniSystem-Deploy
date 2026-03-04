@extends('layouts.app')
@section('content')

@php
$detailsArray = $detailsArray ?? [];
@endphp

<div class="main-content" id="app">
    <div>
        <div class="breadcrumb">
            <h1 class="mr-3">{{ $isEdit ? 'Edit' : 'Create' }} Accounts Payable</h1>
            <ul>
                <li><a href="#">Accounting</a></li>
            </ul>
        </div>
        <div class="separator-breadcrumb border-top"></div>
    </div>

    <form id="apForm"
        action="{{ $isEdit 
        ? route('accounts-payables.update', $ap->id) 
        : route('accounts-payables.store') }}" 
        method="POST"
    >

    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

        <div class="wrapper">
            <div class="row">

                {{-- STEP 1 --}}
                <div class="col-xl-3">
                    <div id="step1" class="card p-3">
                        <h5>Step 1: Basic Information</h5>

                        <label for="created_at">Date and Time Created</label>
                        <div class="d-flex">
                            <input type="datetime-local"
                                id="created_at"
                                name="created_at"
                                class="form-control"
                                value="{{ old('created_at', $isEdit ? $ap->created_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                            
                            <button type="button"
                                class="btn btn-secondary ml-2"
                                onclick="document.getElementById('created_at').value = ''">
                                Clear
                            </button>
                        </div>

                        <label>Reference Number *</label>
                        @php
                            $selectedBranchId = old('branch_id', $currentBranchId ?? '');

                            $prefix = 'AP-' . $selectedBranchId . '-';

                            $latestAP = \App\Models\AccountPayable::where('reference_number', 'LIKE', $prefix . '%')
                                ->latest('id')
                                ->first();

                            $nextNumber = $latestAP
                                ? intval(substr($latestAP->reference_number, -6)) + 1
                                : 1;

                            $formattedRef = $isEdit
                                ? $ap->reference_number
                                : $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                        @endphp
                        
                        <input type="text" 
                            name="reference_number" 
                            class="form-control" 
                            value="{{ old('reference_number', $formattedRef) }}" 
                            required>

                        <label class="mt-3">Payor Details *</label>
                        <input type="text" name="payor_details" class="form-control" required
                               value="{{ old('payor_details', $isEdit ? $ap->payor_details : '') }}">
                        <input type="text" name="payer_name" class="form-control mt-1" placeholder="Name"
                               value="{{ old('payer_name', $isEdit ? $ap->payer_name : '') }}">
                        <input type="text" name="payer_company" class="form-control mt-1" placeholder="Company"
                               value="{{ old('payer_company', $isEdit ? $ap->payer_company : '') }}">
                        <input type="text" name="payer_address" class="form-control mt-1" placeholder="Address"
                               value="{{ old('payer_address', $isEdit ? $ap->payer_address : '') }}">
                        <input type="text" name="payer_mobile_number" class="form-control mt-1" placeholder="Mobile #"
                               value="{{ old('payer_mobile_number', $isEdit ? $ap->payer_mobile_number : '') }}">
                        <input type="email" name="payer_email_address" class="form-control mt-1" placeholder="Email"
                               value="{{ old('payer_email_address', $isEdit ? $ap->payer_email_address : '') }}">
                        <input type="text" name="payer_tin" class="form-control mt-1" placeholder="TIN"
                               value="{{ old('payer_tin', $isEdit ? $ap->payer_tin : '') }}">

                        <label class="mt-3">Set Due Date *</label>
                        <input type="date" name="due_date" class="form-control"
                               value="{{ old('due_date', $isEdit && $ap->due_date ? $ap->due_date->format('Y-m-d') : '') }}">

                                 <button id="editStep1Btn"
                type="button"
                class="btn btn-warning btn-sm mt-3 d-none"
                onclick="enableStep1()">
            Edit Basic Information
        </button>

                    </div>
                </div>

                {{-- STEP 2 --}}
                <div class="col-xl-3">
                    <div class="card p-3">
                        <h5>Step 2: Particulars</h5>

                        <label>Account Name</label>
                        <select id="chart_account_id" class="form-control">
                            <option value="" selected disabled>Select Account Name</option>
                            @foreach($categories as $account)
                                <option value="{{ $account->id }}"
                                    data-category="{{ $account->category_name }}"
                                    data-subcategory="{{ $account->subcategory_name }}">
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>

                        <fieldset class="form-group mt-3" id="accountDetails" style="display:none;">
                            <div class="row">
                                <div class="col-6 pr-1">
                                    <legend class="col-form-label pt-0">Category</legend>
                                    <input type="text"
                                        id="categoryDisplay"
                                        class="form-control"
                                        readonly
                                        style="background:#f8f9fa;">
                                </div>

                                <div class="col-6 pl-1">
                                    <legend class="col-form-label pt-0">Sub Category</legend>
                                    <input type="text"
                                        id="subcategoryDisplay"
                                        class="form-control"
                                        readonly
                                        style="background:#f8f9fa;">
                                </div>
                            </div>
                        </fieldset>

                        <label class="mt-3">Description *</label>
                        <textarea id="desc" class="form-control">{{ old('desc') }}</textarea>

                        <label class="mt-3">Quantity *</label>
                        <input type="number" id="qty" class="form-control" value="1">

                        <label class="mt-3">Tax *</label>
                        <select id="tax" class="form-control">
                            <option value="" disabled selected>Select Tax</option>

                            <!-- 12% VAT -->
                            <option value="vat"
                                    data-value="12"
                                    data-type="percentage">
                                VAT (12%)
                            </option>

                            <!-- Non-VAT -->
                            <option value="non-vat"
                                    data-value="0"
                                    data-type="percentage">
                                NON-VAT (0%)
                            </option>

                            <!-- Zero Rated -->
                            <option value="zero-rated"
                                    data-value="0"
                                    data-type="percentage">
                                ZERO-RATED (0%)
                            </option>

                            <!-- Fixed Example (Optional) -->
                            <option value="fixed-100"
                                    data-value="100"
                                    data-type="fixed">
                                Fixed Tax ₱100
                            </option>
                        </select>

                        <label class="mt-3">Amount per Unit *</label>
                        <input type="number" id="amount" class="form-control" value="0">

                        <button type="button" class="btn btn-primary btn-block mt-3" onclick="addToSummary()">
                            Add to Summary
                        </button>
                    </div>
                </div>

                {{-- STEP 3 --}}
                <div class="col-xl-6">
                    <div class="card p-3">
                        <h5>Step 3: Summary</h5>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Tax</th>
                                    <th>Sub Total</th>
                                    <th>Total</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>

                            <tbody id="summaryTable">
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center text-muted">
                                        No data for table
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot id="totalsSection">
                                <tr>
                                    <th colspan="5" class="text-right">Tax Total:</th>
                                    <th colspan="2" id="taxTotalDisplay">₱0.00</th>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Sub-Total:</th>
                                    <th colspan="2" id="subTotalDisplay">₱0.00</th>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Total Amount:</th>
                                    <th colspan="2" id="grandTotalDisplay" class="font-weight-bold">₱0.00</th>
                                </tr>
                            </tfoot>
                        </table>

                        {{-- Hidden input used to submit details --}}
                        <input type="hidden" name="details" id="detailsInput">

                        <div class="text-right mt-3">
                            <button type="button"
                                    class="btn btn-success"
                                    onclick="submitApForm()">
                                {{ $isEdit ? 'Update' : 'Submit' }}
                            </button>

                            <a href="{{ route('accounts-payables.index') }}"
                            class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
  let details = @json($detailsArray);
    document.addEventListener("DOMContentLoaded", () => {
        renderTable();
    });

function addToSummary() {

    const chartAccount = document.getElementById("chart_account_id");
    const descInput = document.getElementById("desc");
    const qtyInput = document.getElementById("qty");
    const taxSelect = document.getElementById("tax");
    const amtInput = document.getElementById("amount");

    const desc = descInput.value.trim();
    const qty = parseFloat(qtyInput.value);
    const amt = parseFloat(amtInput.value);

    // ✅ VALIDATION SECTION
    if (!chartAccount.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Account',
            text: 'Please select an account.'
        });
        return;
    }

    if (!desc) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Description',
            text: 'Please enter a description.'
        });
        return;
    }

    if (!qty || qty <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Quantity',
            text: 'Quantity must be greater than 0.'
        });
        return;
    }

    if (!amt || amt <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Amount',
            text: 'Amount must be greater than 0.'
        });
        return;
    }

    // ✅ Safe Tax Handling
    let tax_id = null;
    let tax_value = 0;
    let tax_type = 'fixed';

    if (taxSelect.value) {
        const selectedTax = taxSelect.selectedOptions[0];
        tax_id = parseInt(selectedTax.value);
        tax_value = parseFloat(selectedTax.dataset.value) || 0;
        tax_type = selectedTax.dataset.type || 'fixed';
    }

    const subtotal = qty * amt;
    let tax_amount = 0;

    if (tax_type === "percentage") {
        tax_amount = subtotal * (tax_value / 100);
    } else {
        tax_amount = tax_value;
    }

    const total_amount = subtotal + tax_amount;

    // ✅ PUSH SAFE OBJECT
    details.push({
        chart_account_id: parseInt(chartAccount.value),
        category_name: chartAccount.selectedOptions[0].dataset.category,
        subcategory_name: chartAccount.selectedOptions[0].dataset.subcategory,
        description: desc,
        quantity: qty,
        tax_id: tax_id,
        amount_per_unit: amt,
        total_amount: total_amount,
        tax_value: tax_value, // frontend only
        tax_type: tax_type    // frontend only
    });

    renderTable();

    // ✅ Reset fields
    chartAccount.value = '';
    descInput.value = '';
    qtyInput.value = 1;
    taxSelect.value = '';
    amtInput.value = '';
}

function renderTable() {
    const tbody = document.getElementById("summaryTable");
    tbody.innerHTML = "";

    if (details.length === 0) {
        tbody.innerHTML = `<tr id="emptyRow"><td colspan="8" class="text-center text-muted">No data for table</td></tr>`;
        return;
    }

    details.forEach((row, i) => {
        const qty = parseFloat(row.quantity) || 0;
        const unit = parseFloat(row.amount_per_unit) || 0;
        const subtotal = qty * unit;

        const taxAmount = row.tax_type === 'percentage'
            ? subtotal * (parseFloat(row.tax_value) / 100)
            : parseFloat(row.tax_value || 0);

        const totalWithTax = subtotal + taxAmount;

        tbody.innerHTML += `
            <tr data-row>
                <td>${row.category_name}</td>
                <td>${row.subcategory_name}</td>
                <td>${row.description}</td>
                <td>${row.quantity}</td>
                <td>₱${taxAmount.toLocaleString(undefined,{minimumFractionDigits:2})}</td>
                <td>₱${subtotal.toLocaleString(undefined,{minimumFractionDigits:2})}</td>
                <td>₱${totalWithTax.toLocaleString(undefined,{minimumFractionDigits:2})}</td>
                <td class="text-right">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${i})">Delete</button>
                </td>
            </tr>
        `;

        // update the row total in details array
        row.total_amount = totalWithTax;
    });

    document.getElementById('detailsInput').value = JSON.stringify(details);
    updateTotals();
}

function removeRow(i) {
    details.splice(i, 1);
    renderTable();
}

function updateTotals() {
 let subTotal = 0;
    let taxTotal = 0;

    details.forEach(d => {
        const qty = parseFloat(d.quantity) || 0;
        const unit = parseFloat(d.amount_per_unit) || 0;
        const taxValue = parseFloat(d.tax_value) || 0;
        const taxType = d.tax_type; // percentage or fixed

        let lineSub = qty * unit;
        let lineTax = 0;

        if (taxType === "percentage") {
            lineTax = lineSub * (taxValue / 100);
        } else {
            lineTax = taxValue;
        }

        subTotal += lineSub;
        taxTotal += lineTax;
    });

    const grandTotal = subTotal + taxTotal;

    // Display formatted values
    document.getElementById("subTotalDisplay").textContent =
        `₱${subTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;

    document.getElementById("taxTotalDisplay").textContent =
        `₱${taxTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;

    document.getElementById("grandTotalDisplay").textContent =
        `₱${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
}

document.getElementById('chart_account_id').addEventListener('change', function () {

    const selected = this.options[this.selectedIndex];

    const category = selected.dataset.category;
    const subcategory = selected.dataset.subcategory;

    document.getElementById('categoryDisplay').value = category || '';
    document.getElementById('subcategoryDisplay').value = subcategory || '';

    document.getElementById('accountDetails').style.display = 'block';
});

document.querySelector('form').addEventListener('submit', function(e) {

    if (details.length < 1) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'No Items Added',
            text: 'Please add at least one summary item before saving.'
        });

        return false;
    }

    document.getElementById('detailsInput').value = JSON.stringify(details);

    Swal.fire({
        title: 'Saving...',
        text: 'Please wait.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});

function disableStep1() {
    const step1 = document.getElementById("step1");
    const editBtn = document.getElementById("editStep1Btn");

    // Make fields read-only
    step1.querySelectorAll("input, select, textarea").forEach(el => {
        el.setAttribute("readonly", true);
    });

    // Fade the card
    step1.style.opacity = "0.6";

    // Disable clicks for everything except the edit button
    step1.querySelectorAll("input, select, textarea, div, label").forEach(el => {
        el.style.pointerEvents = "none";
    });

    // Show the edit button
    editBtn.classList.remove("d-none");

    // Ensure the edit button itself is clickable
    editBtn.style.pointerEvents = "auto";
}


function enableStep1() {
    const step1 = document.getElementById("step1");
    const editBtn = document.getElementById("editStep1Btn");

    // Enable inputs
    step1.querySelectorAll("input, select, textarea").forEach(el => {
        el.removeAttribute("readonly");
    });

    // Restore interaction
    step1.style.opacity = "1";
    step1.style.pointerEvents = "auto";

    // Hide edit button again
    editBtn.classList.add("d-none");
}

function submitApForm() {

    if (details.length < 1) {
        Swal.fire({
            icon: 'warning',
            title: 'No Items Added',
            text: 'Please add at least one summary item before saving.'
        });
        return;
    }

    // Update hidden input
    document.getElementById('detailsInput').value = JSON.stringify(details);

    Swal.fire({
        title: '{{ $isEdit ? "Confirm Update?" : "Confirm Submission?" }}',
        text: 'Please confirm to proceed.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed'
    }).then((result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            document.getElementById('apForm').submit();
        }
    });
}

</script>

@endsection
