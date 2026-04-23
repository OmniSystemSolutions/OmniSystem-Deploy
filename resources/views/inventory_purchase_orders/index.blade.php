@extends('layouts.app')

@section('content')
<div class="main-content">
    {{-- Breadcrumb --}}
    <!-- Scoped styles: make vgt tables more compact to match products index -->
    <style>
        /* match product table compactness */
        table.vgt-table.small, table.vgt-table.table-sm, table.vgt-table.custom-vgt-table {
            font-size: 13px !important;
        }
        table.vgt-table.small td, table.vgt-table.small th,
        table.vgt-table.custom-vgt-table td, table.vgt-table.custom-vgt-table th {
            padding: .4em .6em !important;
        }
        /* tighten modal inner tables as well */
        .modal-body table.table.small td, .modal-body table.table.small th {
            padding: .35em .5em !important;
            font-size: 13px !important;
        }
    </style>
    <div>
        <div class="breadcrumb">
            <h1 class="mr-3">Inventory Purchase Orders</h1>
            <ul>
                <li><a href="">Inventory</a></li>
                <li>Purchase Orders</li>
            </ul>
        </div>
        <div class="separator-breadcrumb border-top"></div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-sm-12 col-md-6 col-lg-3">
            <div class="card card-icon-bg card-icon-bg-primary o-hidden text-center">
                <div class="card-body">
                    <div class="content align-items-center">
                        <p class="text-muted mt-2 mb-0 text-uppercase">Total POs</p>
                        <p class="text-primary text-18 line-height-1 mb-2">{{ $purchaseOrders->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card wrapper">
        <div class="card-body">

            {{-- Tabs --}}
            <nav class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    @foreach(['pending', 'approved', 'completed', 'disapproved', 'archived'] as $tab)
                        <li class="nav-item">
                            <a href="{{ route('inventory_purchase_orders.index', ['status' => $tab]) }}"
                                class="nav-link {{ $status === $tab ? 'active' : '' }}">
                                {{ ucfirst($tab) }}
                            </a>
                        </li>
                    @endforeach 
                </ul>
            </nav>  

            {{-- Actions --}}
            <div class="vgt-global-search vgt-clearfix mt-3">
                <div class="vgt-global-search__input vgt-pull-left">
                    <form role="search">
                        <label for="vgt-search-po">
                            <span aria-hidden="true" class="input__icon">
                                <div class="magnifying-glass"></div>
                            </span>
                        </label>
                        <input id="vgt-search-po" type="text" placeholder="Search this table" class="vgt-input vgt-pull-left">
                    </form>
                </div>

                <div class="vgt-global-search__actions vgt-pull-right">
                    <div class="mt-2 mb-3">
                        <a href="{{ route('inventory_purchase_orders.create') }}" class="btn btn-rounded btn-primary btn-icon m-1">
                            <i class="i-Add"></i> New PO
                        </a>
                    </div>
                </div>
            </div>

            {{-- Main Table --}}
            <div class="vgt-responsive mt-3">
                <table class="table table-hover table-sm vgt-table small">
                    <thead>
                        <tr>
                            <th>Date & Time Created</th>
                            <th>PO #</th>
                            <th>Requested By</th>
                            <th>Department</th>
                            <th>PRF Reference #</th>
                            <th>Type of Request</th>
                            <th>Origin</th>
                            <th>Supplier</th>
                            <th>PO Details</th>
                            @if($status === 'approved')
                                <th>Approved By</th>
                                <th>Date & Time Approved</th>
                            @endif

                            <th>Status</th>

                            @if($status === 'archived')
                                <th>Archived By</th>
                                <th>Date & Time Archived</th>
                            @endif

                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            <tr>
                                <td>{{ $po->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>{{ $po->po_number }}</td>
                                <td>{{ $po->user?->name ?? '—' }}</td>
                                <td>{{ $po->department ?? '—' }}</td>
                                <td>{{ $po->prf_reference_number ?? '—' }}</td>
                                <td>{{ $po->type_of_request ?? '—' }}</td>
                                <td>{{ $po->select_origin ?? '—' }}</td>
                                <td>{{ $po->supplier?->fullname ?? '—' }}</td>
                                <td class="text-right">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info"
                                            onclick="viewPODetails({{ $po->id }})">
                                        <i class="i-Eye"></i> View
                                    </button>
                                </td>

                            </td>
                            {{-- ✅ Approved Tab Columns --}}
                            @if($status === 'approved')
                                <td>
                                    {{ $po->approvedBy?->name ?? 'NA' }}
                                </td>
                                <td>{{ $po->approved_at ? \Carbon\Carbon::parse($po->approved_at)->format('Y-m-d H:i') : '—' }}</td>
                            @endif
                                
                                
                                <td>
                                    <span class="badge 
                                        {{ $po->status === 'pending' ? 'badge-warning' : 
                                            ($po->status === 'approved' ? 'badge-success' : 
                                            ($po->status === 'completed' ? 'badge-primary' : 
                                            ($po->status === 'disapproved' ? 'badge-danger' : 'badge-secondary'))) }}">
                                        {{ ucfirst($po->status) }}
                                    </span>
                                </td>

                        {{-- ✅ Archived Tab Columns --}}
                    @if($status === 'archived')
                        <td>{{ $po->archivedBy?->name ?? '' }}</td>
                        <td>
                            {{ $po->archived_at ? \Carbon\Carbon::parse($po->archived_at)->format('Y-m-d H:i A') : '' }}
                        </td>
                    @endif

                        {{-- ✅ Actions --}}
                            <td class="text-right">
                                    @include('layouts.actions-dropdown', [
                                        'id' => $po->id,
                                        'editRoute' => route('inventory_purchase_orders.edit', $po->id),
                                        'deleteRoute' => route('inventory_purchase_orders.destroy', $po->id),
                                        'archiveRoute' => route('inventory_purchase_orders.archive', $po->id),
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No purchase orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Log Stocks in Inventory Modal --}}
<div class="modal fade" id="logStocksModal" tabindex="-1" aria-labelledby="logStocksModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Log Stocks in Inventory</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="logStocksForm">
          @csrf
          <input type="hidden" name="po_id" id="log_po_id">

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Purchase Order #</label>
              <!-- dynamic PO number -->
              <p id="log_po_number" class="form-control-plaintext text-primary fw-semibold" style="font-size:1rem;">—</p>
            </div>

            <div class="col-md-6 text-end">
              <label class="form-label fw-bold">Date of PO Request</label>
              <!-- dynamic created_at -->
              <p id="log_po_date" class="form-control-plaintext">—</p>
            </div>
          </div>

          <div class="mb-3">
            <label for="date_of_receipt" class="form-label fw-bold">Date and Time of Receipt</label>
            <div class="d-flex align-items-center gap-2">
              <!-- default to current local datetime (JS will set) -->
              <input type="datetime-local" class="form-control" id="date_of_receipt" name="date_of_receipt">
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetReceiptDate()">Clear</button>
            </div>
            <small class="text-muted">Edit the Date/Time if you want to backdate this transaction. Leave blank to record real time.</small>
          </div>

          <div class="mb-3">
            <label for="delivery_dr" class="form-label fw-bold">Delivery Receipt #</label>
            <!-- editable DR field prefilled by JS -->
            <input type="text" class="form-control" id="delivery_dr" name="delivery_dr" placeholder="DR + Branch_ID + 00000">
          </div>

          <h6 class="fw-bold mt-4 mb-2">Receive Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle small">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>SKU</th>
                  <th>Supplier</th>
                  <th>Category</th>
                  <th>Brand</th>
                  <th>Unit</th>
                  <th>Order Qty</th>
                  <th>Received Qty</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="log_items_table">
                <tr><td colspan="9" class="text-center text-muted">Loading items...</td></tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 text-end">
            <button type="submit" class="btn btn-orange btn-primary px-4">
              <i class="i-Yes mr-2"></i> Submit
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
    <!-- Small client-side helpers -->
    <script>
    // Escape HTML to prevent XSS and to safely insert values into template literals
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (s) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]);
        });
    }
    </script>

    <script>
