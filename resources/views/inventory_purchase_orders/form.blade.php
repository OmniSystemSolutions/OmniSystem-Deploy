<div class="main-content">
<div>
   <div class="breadcrumb">
      <h1 class="mr-3">{{ isset($purchaseOrder) ? 'Edit Purchase Order' : 'Create Purchase Order' }}</h1>
      <ul>
         <li><a href="">Inventory</a></li>
         <li><a href="{{ route('inventory_purchase_orders.index') }}">Purchase Orders</a></li>
      </ul>
      <div class="breadcrumb-action"></div>
   </div>
   <div class="separator-breadcrumb border-top"></div>
</div>
{{-- @foreach($components as $component)
<pre>
    @php var_dump($component); @endphp
</pre>
@endforeach --}}
<div class="wrapper">
   <form 
      action="{{ isset($purchaseOrder) 
      ? route('inventory_purchase_orders.update', $purchaseOrder->id) 
      : route('inventory_purchase_orders.store') }}" 
      method="POST"
      >
      @csrf
      @if(isset($purchaseOrder))
      @method('PUT')
      @endif
      <div class="row">
         <div class="col-sm-12">
            {{-- <div class="row">
               <div class="mt-3 col-md-8"> --}}
                  <div class="card">
                     <div class="card-body">
                        <div class="row">
                           <!-- Date and Time Created -->
                           <div class="col-md-6">
                              <label for="created_at">Date and Time Created</label>
                              <div class="d-flex">
                                 <input type="datetime-local" 
                                    id="created_at" 
                                    name="created_at" 
                                    class="form-control" 
                                    value="{{ old('created_at', isset($purchaseOrder) ? $purchaseOrder->created_at->format('Y-m-d\TH:i') : '') }}">
                                 <button type="button" 
                                    class="btn btn-secondary ml-2"
                                    onclick="document.getElementById('created_at').value = ''">
                                 Clear
                                 </button>
                              </div>
                           </div>
                           <script>
                              document.addEventListener('DOMContentLoaded', function() {
                                  const createdAtInput = document.getElementById('created_at');
                              
                                  // Only autofill if empty (so editing won't be overwritten)
                                  if (!createdAtInput.value) {
                                      const now = new Date();
                              
                                      // Convert to local ISO string by removing the timezone offset
                                      // This yields the correct local date/time for <input type="datetime-local">
                                      const localISO = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                                          .toISOString()
                                          .slice(0, 16); // "YYYY-MM-DDTHH:MM"
                              
                                      createdAtInput.value = localISO;
                                  }
                              });
                           </script>
                           <script>
                              document.addEventListener('DOMContentLoaded', function() {
                                  const branchInput = document.getElementById('branch_id');
                                  const preview = document.getElementById('po_preview');
                              
                                  if (!branchInput || !preview) return;
                              
                                  function updatePreview() {
                                      const bid = branchInput.value;
                                      // If branch id is empty, keep the server-rendered preview
                                      // (this avoids falling back to the literal 'BR')
                                      if (!bid) return;
                                      preview.value = `PO-${bid}-000001`;
                                  }
                              
                                  branchInput.addEventListener('change', updatePreview);
                                  updatePreview();
                              });
                           </script>
                           <!-- PO # -->
                           <div class="col-md-6">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">PO # *</legend>
                                 @if(isset($purchaseOrder))
                                 <input type="text" name="po_number" id="po_number" class="form-control" value="{{ old('po_number', $purchaseOrder->po_number) }}" readonly>
                                 <input type="hidden" name="branch_id" value="{{ $purchaseOrder->branch_id }}">
                                 @else
                                 @php
                                 // Default to the authenticated user's branch if available.
                                 // The controller now provides $currentBranchId (from pivot `branch_user`).
                                 $selectedBranchId = old('branch_id', $currentBranchId ?? '');
                                 $selectedBranch = $branches->firstWhere('id', $selectedBranchId);
                                 @endphp
                                 <div class="d-flex gap-2">
                                    {{-- store branch_id as hidden input so user can't change it on create --}}
                                    <input type="hidden" name="branch_id" id="branch_id" value="{{ $selectedBranchId }}" required>
                                    <input type="text" id="po_preview" class="form-control" value="PO-{{ $selectedBranchId }}-000001">
                                 </div>
                                 @endif
                              </fieldset>
                           </div>
                           <!-- Requested By -->
                           <div class="col-md-6">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">Requested By *</legend>
                                 <select name="user_id" id="user_id" class="form-select" required>
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                    {{ old('user_id', $purchaseOrder->user_id ?? '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                    </option>
                                    @endforeach
                                 </select>
                                 @error('user_id') <small class="text-danger">{{ $message }}</small> @enderror
                              </fieldset>
                           </div>
                           <!-- Department -->
                           <div class="col-md-6">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">Department</legend>
                                 <input type="text" 
                                    name="department" 
                                    id="department" 
                                    class="form-control" 
                                    placeholder="Enter department"
                                    value="{{ old('department', $purchaseOrder->department ?? '') }}">
                              </fieldset>
                           </div>
                           <!-- PRF Reference Number -->
                           <div class="col-md-6">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">PRF Reference #</legend>
                                 <input type="text" 
                                    name="prf_reference_number" 
                                    id="prf_reference_number" 
                                    class="form-control" 
                                    placeholder="Enter PRF reference number"
                                    value="{{ old('prf_reference_number', $purchaseOrder->prf_reference_number ?? '') }}">
                              </fieldset>
                           </div>
                           <!-- Type of Request -->
                           <div class="col-md-6">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">Type of Request *</legend>
                                 <select name="type_of_request" id="type_of_request" class="form-select form-select-sm shadow-none rounded" required>
                                    <option value="">Select Type</option>
                                    <option value="Direct/Indirect Materials" {{ old('type_of_request', $purchaseOrder->type_of_request ?? '') == 'Direct/Indirect Materials' ? 'selected' : '' }}>
                                    Direct/Indirect Materials
                                    </option>
                                    <option value="Consumables Engineering" {{ old('type_of_request', $purchaseOrder->type_of_request ?? '') == 'Consumables Engineering' ? 'selected' : '' }}>
                                    Consumables Engineering
                                    </option>
                                    <option value="Assets" {{ old('type_of_request', $purchaseOrder->type_of_request ?? '') == 'Assets' ? 'selected' : '' }}>
                                    Assets
                                    </option>
                                    <option value="Services" {{ old('type_of_request', $purchaseOrder->type_of_request ?? '') == 'Services' ? 'selected' : '' }}>
                                    Services
                                    </option>
                                 </select>
                              </fieldset>
                           </div>
                           <!-- Select Origin -->
                           <div class="col-md-6">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">Select Origin *</legend>
                                 <select name="select_origin" id="select_origin" class="form-select" required>
                                 <option value="local" {{ old('select_origin', $purchaseOrder->select_origin ?? '') == 'local' ? 'selected' : '' }}>Local</option>
                                 <option value="international" {{ old('select_origin', $purchaseOrder->select_origin ?? '') == 'international' ? 'selected' : '' }}>International</option>
                                 </select>
                              </fieldset>
                           </div>
                           <!-- Proforma Reference Number -->
                           <div class="col-md-6">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">Proforma Reference #</legend>
                                 <input type="text" 
                                    name="proforma_reference_number" 
                                    id="proforma_reference_number" 
                                    class="form-control" 
                                    placeholder="Enter Proforma reference number"
                                    value="{{ old('proforma_reference_number', $purchaseOrder->proforma_reference_number ?? '') }}">
                              </fieldset>
                           </div>
                           <!-- Supplier -->
                           <div class="col-md-6">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">Supplier *</legend>
                                 <select name="supplier_id" id="supplier_id" class="form-select" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                    {{ old('supplier_id', $purchaseOrder->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->fullname }}
                                    </option>
                                    @endforeach
                                 </select>
                                 @error('supplier_id') <small class="text-danger">{{ $message }}</small> @enderror
                              </fieldset>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="container-fluid mt-4">
                     <h3 class="mb-4">
                        {{ isset($purchaseOrder) ? 'Edit Purchase Order (PO)' : 'Create Purchase Order (PO)' }}
                     </h3>
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
                                    <th>Current Onhand Quantity</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @foreach($components as $component)
                                 <tr>
                                    <td>
                                       <input 
                                       type="checkbox" 
                                       class="component-checkbox" 
                                       data-id="{{ $component->id }}" 
                                       data-name="{{ $component->name }}" 
                                       data-sku="{{ $component->code }}" 
                                       data-supplier="{{ $component->supplier->fullname ?? 'Open' }}" 
                                       data-category="{{ $component->category->name ?? 'N/A' }}" 
                                       data-brand="{{ $component->brand ?? '-' }}" 
                                       data-unit="{{ $component->unit->name ?? '-' }}" 
                                       data-onhand="{{ $component->onhandForCurrentBranch() ?? 0 }}"
                                       @if(isset($purchaseOrder) && $purchaseOrder->details->pluck('component.id')->contains($component->id))
                                       checked
                                       @endif
                                       >
                                    </td>
                                    <td>{{ $component->name }}</td>
                                    <td>{{ $component->code }}</td>
                                    <td>{{ $component->supplier->fullname ?? 'Open' }}</td>
                                    <td>{{ $component->category->name ?? 'N/A' }}</td>
                                    <td>{{ $component->brand ?? '-' }}</td>
                                    <td>{{ $component->unit->name ?? '-' }}</td>
                                    <td>{{ $component->onhandForCurrentBranch() ?? 0 }}</td>
                                 </tr>
                                 @endforeach
                              </tbody>
                           </table>
                        </div>
                     </div>
                     <!-- Summary -->
                     <div class="card">
                        <div class="card-header">Summary</div>
                            <div class="card-body">+<div class="table-responsive">
                                <table class="table" id="summaryTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>SKU</th>
                                            <th>Supplier</th>
                                            <th>Category</th>
                                            <th>Brand</th>
                                            <th>Unit</th>
                                            <th>Current Onhand Quantity</th>
                                            <th>Cost per Unit</th>
                                            <th>Order Qty</th>
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
                     <!-- Submit -->
                     <div class="b-overlay-wrap position-relative d-inline-block btn-loader mt-3">
                        <button type="submit" class="btn btn-primary">
                        <i class="i-Yes me-2"></i>
                        {{ isset($purchaseOrder) ? 'Update Purchase Order' : 'Create Purchase Order' }}
                        </button>
                        <a href="{{ route('inventory_purchase_orders.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                     </div>
                  </div>
               {{-- </div>
            </div> --}}
         </div>
   </form>
   </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.component-checkbox');
    const summaryBody = document.querySelector('#summaryTable tbody');
    const selectAll = document.getElementById('selectAll');
    const TAX_RATE = 0.12; // 12% VAT

    // Select All toggle
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
                cb.dispatchEvent(new Event('change'));
            });
        });
    }

    // Add/remove rows dynamically
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const id = this.dataset.id;

            // Prevent duplicate rows
            if (this.checked && summaryBody.querySelector(`tr[data-id="${id}"]`)) return;

            if (this.checked) {
                const row = `
                    <tr data-id="${id}">
                        <td>
                            ${this.dataset.name}
                            <input type="hidden" name="components[${id}][id]" value="${id}">
                        </td>
                        <td>${this.dataset.sku}</td>
                        <td>${this.dataset.supplier}</td>
                        <td>${this.dataset.category}</td>
                        <td>${this.dataset.brand}</td>
                        <td>${this.dataset.unit}</td>
                        <td>${this.dataset.onhand}</td>

                        <td>
                            <input type="number" 
                                name="components[${id}][unit_cost]" 
                                class="form-control cost" 
                                value="0" min="0" step="0.01">
                        </td>

                        <td>
                            <div class="input-group input-group-sm" style="width:160px;margin:0 auto;">
                                <button type="button" class="btn btn-primary px-3" onclick="decrementQty(this)">-</button>
                                <input type="number" 
                                    name="components[${id}][qty]" 
                                    class="form-control text-center qty" 
                                    value="1" 
                                    min="1">
                                <button type="button" class="btn btn-primary px-3" onclick="incrementQty(this)">+</button>
                            </div>
                        </td>

                        <td class="tax">₱0.00</td>
                        <td class="subtotal">₱0.00</td>

                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                        </td>
                    </tr>`;
                summaryBody.insertAdjacentHTML('beforeend', row);
                calculateTotals();
            } else {
                summaryBody.querySelector(`tr[data-id="${id}"]`)?.remove();
                calculateTotals();
            }
        });
    });

    // Remove individual row
    summaryBody.addEventListener('click', e => {
        if (e.target.classList.contains('remove-row')) {
            const tr = e.target.closest('tr');
            const id = tr.dataset.id;
            document.querySelector(`.component-checkbox[data-id="${id}"]`).checked = false;
            tr.remove();
            calculateTotals();
        }
    });

    // Recalculate tax/subtotal when unit_cost or qty changes
    summaryBody.addEventListener('input', e => {
        if (e.target.classList.contains('cost') || e.target.classList.contains('qty')) {
            calculateTotals();
        }
    });

    // ✅ Calculate totals
    function calculateTotals() {
        summaryBody.querySelectorAll('tr').forEach(row => {
            const cost = parseFloat(row.querySelector('.cost').value) || 0;
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const subtotal = cost * qty;
            const tax = subtotal * TAX_RATE;

            row.querySelector('.subtotal').textContent = `₱${subtotal.toFixed(2)}`;
            row.querySelector('.tax').textContent = `₱${tax.toFixed(2)}`;
        });
    }

    // Pre-fill existing components if editing
    @if(isset($purchaseOrder))
        const existingDetails = @json($purchaseOrder->details);
        existingDetails.forEach(detail => {
            const checkbox = document.querySelector(`.component-checkbox[data-id="${detail.component.id}"]`);
            if (!checkbox) return;

            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change'));

            const row = summaryBody.querySelector(`tr[data-id="${detail.component.id}"]`);
            if (row) {
                row.querySelector('.cost').value = detail.unit_cost ?? 0;
                row.querySelector('.qty').value = detail.qty ?? 1;
                calculateTotals(); // recalc immediately
            }
        });
    @endif

    // Increment/decrement functions
    window.incrementQty = function(btn) {
        const input = btn.parentElement.querySelector('input[type="number"]');
        if (!input) return;
        input.value = Number(input.value || 0) + 1;
        input.dispatchEvent(new Event('input'));
    };

    window.decrementQty = function(btn) {
        const input = btn.parentElement.querySelector('input[type="number"]');
        if (!input) return;
        input.value = Math.max(1, Number(input.value || 0) - 1);
        input.dispatchEvent(new Event('input'));
    };
});
</script>