function openLogStocksModal(poId) {
    // show the modal and populate with PO data (matches provided UI design)
    const modalEl = document.getElementById('logStocksModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const poIdInput = document.getElementById('log_po_id');
    const poNumberEl = document.getElementById('log_po_number');
    const poDateEl = document.getElementById('log_po_date');
    const dateOfReceiptEl = document.getElementById('date_of_receipt');
    const deliveryDrEl = document.getElementById('delivery_dr');
    const itemsTbody = document.getElementById('log_items_table');
    const form = document.getElementById('logStocksForm');
    

    poIdInput.value = poId;
    poNumberEl.textContent = '—';
    poDateEl.textContent = '—';
    dateOfReceiptEl.value = new Date().toISOString().slice(0,16);
    deliveryDrEl.value = '';
    itemsTbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Loading items...</td></tr>';

    // Use Laravel-generated URL so fetch works when app is served from a subdirectory
    console.debug('Fetching PO details:', "{{ url('inventory/purchase-orders') }}" + '/' + poId + '/details');
    fetch(`/inventory/purchase-orders/${poId}/details`)
        .then(r => {
            if (!r.ok) {
                return r.text().then(txt => {
                    console.error('Failed to fetch PO details', r.status, txt);
                    throw new Error('Server returned ' + r.status);
                });
            }
            return r.json();
        })
        .then(data => {
            if (!data) throw new Error('PO not found');

            console.log(data.po_number);
            console.log(new Date(data.created_at).toLocaleString());
            
            // assign PO number to the visible element using multiple properties (defensive)
            const poNum = data.po_number ?? '—';
            try {
                poNumberEl.textContent = poNum;
                poNumberEl.innerText = poNum;
                poNumberEl.dataset.poNumber = poNum;
                // ensure it's visible and not impacted by styling
                poNumberEl.style.visibility = 'visible';
            } catch (e) {
                console.error('Failed to assign PO number to element', e);
            }
            console.log('Assigned PO number to UI element:', poNumberEl.textContent);
            poDateEl.textContent = data.created_at ? new Date(data.created_at).toLocaleString() : '—';

            const branchId = data.branch_id ?? null;
            // If we have a branch id, ask the server for the next DR number for that branch
            if (branchId) {
                fetch(`/inventory/purchase-orders/generate-next-dr?branch_id=${encodeURIComponent(branchId)}`)
                    .then(r => {
                        if (!r.ok) return r.text().then(t => { throw new Error(t || r.status); });
                        return r.json();
                    })
                    .then(resp => {
                        if (resp && resp.success && resp.next_dr_number) {
                            deliveryDrEl.value = resp.next_dr_number;
                        } else {
                            // fallback predictable format
                            deliveryDrEl.value = `DR-${branchId}-` + String(0).padStart(6, '0');
                        }
                    })
                    .catch(err => {
                        console.error('Failed to generate next DR number:', err);
                        deliveryDrEl.value = `DR-${branchId}-` + String(0).padStart(6, '0');
                    });
            } else {
                // no branch id available, keep generic placeholder
                deliveryDrEl.value = 'DR-BR-' + String(0).padStart(6, '0');
            }

            const details = Array.isArray(data.details) ? data.details : [];
            if (details.length === 0) {
                itemsTbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No items found.</td></tr>';
                return;
            }

            itemsTbody.innerHTML = '';
            details.forEach(d => {
                const comp = d.component ?? {};
                const totalRequested = Number(d.qty ?? 0);
                const receivedAlready = Number(d.received_qty ?? 0);
                const remaining = Math.max(0, totalRequested - receivedAlready);

                const supplierName = (data && data.supplier && data.supplier.fullname)
                    ? data.supplier.fullname
                    : (comp.fullname ?? comp.supplier ?? '—');

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-start">${escapeHtml(comp.name ?? '—')}</td>
                    <td>${escapeHtml(comp.code ?? '—')}</td>
                    <td>${escapeHtml(supplierName)}</td>
                    <td>${escapeHtml(comp.category ?? '—')}</td>
                    <td>${escapeHtml(comp.brand ?? '—')}</td>
                    <td>${escapeHtml(comp.unit ?? '—')}</td>
                    <td class="text-end">${totalRequested}</td>
                    <td class="text-center">
                        <div class="d-flex flex-column align-items-center">
                            <small class="text-muted mb-1">Received: ${receivedAlready}</small>
                            <div class="input-group input-group-sm" style="width:160px;margin:0 auto;">
                                <button type="button" class="btn btn-orange btn-primary px-3" onclick="decrementQty(this)">-</button>
                                <input type="number" min="0" max="${remaining}" value="0" data-detail-id="${d.id}" class="form-control text-end" />
                                <button type="button" class="btn btn-orange btn-primary px-3" onclick="incrementQty(this)">+</button>
                            </div>
                            <small class="text-muted mt-1">Remaining: ${remaining}</small>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-danger" onclick="removeItemRow(this)">Remove</button>
                        </div>
                    </td>
                `;
                itemsTbody.appendChild(tr);
            });

            form.onsubmit = function(ev) {
                ev.preventDefault();
                submitLogStocks(poId, modal);
            };
        })
        .catch(err => {
            console.error(err);
            itemsTbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading items.</td></tr>';
        });
}

function submitLogStocks(poId, modalInstance) {
    const form = document.getElementById('logStocksForm');
    const tokenInput = form.querySelector('input[name="_token"]');
    const token = tokenInput ? tokenInput.value : document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const dateOfReceipt = document.getElementById('date_of_receipt').value;
    const deliveryDr = document.getElementById('delivery_dr').value;

    const items = [];
    document.querySelectorAll('#log_items_table input[type="number"]').forEach(input => {
        const qty = Number(input.value || 0);
        const detailId = input.dataset.detailId;
        if (qty > 0) items.push({ detail_id: detailId, qty_received: qty });
    });

    if (items.length === 0) {
        alert('Please enter at least one received quantity before submitting.');
        return;
    }

    const payload = { po_id: poId, date_of_receipt: dateOfReceipt, delivery_dr: deliveryDr, items };

    fetch(`/inventory/purchase-orders/${poId}/log-stocks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(resp => {
        if (resp && resp.success) {
            if (modalInstance && typeof modalInstance.hide === 'function') modalInstance.hide();
            window.location.reload();
        } else {
            alert(resp.message || 'Failed to log stocks.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Failed to log stocks. See console for details.');
    });
}

function incrementQty(btn) {
    const input = btn.parentElement.querySelector('input[type="number"]');
    if (!input) return;
    input.value = Math.max(0, Number(input.value || 0) + 1);
}
function decrementQty(btn) {
    const input = btn.parentElement.querySelector('input[type="number"]');
    if (!input) return;
    input.value = Math.max(0, Number(input.value || 0) - 1);
}

function resetReceiptDate() {
    document.getElementById('date_of_receipt').value = '';
}
</script>


<!-- 📁 View Attached Files Modal -->
<div class="modal fade" id="viewAttachmentsModal" tabindex="-1" aria-labelledby="viewAttachmentsLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-white">
        <h5 class="modal-title fw-semibold" id="viewAttachmentsLabel">Attached Files</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="attachmentsList">
          <p class="text-muted mb-0">Loading attachments...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="attachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-semibold" id="attachmentModalLabel">Attach Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="attachmentForm" enctype="multipart/form-data" method="POST" action="{{ route('inventory.purchase_orders.attachments') }}">
                @csrf
                <input type="hidden" name="po_id" id="attachment_po_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Attachments</label>
                        <div id="attachmentList" class="border rounded d-flex align-items-center justify-content-between px-3 py-2 text-muted">
                            No Attachments
                            <button type="button" class="btn btn-link text-danger p-0 ms-2" id="removeFileBtn" style="display:none;">
                                <i class="i-Close-Window"></i>
                            </button>
                        </div>
                        <input type="file" class="form-control mt-2" id="attachmentInput" name="attachments[]" multiple hidden>
                        <button type="button" class="btn btn-light border mt-2 w-100" id="addAttachmentBtn">
                            <i class="i-Add me-1"></i> Add File
                        </button>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-warning text-white px-4">
                        <i class="i-Yes me-1"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ✅ APPROVE MODAL -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-body text-center p-4">
        <div class="mb-3">
          <i class="i-Information text-warning" style="font-size: 48px;"></i>
        </div>
        <h5 class="fw-bold">Are you sure?</h5>
        <p class="text-muted mb-4">Click continue to approve request.</p>

        <form id="approveForm" method="POST">
          @csrf
          @method('PUT')
          <div class="d-flex justify-content-center gap-3">
            <button type="submit" class="btn btn-warning text-white px-4">Continue</button>
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ❌ DISAPPROVE MODAL -->
<div class="modal fade" id="disapproveModal" tabindex="-1" aria-labelledby="disapproveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-body text-center p-4">
        <div class="mb-3">
          <i class="i-Close text-danger" style="font-size: 48px;"></i>
        </div>
        <h5 class="fw-bold">Are you sure?</h5>
        <p class="text-muted mb-4">Click continue to disapprove request.</p>

        <form id="disapproveForm" method="POST">
          @csrf
          @method('PUT')
          <div class="d-flex justify-content-center gap-3">
            <button type="submit" class="btn btn-danger text-white px-4">Continue</button>
            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- 🧾 VIEW PO INVOICE MODAL -->
<div class="modal fade" id="viewPOInvoiceModal" tabindex="-1" aria-labelledby="viewPOInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header bg-white">
                <h5 class="modal-title fw-semibold" id="viewPOInvoiceLabel">Purchase Order Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="poInvoiceContent">
                <div class="text-center text-muted py-5">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-3">Loading Purchase Order...</p>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-warning px-4" onclick="window.print()">Print</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL for PO Details --}}
<div class="modal fade" id="poDetailsModal" tabindex="-1" aria-labelledby="poDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Purchase Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="po-details-content" class="table-responsive text-center py-3">
                    <p>Loading details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Script for modal view --}}
<script>
function viewPODetails(poId) {
    const modalBody = document.getElementById('po-details-content');
    modalBody.innerHTML = '<p>Loading details...</p>';
    const modal = new bootstrap.Modal(document.getElementById('poDetailsModal'));
    modal.show();

    fetch(`/inventory/purchase-orders/${poId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.details && data.details.length > 0) {
                let rows = '';
                data.details.forEach(d => {
                    rows += `
                        <tr>
                            <td>${d.component?.name ?? '—'}</td>
                            <td>${d.component?.code ?? '—'}</td>
                            <td>${d.component?.unit ?? '—'}</td>
                            <td>${parseFloat(d.unit_cost).toFixed(2)}</td>
                            <td>${d.qty}</td>
                            <td>${parseFloat(d.sub_total).toFixed(2)}</td>
                            <td>${data.type_of_request ?? '—'}</td>
                        </tr>`;
                });

                modalBody.innerHTML = `
                    <h6 class="text-start mb-3">PO #: <strong>${data.po_number}</strong></h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Unit</th>
                                <th>Unit Cost</th>
                                <th>Qty</th>
                                <th>Sub-Total</th>
                                <th>Type of Request</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>`;
            } else {
                modalBody.innerHTML = '<p>No items found for this PO.</p>';
            }
        })
        .catch(err => {
            modalBody.innerHTML = `<p class="text-danger">Error loading details.</p>`;
            console.error(err);
        });
}
</script>

<script>
function viewPOInvoice(poId) {
    const modalBody = document.getElementById('po-details-content');
    modalBody.innerHTML = '<p>Loading details...</p>';
    const modal = new bootstrap.Modal(document.getElementById('poDetailsModal'));
    modal.show();

    // Use Laravel-generated URL to avoid absolute-root path issues
    fetch("{{ url('inventory/purchase-orders') }}" + '/' + poId + '/details')
        .then(response => response.json())
        .then(data => {
            if (!data || !data.details || data.details.length === 0) {
                modalBody.innerHTML = '<p>No items found for this PO.</p>';
                return;
            }

            // Compute subtotal and grand total
            let subtotal = 0;
            data.details.forEach(d => subtotal += parseFloat(d.sub_total || 0));
            const tax = subtotal * 0.12;
            const grandTotal = subtotal + tax;

            // Build PO Summary table rows
            let rows = '';
            data.details.forEach(d => {
                rows += `
                    <tr>
                        <td>${d.component?.name ?? '—'}</td>
                        <td>${d.component?.code ?? '—'}</td>
                        <td>${d.component?.category ?? '—'}</td>
                        <td>${d.component?.brand ?? '—'}</td>
                        <td>${d.component?.unit ?? '—'}</td>
                        <td class="text-end">₱${parseFloat(d.unit_cost ?? 0).toFixed(2)}</td>
                        <td class="text-end">${d.qty}</td>
                        <td class="text-end">₱${parseFloat(d.sub_total ?? 0).toFixed(2)}</td>
                    </tr>`;
            });

            // Build full modal content
            modalBody.innerHTML = `
                <div class="po-header text-center mb-4">
                    <h5 class="fw-bold text-uppercase">PURCHASE ORDER</h5>
                    <div class="text-muted small">
                        Date of PO: ${new Date(data.created_at).toLocaleDateString()}<br>
                        Estimated Date of Delivery: —
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>NO.:</strong> ${data.po_number}</p>
                        <p class="mb-0"><strong>Requested by:</strong> ${data.user && data.user.name ? data.user.name : '—'}</p>
                        <p class="mb-0"><strong>Department:</strong> ${data.department ? data.department : '—'}</p>
                        <p><strong>PO Addressed to (Supplier):</strong> ${data.supplier?.fullname ?? '—'}</p>
                        <p><strong>Address:</strong> ${data.supplier?.address ?? '—'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Type of PO:</strong> ${data.type_of_request ?? '—'}</p>
                        <p><strong>Origin:</strong> ${data.select_origin ?? '—'}</p>
                    </div>
                </div>

                <h6 class="fw-bold mt-3 mb-2">PO SUMMARY</h6>
                <table class="table table-bordered align-middle text-sm">
                    <thead class="table-light" style="background-color: skyblue;>
                        <tr class="text-center">
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Unit</th>
                            <th>Unit Cost</th>
                            <th>Qty</th>
                            <th>Sub-Total</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>

                <div class="text-end mt-3 ml-auto" style="max-width: 200px;">
                    <p><strong>SUBTOTAL:</strong> ₱${subtotal.toFixed(2)}</p>
                    <p><strong>VAT (12%):</strong> ₱${tax.toFixed(2)}</p>
                    <h6><strong>GRAND TOTAL:</strong> ₱${grandTotal.toFixed(2)}</h6>
                </div>

                <div class="mt-5">
                    <p class="fw-semibold mb-0">Prepared By:</p>
                <p class="mb-0">{{ auth()->user()->name }}</p>
                </div>

                <div class="mt-5>
                    @if($status === 'approved')
                        <p class="fw-semibold mb-0">Approved By:</p>
                        <p class="mb-0">{{ auth()->user()->name }}</p>
                    @endif
                </div>
            `;
        })
        .catch(err => {
            modalBody.innerHTML = `<p class="text-danger">Error loading details.</p>`;
            console.error(err);
        });
}
</script>

<script>
    let selectedFiles = [];

    function openAttachmentModal(poId) {
        document.getElementById('attachment_po_id').value = poId;
        const modal = new bootstrap.Modal(document.getElementById('attachmentModal'));
        modal.show();
    }

    document.getElementById('addAttachmentBtn').addEventListener('click', () => {
        document.getElementById('attachmentInput').click();
    });

    document.getElementById('attachmentInput').addEventListener('change', function() {
        const list = document.getElementById('attachmentList');
        const removeBtn = document.getElementById('removeFileBtn');
        selectedFiles = Array.from(this.files);

        if (selectedFiles.length > 0) {
            list.innerHTML = selectedFiles.map(f => f.name).join(', ');
            removeBtn.style.display = 'inline-block';
        } else {
            list.innerHTML = 'No Attachments';
            removeBtn.style.display = 'none';
        }
    });

    document.getElementById('removeFileBtn').addEventListener('click', function() {
        selectedFiles = [];
        document.getElementById('attachmentInput').value = '';
        document.getElementById('attachmentList').innerHTML = 'No Attachments';
        this.style.display = 'none';
    });
</script>

<script>
function openViewAttachmentsModal(poId) {
    const modal = new bootstrap.Modal(document.getElementById('viewAttachmentsModal'));
    const container = document.getElementById('attachmentsList');
    container.innerHTML = `<p class="text-muted mb-0">Loading attachments...</p>`;
    modal.show();

    // Use Laravel-generated URL (route uses underscore) so path includes any base folder
    fetch("{{ url('inventory_purchase_orders') }}" + '/' + poId + '/attachments')
        .then(response => response.json())
        .then(data => {
            if (!data.attachments || data.attachments.length === 0) {
                container.innerHTML = `<p class="text-muted mb-0">No attachments found.</p>`;
                return;
            }

            let html = '<div class="list-group">';
            data.attachments.forEach(file => {
                const filename = file.split('/').pop();
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="i-File-PDF text-danger me-2" style="font-size: 1.4rem;"></i>
                            <span>${filename}</span>
                        </div>
                        <div>
                            <a href="/storage/${file}" target="_blank" class="text-decoration-none me-2 text-warning">
                                <i class="i-Download"></i>
                            </a>
                            <a href="javascript:void(0);" class="text-decoration-none text-danger" onclick="deleteAttachment('${file}', ${poId})">
                                <i class="i-Close-Window"></i>
                            </a>
                        </div>
                    </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = `<p class="text-danger">Failed to load attachments.</p>`;
        });
}
</script>

<script>
function approvePO(id) {
    const form = document.getElementById('approveForm');
    form.action = `/inventory/purchase-orders/${id}/approve`;
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function disapprovePO(id) {
    const form = document.getElementById('disapproveForm');
    form.action = `/inventory/purchase-orders/${id}/disapprove`;
    const modal = new bootstrap.Modal(document.getElementById('disapproveModal'));
    modal.show();
}
</script>

@endsection
