@extends('layouts.app')
@section('content')
<style>
   .dropdown-menu {
   position: relative !important;
   transform: translate(0px, 0px) !important;
   }
   /* ===== Layout View (Serving tab) ===== */
   #layout-canvas { min-height: 500px; min-width: 800px; }
</style>
<div class="main-content" id="orderTypeApp">
   <div>
      <div class="breadcrumb">
         <h1 class="mr-3">Orders</h1>
         <ul>
            <li><a href=""> POS </a></li>
            <!----> <!---->
         </ul>
         <div class="breadcrumb-action"></div>
      </div>
      <div class="separator-breadcrumb border-top"></div>
   </div>
   <!-- 🧾 ORDER TYPE MODAL -->
   <div class="modal fade" id="orderTypeModal" tabindex="-1" aria-labelledby="orderTypeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content" id="orderTypeApp">
            <!-- Header -->
            <div class="modal-header">
               <h5 class="modal-title" id="orderTypeModalLabel">Select Order Type</h5>
            </div>
            <!-- Body -->
            <div class="modal-body">
               <div class="form-group mb-3">
                  <label for="orderType">Order Type</label>
                  <select class="form-control" v-model="orderType" id="orderType">
                     <option value="Dine-In">Dine-In</option>
                     <option value="Take-Out">Take-Out</option>
                     <option value="Delivery">Delivery</option>
                  </select>
               </div>
            </div>
            <!-- Footer -->
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
               <button class="btn btn-primary" @click="submitOrderType">Continue</button>
            </div>
         </div>
      </div>
   </div>
   {{-- Layout: Order Detail Modal (single instance, populated by JS) --}}
   @if($status === 'serving')
   <div class="modal fade" id="layoutOrderDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Order Details</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
               <div class="row mb-3 g-2" id="lodm-header"></div>
               <div id="lodm-details"></div>
            </div>
            <div class="modal-footer">
               <a id="lodm-edit-btn" href="#" class="btn btn-primary btn-sm">Edit Order</a>
               <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>
   @endif

   <!---->
   <div class="wrapper">
      <div class="card mt-4">
         <nav class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
               <li class="nav-item">
                  <a href="{{ route('orders.index', ['status' => 'serving']) }}" 
                     class="nav-link {{ $status === 'serving' ? 'active' : '' }}">
                  Serving
                  </a>
               </li>
               {{-- 
               <li class="nav-item">
                  <a href="{{ route('orders.index', ['status' => 'served']) }}" 
                     class="nav-link {{ $status === 'served' ? 'active' : '' }}">
                  Served
                  </a>
               </li>
               --}}
               <li class="nav-item">
                  <a href="{{ route('orders.index', ['status' => 'billout']) }}" 
                     class="nav-link {{ $status === 'billout' ? 'active' : '' }}">
                  Bill Out
                  </a>
                  @foreach($orders as $order)
                  <!-- Payment Modal (moved here so it's outside nav links) -->
                  <div class="modal fade" id="paymentModal{{ $order->id }}" tabindex="-1" aria-labelledby="paymentLabel{{ $order->id }}" aria-hidden="true">
                     <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                           <div class="modal-header">
                              <h5 class="modal-title">Payment - Order #{{ $order->id }}</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                           </div>
                           <div class="modal-body">
                              <div class="container-fluid">
                                 <div class="row mb-2">
                                    <div class="col-md-2 offset-md-2">
                                       <label class="form-label">Order No</label>
                                       <input type="text" class="form-control" value="{{ $order->id }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                       <label class="form-label">No of Pax</label>
                                       <input type="text" class="form-control" value="{{ $order->number_pax }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                       <label class="form-label">Date & Time</label>
                                       <input type="text" class="form-control" value="{{ $order->created_at->format('Y-m-d H:i') }}" readonly>
                                    </div>
                                 </div>
                                 <div class="row mb-2">
                                    <div class="col-md-2 offset-md-2">
                                       <label class="form-label">Table No</label>
                                       <input type="text" class="form-control" value="{{ $order->table_no }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                       <label class="form-label">Waiter</label>
                                       <input type="text" class="form-control" value="{{ $order->user?->name }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                       <label class="form-label">Status</label>
                                       <input type="text" class="form-control" value="{{ ucfirst($order->status) }}" readonly>
                                    </div>
                                 </div>
                                 <hr>
                                 <h6 class="fw-bold text-center">GROSS CHARGE</h6>
                                 <div class="row mb-3">
                                    <div class="col-md-4 offset-md-4">
                                       <input type="text" class="form-control text-center fw-bold" 
                                          value="₱{{ number_format($order->details->sum(fn($d) => ($d->price * $d->quantity) - ($d->discount ?? 0)), 2) }}" readonly>
                                    </div>
                                 </div>
                                 {{-- Charges and discounts: prefill from order if billout else blank --}}
                                 <div class="row mb-3">
                                    <div class="col-md-3">
                                       <label class="form-label">SR/PWD Bill</label>
                                       <input type="text" id="pay_srPwdBill_{{ $order->id }}" class="form-control" value="{{ $order->sr_pwd_bill ?? '' }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                       <label class="form-label">Net Bill</label>
                                       <input type="text" id="pay_netBill_{{ $order->id }}" class="form-control" value="{{ $order->net_amount ?? '' }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                       <label class="form-label">Reg Bill</label>
                                       <input type="text" id="pay_regBill_{{ $order->id }}" class="form-control" value="{{ $order->regular_bill ?? '' }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                       <label class="form-label">Other Discount</label>
                                       <input type="text" id="pay_otherDiscount_{{ $order->id }}" class="form-control" value="{{ $order->other_discounts ?? '' }}" readonly>
                                    </div>
                                 </div>
                                 <div class="row mb-3">
                                    <div class="col-md-3">
                                       <label class="form-label">Vatable</label>
                                       <input type="text" id="pay_vatable_{{ $order->id }}" class="form-control" value="{{ $order->vatable ?? '' }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                       <label class="form-label">VAT 12%</label>
                                       <input type="text" id="pay_vat12_{{ $order->id }}" class="form-control" value="{{ $order->vat_12 ?? '' }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                       <label class="form-label fw-bold">TOTAL CHARGE</label>
                                       <input type="text" id="pay_totalCharge_{{ $order->id }}" class="form-control fw-bold text-success" value="{{ $order->total_charge ?? '' }}" readonly>
                                    </div>
                                 </div>
                                 <hr>
                                 <div class="row mb-2">
                                    <div class="col-md-12 d-flex justify-content-between align-items-center">
                                       <div>
                                          <strong>Mode of Payment</strong>
                                       </div>
                                       <div>
                                          <button type="button" class="btn btn-sm btn-warning" onclick="addPaymentRow({{ $order->id }})">Add</button>
                                       </div>
                                    </div>
                                 </div>
                                 <!-- Payments table -->
                                 <div class="row mb-3">
                                    <div class="col-md-12">
                                       <div class="table-responsive">
                                          <table class="table table-sm table-bordered">
                                             <thead>
                                                <tr>
                                                   <th>Payment Method</th>
                                                   {{-- 
                                                   <th>Transaction Reference #</th>
                                                   --}}
                                                   <th>Payment Destination</th>
                                                   <th>Transaction Reference #</th>
                                                   <th class="text-end">Amount Paid</th>
                                                   <th>Action</th>
                                                </tr>
                                             </thead>
                                             <tbody id="payments_table_body_{{ $order->id }}">
                                                <!-- rows will be appended here -->
                                             </tbody>
                                             <tfoot>
                                                <tr>
                                                   <td colspan="3" class="text-end"><strong>Total Payment Rendered</strong></td>
                                                   <td class="text-end"><strong id="payments_total_{{ $order->id }}">0.00</strong></td>
                                                   <td></td>
                                                </tr>
                                                <tr>
                                                   <td colspan="3" class="text-end"><strong>Change</strong></td>
                                                   <td class="text-end"><strong id="payments_change_{{ $order->id }}">0.00</strong></td>
                                                   <td></td>
                                                </tr>
                                             </tfoot>
                                          </table>
                                       </div>
                                       <input type="hidden" id="payments_counter_{{ $order->id }}" value="0">
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="modal-footer justify-content-between flex-wrap gap-2">
                              <div class="d-flex gap-2">
                                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                 <button type="button" class="btn btn-primary" onclick="submitPayment({{ $order->id }})">
                                 Submit
                                 </button>
                              </div>
                              <!-- Right side: primary action -->
                              <button type="button" class="btn btn-warning px-4" onclick="openBillOutFromPayment({{ $order->id }})">
                                 <!-- important: close current modal first -->
                                 Revert to Bill-Out
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
                  @endforeach
                  </a>
               </li>
               <li class="nav-item">
                  <a href="{{ route('orders.index', ['status' => 'payments']) }}"
                     class="nav-link {{ $status === 'payments' ? 'active' : '' }}">
                  Payment
                  </a>
               </li>
            </ul>
         </nav>
            @if($status === 'serving')
            <div class="d-flex" style="flex-direction: row-reverse; gap:10px; margin: 20px;">
                <div class="btn-group" id="servingViewToggle">
                    <button type="button" class="btn btn-sm btn-outline-secondary active" id="btn-tabular" onclick="setServingView('tabular')">
                    <i class="i-Table2"></i> Tabular
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-layout" onclick="setServingView('layout')">
                    <i class="i-Map2"></i> Layout
                    </button>
                </div>
            </div>
            {{-- ===== LAYOUT VIEW — sibling of vgt-wrap, outside table structure ===== --}}
            <div id="layout-view" style="display:none; padding:15px 20px">
            {{-- Toolbar: layout selector + legend --}}
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <select id="layoutSelect" class="form-select form-select-sm" style="width:220px;" onchange="loadSelectedLayout()">
                    <option value="">-- Select a layout --</option>
                </select>
                <div class="cv-legend ms-3">
                    <span><span class="cv-dot" style="background:#f44336;"></span> Occupied</span>
                    <span><span class="cv-dot" style="background:#42a5f5;"></span> Available</span>
                </div>
            </div>
            {{-- Floor tabs --}}
            <div id="layout-floors" class="d-flex gap-2 mb-2 flex-wrap"></div>
            {{-- Canvas --}}
            <div style="overflow:auto; border:1px solid #ddd; border-radius:8px; background:#f5f5f5;">
                <div id="layout-canvas"></div>
            </div>
            </div>
            @endif
         <div class="card-body">
            <!----><!---->
            <div id="vgt-order-wrap" class="vgt-wrap ">
               <!----> 
               <div class="vgt-inner-wrap">
                  <!----> 
                  <div class="vgt-global-search vgt-clearfix">
                     <div class="vgt-global-search__input vgt-pull-left">
                        <form role="search">
                           <label for="vgt-search-352530096888">
                              <span aria-hidden="true" class="input__icon">
                                 <div class="magnifying-glass"></div>
                              </span>
                              <span class="sr-only">Search</span>
                           </label>
                           <input id="vgt-search-352530096888" type="text" placeholder="Search this table" class="vgt-input vgt-pull-left">
                        </form>
                     </div>
                     <div class="vgt-global-search__actions vgt-pull-right">
                        <div class="d-flex align-items-center gap-2">
                           <!-- ADD ORDER BUTTON -->
                           <button
                              type="button"
                              class="btn mx-1 btn-btn btn-primary btn-icon"
                              data-bs-toggle="modal"
                              data-bs-target="#orderTypeModal">
                           <i class="i-Add"></i> Add
                           </button>
                        </div>
                     </div>
                  </div>
                  <!----> 
                  <div class="vgt-fixed-header">
                     <!---->
                  </div>
                  <div id="tabular-view" class="vgt-responsive" style="max-height: 400px; overflow-y: auto;">
                     <table id="vgt-table" class="table-hover tableOne vgt-table custom-vgt-table ">
                        <colgroup>
                           <col id="col-0">
                           <col id="col-1">
                           <col id="col-2">
                           <col id="col-3">
                           <col id="col-4">
                           <col id="col-5">
                           <col id="col-6">
                           <col id="col-7">
                           <col id="col-8">
                           <col id="col-9">
                           <col id="col-10">
                           <col id="col-11">
                           <col id="col-12">
                           <col id="col-13">
                           <col id="col-14">
                           <col id="col-15">
                           <col id="col-16">
                        </colgroup>
                        <thead style="min-width: auto; width: auto;">
                           <tr>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>Show Details</span>
                              </th>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>Order No.</span>
                                 <button><span class="sr-only">Sort table by Product No in descending order</span></button>
                              </th>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>Order Type</span>
                                 <button><span class="sr-only">Sort table by Order Type in descending order</span></button>
                              </th>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>Waiter Name</span>
                                 <button><span class="sr-only">Sort table by Waiter Name in descending order</span></button>
                              </th>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>Table No.</span>
                                 <button><span class="sr-only">Sort table by Table No in descending order</span></button>
                              </th>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>Number Of Pax</span>
                                 <button><span class="sr-only">Sort table by Number of Pax in descending order</span></button>
                              </th>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>Status</span>
                                 <button><span class="sr-only">Sort table by Status in descending order</span></button>
                              </th>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>Amount</span>
                                 <button><span class="sr-only">Sort table by Amount Price in descending order</span></button>
                              </th>
                              <th scope="col" class="vgt-left-align text-right">
                                 <span>Action</span>
                              </th>
                           </tr>
                        </thead>
                        <tbody>
                           @php
                           $filteredOrders = $orders;
                           if ($status === 'serving') {
                           $filteredOrders = $orders->filter(fn ($o) =>
                           in_array($o->status, ['serving', 'served'])
                           && $o->branch_id === $branch->id
                           );
                           } elseif ($status) {
                           $filteredOrders = $orders->where('status', $status)->where('branch_id', $branch->id);;
                           }
                           @endphp
                           @forelse ($filteredOrders as $order)
                           <tr x-data="{ open: false }">
                              <!-- Checkbox -->
                              <td>
                                 <input type="checkbox" class="toggle-details" data-id="{{ $order->id }}">
                              </td>
                              <!-- Order Data -->
                              {{-- 
                              <td class="text-left">{{ $order->id }}</td>
                              <td class="text-left">{{ $order->order_type }}</td>
                              --}}
                              <!-- Order Data -->
                              <td class="text-left">
                                 {{ $order->id }}
                                 @if(!empty($order->reservation_id))
                                 <br>
                                 <span class="badge badge-warning" style="font-size:10px; white-space:nowrap;">
                                 <i class="i-Calendar"></i>
                                 From Reservation
                                 @if($order->reservation)
                                 · {{ $order->reservation->reference_number }}
                                 @endif
                                 </span>
                                 @endif
                              </td>
                              <td class="text-left">{{ $order->order_type }}</td>
                              <td class="text-left">{{ $order->user?->name ?? 'N/A' }}</td>
                              <td class="text-left">{{ $order->table_no }}</td>
                              <td class="text-left">{{ $order->number_pax }}</td>
                              <td class="text-left">{{ ucfirst($order->status) }}</td>
                              {{-- 
                              <td class="text-left" id="amount_{{ $order->id }}"> --}}
                              <td class="text-left fw-bold {{ $order->status === 'billout' ? 'text-success' : '' }}">
                                 @php 
                                 $grandTotal = 0; 
                                 $totalItems = 0;
                                 foreach ($order->details as $detail) {
                                 $lineTotal = $detail->quantity * $detail->price; 
                                 $grandTotal += $lineTotal; 
                                 $totalItems += $detail->quantity;
                                 }
                                 if ($order->status === 'billout') {
                                 $displayTotal = $order->total_charge ?? $grandTotal;
                                 } else {
                                 $displayTotal = $grandTotal;
                                 }
                                 @endphp
                                 ₱{{ number_format($displayTotal, 2) }}
                              </td>
                              {{-- Actions --}}
                              <td class="text-right">
                                 @include('layouts.actions-dropdown', [
                                 'id' => $order->id,
                                 'editRoute' => route('orders.edit', $order->id),
                                 'viewRoute' => '#',
                                 'cancelRoute' => '#',
                                 'deleteRoute' => '#',
                                 'remarksRoute' => '#',
                                 'status' => '#',
                                 // Show label/modal depending on current page tab ($status):
                                 // serving => 'Bill out' (opens billOut modal)
                                 // billout  => 'Payment' (opens payment modal)
                                 // payments => 'View Receipt' (opens invoice modal)
                                 'viewLabel' => ($status === 'serving') ? 'Bill out' : (($status === 'billout') ? 'Payment' : (($status === 'payments') ? 'View Receipt' : 'View')),
                                 // target appropriate modal depending on page status
                                 'viewModalId' => ($status === 'serving') ? "billOutModal{$order->id}" : (($status === 'billout') ? "paymentModal{$order->id}" : (($status === 'payments') ? "invoiceModal{$order->id}" : null)),
                                 'billOutPreviewModalId' => ($status === 'billout') ? "billOutPreviewModal{$order->id}" : null,
                                 'deleteLabel' => 'Cancel',
                                 ])
                              </td>
                           </tr>
                           <!-- Hidden Details Row -->
                           <tr id="details-{{ $order->id }}" class="order-details" style="display:none;">
                              <td colspan="7">
                                 <div style="max-height:200px; overflow-y:auto; padding:10px; border:1px solid #ddd; background:#f9f9f9;">
                                    <table style="width:100%; border-collapse:collapse;">
                                       <thead style="border-bottom:1px solid #ccc; background:#e9e9e9;">
                                          <tr>
                                             <th>SKU</th>
                                             <th>Product</th>
                                             <th>Qty</th>
                                             <th>Amount</th>
                                             <th>Status</th>
                                          </tr>
                                       </thead>
                                       <tbody>
                                          @foreach($order->details as $detail)
                                          <tr>
                                             <td>
                                                {{ $detail->product?->code ?? $detail->component?->code ?? 'N/A' }}
                                             </td>
                                             <td>
                                                {{ $detail->item_name }}
                                             </td>
                                             <td>
                                                {{ $detail->quantity }}
                                             </td>
                                             <td>
                                                ₱{{ number_format($detail->price, 2) }}
                                             </td>
                                             <td>
                                                {{ $detail->status }}
                                             </td>
                                          </tr>
                                          @endforeach
                                       </tbody>
                                    </table>
                                 </div>
                              </td>
                           </tr>
                           @empty
                           <tr>
                              <td colspan="9" class="vgt-center-align vgt-text-disabled">
                                 No orders found
                              </td>
                           </tr>
                           @endforelse
                        </tbody>
                  </div>
                  {{-- end #tabular-view --}}
                  </td></tr></tbody></table>
                  @foreach($orders as $order)
                  <div class="modal fade" id="billOutModal{{ $order->id }}" tabindex="-1" aria-labelledby="billOutLabel{{ $order->id }}" aria-hidden="true">
                     <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                           {{-- Modal Header --}}
                           <div class="modal-header">
                              <h5 class="modal-title">Bill Out - Order #{{ $order->id }}</h5>
                           </div>
                           {{-- Modal Body --}}
                           <div class="modal-body">
                              <div class="container-fluid">
                                 {{-- Order Info --}}
                                 <div class="row mb-2">
                                    <div class="col-md-2 offset-md-1">
                                       <label class="form-label text-center w-100">Order No</label>
                                       <input type="text" class="form-control text-center" value="{{ $order->id }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                       <label class="form-label text-center w-100">No of Pax</label>
                                       <input type="text" class="form-control text-center" value="{{ $order->number_pax }}" readonly>
                                    </div>
                                    <div class="col-md-3">
                                       <label class="form-label text-center w-100">Date & Time</label>
                                       <input type="text" class="form-control text-center" value="{{ $order->created_at->format('Y-m-d H:i') }}" readonly>
                                    </div>
                                    <div class="col-md-2">
                                       <label class="form-label text-center w-100">Cashier</label>
                                       <input type="text" class="form-control text-center" value="{{ $order->cashier?->name ?? auth()->user()->name }}" readonly>
                                    </div>
                                 </div>
                                 <div class="row mb-2">
                                    {{-- 
                                    <div class="col-md-2">
                                       <label class="form-label">Table No</label>
                                       <input type="text" class="form-control" value="{{ $order->table_no }}" readonly> --}}
                                       <div class="col-md-2 offset-md-2">
                                          <label class="form-label text-center w-100">Table No</label>
                                          <input type="text" class="form-control text-center" value="{{ $order->table_no }}" readonly>
                                       </div>
                                       <div class="col-md-3">
                                          <label class="form-label text-center w-100">Waiter</label>
                                          <input type="text" class="form-control text-center" value="{{ $order->user?->name }}" readonly>
                                       </div>
                                       <div class="col-md-2">
                                          <label class="form-label text-center w-100">Status</label>
                                          <input type="text" class="form-control text-center" value="{{ ucfirst($order->status) }}" readonly>
                                       </div>
                                    </div>
                                    {{-- Gross Charge --}}
                                    <hr>
                                    <h6 class="fw-bold text-center">GROSS CHARGE</h6>
                                    <div class="row mb-3">
                                       <div class="col-md-4 offset-md-4">
                                          <input type="text" class="form-control text-center fw-bold" 
                                             value="₱{{ number_format($order->details->sum(fn($d) => ($d->price * $d->quantity) - ($d->discount ?? 0)), 2) }}" readonly>
                                       </div>
                                    </div>
                                    @php
                                    // compute gross for this order explicitly (per-order, not a shared var)
                                    $orderGross = $order->details->sum(fn($d) => ($d->price * $d->quantity) - ($d->discount ?? 0));
                                    @endphp
                                    {{-- Entries Section --}}
                                    {{-- 
                                    <div class="col-md-5 position-relative">
                                       <label class="form-label">Discount</label>
                                       <div class="d-flex align-items-center gap-2">
                                          <input type="hidden" name="discount_ids_{{ $order->id }}" id="discountIds_{{ $order->id }}">
                                          <div class="col-md-12 p-0">
                                             <input type="text" id="selectedDiscountName_{{ $order->id }}"
                                                class="form-control"
                                                placeholder="Select discounts..."
                                                onclick="toggleDiscountDropdown({{ $order->id }})"
                                                readonly>
                                          </div>
                                          <button type="button"
                                             class="btn btn-outline-primary btn-sm manage-btn"
                                             onclick="toggleDiscountForm({{ $order->id }})">
                                          Manage
                                          </button>
                                       </div>
                                       <div id="discountDropdown_{{ $order->id }}"
                                          class="border rounded p-2 position-absolute bg-white w-100"
                                          style="display:none; max-height:200px; overflow-y:auto; z-index:100;">
                                          @foreach($discounts as $discount)
                                          <div class="form-check">
                                             <input class="form-check-input discount-checkbox-single" type="checkbox"
                                                value="{{ $discount->id }}"
                                                data-name="{{ $discount->name }}"
                                                data-value="{{ $discount->value }}"
                                                onchange="updateSelectedDiscounts({{ $order->id }})"
                                                id="discountCheck_{{ $order->id }}_{{ $discount->id }}">
                                             <label class="form-check-label" for="discountCheck_{{ $order->id }}_{{ $discount->id }}">
                                             {{ $discount->name }} ({{ $discount->value }}%)
                                             </label>
                                          </div>
                                          @endforeach
                                       </div>
                                       <input type="hidden" name="discount_ids_{{ $order->id }}" id="discountIds_{{ $order->id }}">
                                    </div>
                                    --}}
                                    {{-- Entries Section --}}
                                    <div class="row mb-3">
                                       <div class="col-md-10 position-relative">
                                          <label class="form-label">Discount <span class="text-danger">*</span></label>
                                          <div class="d-flex align-items-start gap-2">
                                             <input type="hidden" name="discount_ids_{{ $order->id }}" id="discountIds_{{ $order->id }}">
                                             <div class="flex-grow-1 position-relative">
                                                <!-- Selected Tags Display -->
                                                <div class="form-control discount-select-container" 
                                                   id="discountSelectContainer_{{ $order->id }}"
                                                   onclick="if (!event.target.closest('.discount-tag-close')) toggleDiscountDropdown({{ $order->id }})"
                                                   style="min-height: 38px; max-height: 76px; overflow-y: auto; cursor: pointer; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; padding: 6px 10px;">
                                                   <span class="text-muted" id="discountPlaceholder_{{ $order->id }}" style="margin: 4px;">Select discounts...</span>
                                                </div>
                                                <!-- Dropdown Menu -->
                                                <div id="discountDropdown_{{ $order->id }}"
                                                   class="discount-dropdown-menu border rounded bg-white shadow-sm"
                                                   style="display:none; position: absolute; top: 100%; left: 0; right: 0; max-height: 250px; overflow-y: auto; z-index: 1050; margin-top: 2px;">
                                                   @foreach($discounts as $discount)
                                                   <div class="discount-dropdown-item" 
                                                      onclick="toggleDiscountSelection({{ $order->id }}, {{ $discount->id }}, '{{ $discount->name }}', {{ $discount->value }})"
                                                      style="padding: 10px 12px; cursor: pointer; transition: background-color 0.15s ease;">
                                                      <div class="form-check mb-0">
                                                         <input class="form-check-input discount-checkbox-single" 
                                                            type="checkbox"
                                                            value="{{ $discount->id }}"
                                                            data-name="{{ $discount->name }}"
                                                            data-value="{{ $discount->value }}"
                                                            id="discountCheck_{{ $order->id }}_{{ $discount->id }}"
                                                            style="pointer-events: none;">
                                                         <label class="form-check-label" 
                                                            for="discountCheck_{{ $order->id }}_{{ $discount->id }}"
                                                            style="cursor: pointer; user-select: none; font-size: 14px;">
                                                         {{ $discount->name }} ({{ $discount->value }}%)
                                                         </label>
                                                      </div>
                                                   </div>
                                                   @endforeach
                                                </div>
                                             </div>
                                             <button type="button"
                                                class="btn btn-outline-primary btn-sm manage-btn"
                                                onclick="toggleDiscountForm({{ $order->id }})"
                                                style="white-space: nowrap; margin-top: 0;">
                                             Manage
                                             </button>
                                          </div>
                                       </div>
                                    </div>
                                    {{-- css --}}
                                    <style>
                                       /* Payment destination validation states */
                                       select.form-select[style*="border-color: rgb(220, 53, 69)"],
                                       select.form-select[style*="border-color: #dc3545"] {
                                       border-color: #dc3545 !important;
                                       background-color: #fff5f5;
                                       }
                                       select.form-select[style*="border-color: rgb(25, 135, 84)"],
                                       select.form-select[style*="border-color: #198754"] {
                                       border-color: #198754 !important;
                                       background-color: #f0fff4;
                                       }
                                       /* Error message in select */
                                       select.form-select option:first-child[value=""]:only-child {
                                       color: #dc3545;
                                       font-weight: 500;
                                       }
                                       .discount-select-container .discount-tag {
                                       font-size: 13px;
                                       padding: 2px 6px;
                                       border-radius: 4px;
                                       }
                                       /* Submit button disabled state */
                                       #submitBtn_{{ $order->id }}:disabled {
                                       opacity: 0.6;
                                       cursor: not-allowed;
                                       }
                                       #submitBtn_{{ $order->id }}:not(:disabled) {
                                       opacity: 1;
                                       cursor: pointer;
                                       }
                                       /* Discount Select Styles */
                                       .discount-select-container {
                                       position: relative;
                                       transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
                                       }
                                       .discount-select-container:hover {
                                       border-color: #86b7fe;
                                       }
                                       .discount-select-container::-webkit-scrollbar {
                                       width: 8px;
                                       }
                                       .discount-select-container::-webkit-scrollbar-track {
                                       background: #f1f1f1;
                                       border-radius: 4px;
                                       }
                                       .discount-select-container::-webkit-scrollbar-thumb {
                                       background: #c1c1c1;
                                       border-radius: 4px;
                                       }
                                       .discount-select-container::-webkit-scrollbar-thumb:hover {
                                       background: #a8a8a8;
                                       }
                                       .discount-tag {
                                       display: inline-flex;
                                       align-items: center;
                                       padding: 6px 10px;
                                       background-color: #e7f3ff;
                                       border: 1px solid #b3d9ff;
                                       border-radius: 4px;
                                       font-size: 13px;
                                       gap: 8px;
                                       white-space: nowrap;
                                       margin: 2px;
                                       }
                                       .discount-tag-close {
                                       cursor: pointer;
                                       font-weight: bold;
                                       color: #0066cc;
                                       padding: 0 4px;
                                       border-radius: 3px;
                                       transition: background-color 0.15s ease;
                                       line-height: 1;
                                       font-size: 18px;
                                       }
                                       .discount-tag-close:hover {
                                       background-color: rgba(0, 102, 204, 0.15);
                                       color: #004080;
                                       }
                                       .discount-dropdown-menu {
                                       border: 1px solid #dee2e6 !important;
                                       }
                                       .discount-dropdown-item {
                                       display: flex;
                                       align-items: center;
                                       }
                                       .discount-dropdown-item:hover {
                                       background-color: #f0f7ff;
                                       }
                                       .discount-dropdown-item .form-check {
                                       width: 100%;
                                       margin-bottom: 0;
                                       }
                                       .discount-dropdown-item .form-check-input:checked {
                                       background-color: #0d6efd;
                                       border-color: #0d6efd;
                                       }
                                       .discount-dropdown-item:not(:last-child) {
                                       border-bottom: 1px solid #f0f0f0;
                                       }
                                       /* Scrollbar styling for dropdown */
                                       .discount-dropdown-menu::-webkit-scrollbar {
                                       width: 6px;
                                       }
                                       .discount-dropdown-menu::-webkit-scrollbar-track {
                                       background: #f1f1f1;
                                       }
                                       .discount-dropdown-menu::-webkit-scrollbar-thumb {
                                       background: #888;
                                       border-radius: 3px;
                                       }
                                       .discount-dropdown-menu::-webkit-scrollbar-thumb:hover {
                                       background: #555;
                                       }
                                       /* Manage button alignment */
                                       .manage-btn {
                                       height: 38px;
                                       align-self: flex-start;
                                       }
                                       /* Button disabled state */
                                       #calculateBtn_{{ $order->id }}:disabled {
                                       opacity: 0.6;
                                       cursor: not-allowed;
                                       }
                                       #calculateBtn_{{ $order->id }}:not(:disabled) {
                                       opacity: 1;
                                       cursor: pointer;
                                       }
                                    </style>
                                    <script>
                                       // Toggle discount dropdown visibility
                                       function toggleDiscountDropdown(orderId) {
                                           const dropdown = document.getElementById('discountDropdown_' + orderId);
                                           const isVisible = dropdown.style.display === 'block';
                                           
                                           // Close all other dropdowns
                                           document.querySelectorAll('[id^="discountDropdown_"]').forEach(dd => {
                                               if (dd.id !== 'discountDropdown_' + orderId) {
                                                   dd.style.display = 'none';
                                               }
                                           });
                                           
                                           dropdown.style.display = isVisible ? 'none' : 'block';
                                       }
                                       
                                       // Close dropdown when clicking outside
                                       document.addEventListener('click', function(event) {
                                           const target = event.target;
                                           const isDropdownClick = target.closest('[id^="discountSelectContainer_"]') || 
                                                                  target.closest('[id^="discountDropdown_"]');
                                           
                                           if (!isDropdownClick) {
                                               document.querySelectorAll('[id^="discountDropdown_"]').forEach(dd => {
                                                   dd.style.display = 'none';
                                               });
                                           }
                                       });
                                       
                                       // Toggle discount selection
                                       // Toggle discount selection
                                       function toggleDiscountSelection(orderId, discountId, discountName, discountValue) {
                                           const checkbox = document.getElementById(`discountCheck_${orderId}_${discountId}`);
                                           const hiddenInput = document.getElementById('discountIds_' + orderId);
                                       
                                           // Toggle the checkbox
                                           checkbox.checked = !checkbox.checked;
                                       
                                           // Update visual tags
                                           updateSelectedDiscountTags(orderId);
                                       
                                           // ────────────────────────────────────────────────
                                           // NEW: Auto-close dropdown after selection
                                           const dropdown = document.getElementById('discountDropdown_' + orderId);
                                           if (dropdown) {
                                               dropdown.style.display = 'none';
                                           }
                                           // ────────────────────────────────────────────────
                                       
                                           // Update button states
                                           setTimeout(() => {
                                               updateCalculateButtonState(orderId);
                                           }, 100);
                                       }
                                       
                                       // Update the visual tags display
                                       function updateSelectedDiscountTags(orderId) {
                                           const container = document.getElementById('discountSelectContainer_' + orderId);
                                           const placeholder = document.getElementById('discountPlaceholder_' + orderId);
                                           const hiddenInput = document.getElementById('discountIds_' + orderId);
                                           
                                           // Get all checked checkboxes
                                           const checkedBoxes = document.querySelectorAll(
                                               `#discountDropdown_${orderId} input[type="checkbox"]:checked`
                                           );
                                           
                                           // Clear container
                                           container.innerHTML = '';
                                           
                                           if (checkedBoxes.length === 0) {
                                               // Show placeholder
                                               const placeholderSpan = document.createElement('span');
                                               placeholderSpan.className = 'text-muted';
                                               placeholderSpan.id = `discountPlaceholder_${orderId}`;
                                               placeholderSpan.textContent = 'Select discounts...';
                                               container.appendChild(placeholderSpan);
                                               hiddenInput.value = '';
                                           } else {
                                               // Create tags for selected items
                                               const selectedIds = [];
                                               checkedBoxes.forEach(checkbox => {
                                                   const discountName = checkbox.dataset.name;
                                                   const discountValue = checkbox.dataset.value;
                                                   const discountId = checkbox.value;
                                                   
                                                   selectedIds.push(discountId);
                                                   
                                                   const tag = document.createElement('span');
                                                   tag.className = 'discount-tag';
                                                   tag.innerHTML = `
                                                       ${discountName}
                                                       <span class="discount-tag-close" 
                                                             onclick="removeDiscountTag(event, ${orderId}, ${discountId})"
                                                             title="Remove">×</span>
                                                   `;
                                                   container.appendChild(tag);
                                               });
                                               
                                               // Update hidden input
                                               hiddenInput.value = selectedIds.join(',');
                                           }
                                       }
                                       
                                       // Remove individual discount tag
                                       function removeDiscountTag(event, orderId, discountId) {
                                           event.stopPropagation();
                                           
                                           const checkbox = document.getElementById(`discountCheck_${orderId}_${discountId}`);
                                           if (checkbox) {
                                               checkbox.checked = false;
                                               updateSelectedDiscountTags(orderId);
                                               
                                               // Clear saved data for this discount
                                               if (savedDiscountPersons[orderId] && savedDiscountPersons[orderId][discountId]) {
                                                   delete savedDiscountPersons[orderId][discountId];
                                               }
                                               
                                               // Update button state when discount is removed
                                               setTimeout(() => {
                                                   updateCalculateButtonState(orderId);
                                               }, 100);
                                           }
                                       }
                                       
                                       // Update the old function to use the new logic
                                       function updateSelectedDiscounts(orderId) {
                                           updateSelectedDiscountTags(orderId);
                                       }
                                    </script>
                                 </div>
                                 <!-- Apply Discount form (rendered by toggle) -->
                                 <div id="discountForm_{{ $order->id }}" class="border rounded p-3 mt-3" style="display:none;">
                                    <h6 class="text-center mb-3">Manage Discount</h6>
                                    <div id="selectedDiscountsContainer_{{ $order->id }}"></div>
                                    <div class="d-flex justify-content-center mt-3">
                                       <button type="button" class="btn btn-danger btn-sm px-3" onclick="toggleDiscountForm({{ $order->id }})">
                                       Close
                                       </button>
                                    </div>
                                 </div>
                                 <!-- Other Charges / Calculate -->
                                 <div class="row mb-2">
                                    <div class="col-md-4 mt-3 offset-md-2">
                                       <label class="form-label">Other Charges</label>
                                       <input type="number" class="form-control" id="otherCharges_{{ $order->id }}" name="other_charges" placeholder="Enter Amount">
                                    </div>
                                    <div class="col-md-4 mt-3">
                                       <label class="form-label">Charges Description</label>
                                       <input type="text" class="form-control" id="chargesDescription_{{ $order->id }}" name="charges_description" placeholder="Description">
                                    </div>
                                 </div>
                                 <div class="row mb-3">
                                    <div class="col-md-12 text-center">
                                       <button type="button" 
                                          class="btn btn-success" 
                                          id="calculateBtn_{{ $order->id }}"
                                          onclick="calculateChargesAndDiscounts({{ $order->id }}, {{ json_encode((float)$orderGross) }}, {{ $order->number_pax }})">
                                       Calculate Charges and Discounts
                                       </button>
                                    </div>
                                 </div>
                                 <form id="billOutForm_{{ $order->id }}" 
                                    action="{{ route('orders.billout', $order->id) }}" 
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                                    <!-- CHARGES AND DISCOUNTS fields (keep your IDs) -->
                                    <hr>
                                    <h6 class="fw-bold text-center">CHARGES AND DISCOUNTS</h6>
                                    <div class="row">
                                       <div class="col-md-4">
                                          <label class="form-label">SR/PWD Bill</label>
                                          <input type="text" id="srPwdBill_{{ $order->id }}" class="form-control" readonly>
                                       </div>
                                       <div class="col-md-4">
                                          <label class="form-label">Reg Bill</label>
                                          <input type="text" id="regBill_{{ $order->id }}" class="form-control" readonly>
                                       </div>
                                       <div class="col-md-4">
                                          <label class="form-label">20% Discount</label>
                                          <input type="text" id="discount20_{{ $order->id }}" class="form-control" readonly>
                                       </div>
                                    </div>
                                    <input type="hidden" id="grossCharge_{{ $order->id }}" value="{{ number_format($order->details->sum(fn($d) => ($d->price * $d->quantity) - ($d->discount ?? 0)), 2) }}"class="form-control" readonly>
                                    <div class="row mt-2">
                                       <div class="col-md-4">
                                          <label class="form-label">Vatable</label>
                                          <input type="text" id="vatable_{{ $order->id }}" class="form-control" readonly>
                                       </div>
                                       <div class="col-md-4">
                                          <label class="form-label">VAT 12%</label>
                                          <input type="text" id="vat12_{{ $order->id }}" class="form-control" readonly>
                                       </div>
                                       <div class="col-md-4">
                                          <label class="form-label">Net Bill</label>
                                          <input type="text" id="netBill_{{ $order->id }}" class="form-control" readonly>
                                       </div>
                                    </div>
                                    <div class="row mt-2">
                                       <div class="col-md-4">
                                          <label class="form-label">Other Discount</label>
                                          <input type="text" id="otherDiscount_{{ $order->id }}" class="form-control" readonly>
                                       </div>
                                       <div class="col-md-8">
                                          <label class="form-label fw-bold">TOTAL CHARGE</label>
                                          <input type="text" id="totalCharge_{{ $order->id }}" class="form-control fw-bold text-success" readonly>
                                       </div>
                                    </div>
                                    {{-- Modal Footer (Submit inside form now) --}}
                                    <div class="modal-footer justify-content-start">
                                       <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                       <button type="button" 
                                          class="btn btn-primary"
                                          id="submitBtn_{{ $order->id }}"
                                          onclick="confirmBillOut({{ $order->id }})">
                                       Submit
                                       </button>
                                    </div>
                                 </form>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            @endforeach
            <!----><!---->
            @if($status === 'serving')
            {{-- ===== LAYOUT VIEW — sibling of vgt-wrap, outside table structure ===== --}}
            <div id="layout-view" style="display:none; padding:15px 20px; margin-left: 10%;">
            {{-- Toolbar: layout selector + legend --}}
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <select id="layoutSelect" class="form-select form-select-sm" style="width:220px;" onchange="loadSelectedLayout()">
                    <option value="">-- Select a layout --</option>
                </select>
                <div class="cv-legend ms-3">
                    <span><span class="cv-dot" style="background:#f44336;"></span> Occupied</span>
                    <span><span class="cv-dot" style="background:#42a5f5;"></span> Available</span>
                </div>
            </div>
            {{-- Floor tabs --}}
            <div id="layout-floors" class="d-flex gap-2 mb-2 flex-wrap"></div>
            {{-- Canvas --}}
            <div style="overflow:auto; border:1px solid #ddd; border-radius:8px; background:#f5f5f5;">
                <div id="layout-canvas"></div>
            </div>
            </div>
            @endif
            <!-- Bill Out Preview Modal -->
            @foreach($orders as $order)
            <div class="modal fade" id="billOutPreviewModal{{ $order->id }}" tabindex="-1" aria-labelledby="billOutPreviewLabel{{ $order->id }}" aria-hidden="true">
               <div class="modal-dialog modal-sm modal-dialog-scrollable">
                  <div class="modal-content">
                     <div class="modal-body p-0">
                        <div style="max-width:400px; margin:0 auto; font-family:Arial,Helvetica,sans-serif; font-size:13px; line-height:1.4;">
                           <!-- Header - aligned with receipt -->
                           <div class="text-center">
                              <div class="invoice_logo mb-2">
                                 <img src="/images/logo-default.png" alt="Omni Logo" width="60" height="60">
                              </div>
                              <div class="d-flex flex-column small">
                                 <span class="t-font-boldest">{{ $branch->name ?? 'omni' }}</span>
                                 <span>{{ $branch->address ?? 'Main Commisary, 123 Main St, Cityville' }}</span>
                                 <span>Permit #: {{ $branch->permit_number ?? '' }}</span>
                                 <span>DTI Issued: {{ $branch->dti_issued ?? '' }}</span>
                                 <span>POS SN: {{ $branch->pos_sn ?? '' }}</span>
                                 <span>MIN#: {{ $branch->min_number ?? '' }}</span>
                              </div>
                              <h6 class="t-font-boldest mt-3 mb-1">BILL-OUT SLIP</h6>
                              <div class="mb-1">OR #: {{ sprintf('%08d', $order->id) }}</div>
                              <div class="mb-1">Date: {{ $order->created_at->format('Y-m-d H:i') }}</div>
                              <div class="mb-1">TBL: {{ $order->table_no ?? '—' }}</div>
                              <div class="mb-2"># of Pax: {{ $order->number_pax ?? '—' }}</div>
                           </div>
                           <!-- Items table - same style as receipt -->
                           <table class="table table-invoice-items m-0" style="width:100%; font-size:13px; border-collapse:collapse;">
                              <thead>
                                 <tr>
                                    <th style="text-align:left; width:10%;">QTY</th>
                                    <th style="text-align:left; width:60%;">DESCRIPTION</th>
                                    <th style="text-align:right; width:30%;">AMOUNT</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @foreach($order->details as $d)
                                 <tr>
                                    <td>{{ $d->quantity }}x</td>
                                    <td>
                                       <div class="d-flex flex-column">
                                          <span>{{ $d->item_name }}</span>
                                          <span style="font-size:11px; color:#666;">@₱{{ number_format($d->price, 2) }}</span>
                                       </div>
                                    </td>
                                    <td style="text-align:right;">₱{{ number_format($d->quantity * $d->price, 2) }}</td>
                                 </tr>
                                 @endforeach
                              </tbody>
                           </table>
                           <hr style="margin:8px 0;">
                           <!-- Summary - matched order & labels from receipt -->
                           @php
                           // Compute reg bill server-side so it matches the JS calculation
                           $payGross = $order->gross_amount ?? $order->details->sum(fn($d) => ($d->price * $d->quantity) - ($d->discount ?? 0));
                           $payPax = max(1, (int)($order->number_pax ?? 1));
                           $payPerPax = $payGross / $payPax;
                           $payQualified = 0;
                           foreach ($order->discountEntries ?? [] as $de) {
                           $dname = strtolower(optional($de->discount)->name ?? '');
                           if (str_contains($dname, 'senior') || str_contains($dname, 'pwd')) {
                           $payQualified += $de->quantity ?? 1;
                           }
                           }
                           $payQualified = min($payQualified, $payPax);
                           $payRegBill = $payPerPax * ($payPax - $payQualified);
                           @endphp
                           <table class="table table-invoice-data m-0" style="width:100%; font-size:13px;">
                              <tbody>
                                 <tr>
                                    <td>Gross Charge</td>
                                    <td class="text-right gross-charge">
                                       ₱{{ number_format($order->details->sum(fn($d) => $d->quantity * $d->price - ($d->discount ?? 0)), 2) }}
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>Less Discount</td>
                                    <td class="text-right less-discount">
                                       @php
                                       $grossCharge = $order->gross_amount;
                                       $totalCharge = $order->total_charge ?? $order->net_amount ?? 0;
                                       $lessDiscount = $grossCharge - $totalCharge;
                                       @endphp
                                       ₱{{ number_format($lessDiscount, 2) }}
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>Vatable</td>
                                    <td class="text-right vatable">
                                       ₱{{ number_format($order->vatable ?? 0, 2) }}
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>Vat 12%</td>
                                    <td class="text-right vat-12">
                                       ₱{{ number_format($order->vat_12 ?? 0, 2) }}
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>Reg Bill</td>
                                    <td class="text-right reg-bill">
                                       ₱{{ number_format(($order->vatable ?? 0) * 1.12, 2) }}
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>SR/PWD Bill</td>
                                    <td class="text-right sr-pwd-bill">
                                       ₱{{ number_format($order->sr_pwd_bill ?? 0, 2) }}
                                    </td>
                                 </tr>
                                 <tr>
                                    <td><strong>Total</strong></td>
                                    <td class="text-right total-due">
                                       <strong>₱{{ number_format($order->total_charge ?? $order->gross_amount ?? 0, 2) }}</strong>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                           <div class="text-center mt-4" style="border:1px dashed #666; padding:10px; font-size:14px; font-weight:bold;">
                              PAYMENT PENDING<br>
                              Please proceed to the cashier
                           </div>
                           <div class="text-center small mt-4">
                              Thank you for dining with us!<br>
                              This document is not valid as an official receipt until payment is made.
                           </div>
                           <p class="d-flex justify-content-between fw-bold mt-3 mb-1">
                              <span>POS Provided by:</span>
                              <span>OMNI Systems Solutions</span>
                           </p>
                           <div class="d-flex flex-column small">
                              <span class="t-font-boldest">TIN: {{ $branch->tin ?? '123-456-789' }}</span>
                              <span>OMNI Address: A. C. Cortes Ave, Mandaue, 6014 Cebu</span>
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer justify-content-center">
                        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">Print Bill</button>
                        <button class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                     </div>
                  </div>
               </div>
            </div>
            @endforeach
            <script>
               function calculateChargesAndDiscounts(orderId, grossAmount, pax) {
                   const vatRate = 0.12;
               
                   // Safety/coerce
                   grossAmount = parseFloat(grossAmount) || 0;
                   pax = parseInt(pax) || 1;
                   if (pax <= 0) pax = 1;
               
                   // Selected discounts
                   const hidden = document.getElementById("discountIds_" + orderId);
                   const selectedIds = hidden && hidden.value
                       ? hidden.value.split(',').map(s => s.trim()).filter(Boolean)
                       : [];
               
                   let qualifiedCount = 0;
                   let discountPercent = 0;
                   let otherDiscountTotal = 0;
               
                   selectedIds.forEach(id => {
                       const chk = document.getElementById(`discountCheck_${orderId}_${id}`);
                       if (!chk) return;
               
                       const name = (chk.dataset.name || '').toLowerCase();
                       const valuePct = parseFloat(chk.dataset.value || 0);
               
                       // SR/PWD Discount logic
                       if (name.includes('senior') || name.includes('pwd')) {
                           const countInput = document.getElementById(`discountCount_${orderId}_${id}`);
                           const count = countInput ? parseInt(countInput.value) || 0 : 0;
               
                           if (count > 0) {
                               qualifiedCount += count;
                               discountPercent = valuePct;
                           }
                       } 
                       // Other discounts (VIP, Athlete, etc.)
                       else {
                           const countInput = document.getElementById(`discountCount_${orderId}_${id}`);
                           const count = countInput ? parseInt(countInput.value) || 1 : 1;
                           const discountPerPerson = grossAmount * (valuePct / 100);
                           const totalDiscount = discountPerPerson * count;
                           otherDiscountTotal += totalDiscount;
                       }
                   });
               
                   // Prevent SR/PWD count exceeding total pax
                   qualifiedCount = Math.min(qualifiedCount, pax);
               
                   // Compute SR/PWD and Regular shares
                   const perPax = grossAmount / pax;
                   const srPwdBill = perPax * qualifiedCount;
                   const regBill = perPax * (pax - qualifiedCount);
               
                   // ✅ VATABLE and VAT 12%
                   const vatable = regBill / (1 + vatRate);
                   const vat12 = regBill - vatable;
               
                   // ✅ SR/PWD 20% discount (non-VAT portion)
                   const srPwdVatable = srPwdBill / (1 + vatRate);
                   const discount20 = srPwdVatable * (discountPercent / 100);
               
                   // ✅ NET BILL for SR/PWD
                   const netBill = srPwdVatable - discount20;
               
                   // ✅ TOTAL CHARGE
                   const totalCharge = ((grossAmount - otherDiscountTotal) - ((srPwdBill / (1 + vatRate)) * vatRate) - ((srPwdBill / (1 + vatRate)) * (discountPercent / 100)));
               
                   // Calculate vat_exempt_12
                   const vatExempt12 = srPwdBill - srPwdVatable; // srPwdBill
                   const vatExempt12Rounded = Number(vatExempt12.toFixed(2));
               
                   // Store in hidden input
                   let vatExemptInput = document.getElementById('vat_exempt_12_' + orderId);
                   if (!vatExemptInput) {
                       vatExemptInput = document.createElement('input');
                       vatExemptInput.type = 'hidden';
                       vatExemptInput.id = 'vat_exempt_12_' + orderId;
                       vatExemptInput.name = 'vat_exempt_12';
                       const form = document.getElementById('billOutForm_' + orderId);
                       if (form) {
                           form.appendChild(vatExemptInput);
                       }
                   }
                   if (vatExemptInput) {
                       vatExemptInput.value = vatExempt12Rounded;
                   }
               
                   // --- Update UI fields ---
                   const setVal = (id, val) => {
                       const el = document.getElementById(id + '_' + orderId);
                       if (el) el.value = Number(val || 0).toFixed(2);
                   };
               
                   setVal('srPwdBill', srPwdBill);
                   setVal('regBill', regBill);
                   setVal('vatable', vatable);
                   setVal('vat12', vat12);
                   setVal('discount20', discount20);
                   setVal('netBill', netBill);
                   setVal('otherDiscount', otherDiscountTotal);
                   setVal('totalCharge', totalCharge);
               
                   // ✅ NEW: Mark that calculation has been performed
                   hasCalculatedCharges[orderId] = true;
                   console.log(`[order ${orderId}] ✅ Charges calculated successfully`);
               
                   // ✅ NEW: Enable the Submit button now
                   enableSubmitButton(orderId);
               }
               
               function enableSubmitButton(orderId) {
                   const submitBtn = document.getElementById('submitBtn_' + orderId);
                   if (submitBtn) {
                       submitBtn.disabled = false;
                       submitBtn.style.opacity = '1';
                       submitBtn.style.cursor = 'pointer';
                       submitBtn.title = '';
                       console.log(`[order ${orderId}] ✅ Submit button ENABLED`);
                   }
               }
               
               function areChargesFieldsPopulated(orderId) {
                   const totalCharge = document.getElementById('totalCharge_' + orderId);
                   const srPwdBill = document.getElementById('srPwdBill_' + orderId);
                   const regBill = document.getElementById('regBill_' + orderId);
                   
                   // Check if total charge has a value greater than 0
                   const hasValue = totalCharge && parseFloat(totalCharge.value || 0) > 0;
                   
                   console.log(`[order ${orderId}] Charges populated: ${hasValue}`);
                   return hasValue;
               }
               
               function disableSubmitButton(orderId, message = 'Please calculate charges first') {
                   const submitBtn = document.getElementById('submitBtn_' + orderId);
                   if (submitBtn) {
                       submitBtn.disabled = true;
                       submitBtn.style.opacity = '0.6';
                       submitBtn.style.cursor = 'not-allowed';
                       submitBtn.title = message;
                       console.log(`[order ${orderId}] 🔒 Submit button DISABLED: ${message}`);
                   }
               }
               
               const savedDiscountPersons = {}; 
               
               function saveDiscountPersons(orderId) {
                   const personsMap = {};
               
                   document.querySelectorAll(`#selectedDiscountsContainer_${orderId} [id^="discountPersons_"]`).forEach(container => {
                       const discountId = container.id.split('_').pop();
                       const rows = container.querySelectorAll('.row');
               
                       personsMap[discountId] = [];
                       rows.forEach((row, idx) => {
                           const nameInput = row.querySelector('input[name^="persons"][name$="[name]"]');
                           const idInput = row.querySelector('input[name^="persons"][name$="[id_number]"]');
               
                           personsMap[discountId].push({
                               name: nameInput ? nameInput.value : '',
                               id_number: idInput ? idInput.value : ''
                           });
                       });
                   });
               
                   savedDiscountPersons[orderId] = personsMap;
               }
               
               // Check if all name + ID fields are filled
               function areAllDiscountFieldsFilled(orderId) {
                   const container = document.getElementById('selectedDiscountsContainer_' + orderId);
                   if (!container) {
                       console.log(`[order ${orderId}] Container not found`);
                       return false; // Changed from true to false
                   }
               
                   // Check if container has any discount forms
                   const discountForms = container.querySelectorAll('[id^="discountPersons_"]');
                   if (discountForms.length === 0) {
                       console.log(`[order ${orderId}] No discount forms found`);
                       
                       // Check if discounts are selected
                       const hidden = document.getElementById('discountIds_' + orderId);
                       const hasSelectedDiscounts = hidden && hidden.value && hidden.value.trim() !== '';
                       
                       // If discounts are selected but no forms rendered, fields are NOT filled
                       if (hasSelectedDiscounts) {
                           console.log(`[order ${orderId}] Discounts selected but no forms - fields NOT filled`);
                           return false;
                       }
                       
                       // No discounts selected at all
                       return true;
                   }
               
                   // Check all name and ID inputs
                   const nameInputs = container.querySelectorAll('input[placeholder="Enter name here"]');
                   const idInputs = container.querySelectorAll('input[placeholder="Enter ID number here"]');
               
                   console.log(`[order ${orderId}] Found ${nameInputs.length} name inputs and ${idInputs.length} ID inputs`);
               
                   // If no inputs found but forms exist, something is wrong
                   if (nameInputs.length === 0 && idInputs.length === 0) {
                       console.log(`[order ${orderId}] No input fields found in forms`);
                       return false;
                   }
               
                   // Check if all fields are filled
                   for (let i = 0; i < nameInputs.length; i++) {
                       const nameValue = nameInputs[i].value.trim();
                       const idValue = idInputs[i] ? idInputs[i].value.trim() : '';
                       
                       if (!nameValue || !idValue) {
                           console.log(`[order ${orderId}] Empty field detected at index ${i} → fields NOT filled`);
                           return false;
                       }
                   }
               
                   console.log(`[order ${orderId}] All discount fields filled`);
                   return true;
               }
               
               // Update the button state logic
               function updateCalculateButtonState(orderId) {
               
                  // You can keep this as fallback when no discounts are selected
                   const hasDiscounts = document.getElementById('discountIds_' + orderId)?.value?.trim();
                   if (!hasDiscounts) {
                       const btn = document.getElementById('calculateBtn_' + orderId);
                       if (btn) {
                           btn.disabled = false;
                           btn.style.opacity = '1';
                           btn.style.cursor = 'pointer';
                           btn.title = '';
                       }
                   }
                   
                   const calculateBtn = document.getElementById('calculateBtn_' + orderId);
                   const submitBtn = document.getElementById('submitBtn_' + orderId);
                   
                   if (!calculateBtn) {
                       console.warn(`[order ${orderId}] Calculate button not found!`);
                       return;
                   }
               
                   const form = document.getElementById('discountForm_' + orderId);
                   const hidden = document.getElementById('discountIds_' + orderId);
                   
                   // Check if discount form/manage section is open
                   const isFormOpen = form && form.style.display === 'block';
                   
                   // Check if any discounts are selected
                   const hasSelectedDiscounts = hidden && hidden.value && hidden.value.trim() !== '';
               
                   console.log(`[order ${orderId}] Form open: ${isFormOpen}, Has discounts: ${hasSelectedDiscounts}`);
               
                   if (!hasSelectedDiscounts) {
                       // No discounts selected - enable both Calculate and Submit buttons
                       calculateBtn.disabled = false;
                       calculateBtn.style.opacity = '1';
                       calculateBtn.style.cursor = 'pointer';
                       calculateBtn.title = '';
                       
                       // ✅ ALSO ENABLE SUBMIT BUTTON
                       if (submitBtn) {
                           submitBtn.disabled = false;
                           submitBtn.style.opacity = '1';
                           submitBtn.style.cursor = 'pointer';
                           submitBtn.title = '';
                       }
                       
                       console.log(`[order ${orderId}] No discounts selected → Both buttons ENABLED`);
                       return;
                   }
               
                   // Discounts are selected - check if fields are filled
                   const allFilled = areAllDiscountFieldsFilled(orderId);
                   
                   // Update Calculate button
                   calculateBtn.disabled = !allFilled;
                   calculateBtn.style.opacity = calculateBtn.disabled ? '0.6' : '1';
                   calculateBtn.style.cursor = calculateBtn.disabled ? 'not-allowed' : 'pointer';
                   calculateBtn.title = calculateBtn.disabled ? 'Please fill all name and ID fields in the discount form' : '';
                   
                   // ✅ UPDATE SUBMIT BUTTON - SYNC WITH CALCULATE BUTTON STATE
                   if (submitBtn) {
                       submitBtn.disabled = calculateBtn.disabled;
                       submitBtn.style.opacity = submitBtn.disabled ? '0.6' : '1';
                       submitBtn.style.cursor = submitBtn.disabled ? 'not-allowed' : 'pointer';
                       submitBtn.title = submitBtn.disabled ? 'Please fill all required fields first' : '';
                   }
                   
                   console.log(`[order ${orderId}] Form ${isFormOpen ? 'open' : 'closed'}, Both buttons: ${calculateBtn.disabled ? 'DISABLED' : 'ENABLED'}`);
               }
               
               
               function confirmBillOut(orderId) {
                   // ✅ NEW: Validate that calculation was performed
                   if (!hasCalculatedCharges[orderId]) {
                       alert('⚠️ Please click "Calculate Charges and Discounts" first!');
                       return;
                   }
                   
                   // ✅ NEW: Validate that charges fields are populated
                   if (!areChargesFieldsPopulated(orderId)) {
                       alert('⚠️ Charges and discounts are empty. Please calculate first!');
                       return;
                   }
               
                    const form = document.getElementById('billOutForm_' + orderId);
                   if (!form) {
                       console.error('Form not found:', 'billOutForm_' + orderId);
                       alert('Form not found. Please refresh the page.');
                       return null;
                   }
               
                   const formData = new FormData(form);
               
                   // List of fields to normalize and send
                   const numericFields = [
                       'srPwdBill', 'discount20', 'otherDiscount', 'netBill',
                       'vatable', 'vat12', 'totalCharge', 'grossCharge', 'regBill', 'vat_exempt_12'
                   ];
               
                   numericFields.forEach(f => {
                       const el = document.getElementById(f + '_' + orderId);
                       if (el) {
                           // Remove commas and default to '0'
                           const value = (el.value || '0').replace(/,/g, '');
                           formData.set(f, value);
                       }
                   });
               
                   // Collect discount persons from saved memory
                   const personsData = [];
                   if (savedDiscountPersons[orderId]) {
                       Object.entries(savedDiscountPersons[orderId]).forEach(([discountId, persons]) => {
                           persons.forEach(p => {
                               personsData.push({
                                   discount_id: discountId,
                                   name: p.name || '',
                                   id_number: p.id_number || ''
                               });
                           });
                       });
                   }
               
                   formData.set('persons', JSON.stringify(personsData));
                            
               
                   // Get CSRF token
                   const csrfToken = document.querySelector('meta[name="csrf-token"]');
                   if (!csrfToken) {
                       console.error('CSRF token not found');
                       alert('Security token missing. Please refresh the page.');
                       return;
                   }
               
                   // Submit request
                   fetch(form.action, {
                       method: 'POST',
                       headers: {
                           'X-CSRF-TOKEN': csrfToken.content,
                           'Accept': 'application/json'
                       },
                       body: formData
                   })
                   .then(res => {
                       console.log('Response status:', res.status);
                       
                       // Check if response is OK
                       if (!res.ok) {
                           throw new Error(`HTTP error! status: ${res.status}`);
                       }
                       
                       // Check content type
                       const contentType = res.headers.get("content-type");
                       if (!contentType || !contentType.includes("application/json")) {
                           throw new TypeError("Response is not JSON");
                       }
                       
                       return res.json();
                   })
                   .then(data => {
                       console.log('Bill-out response:', data);
                       
                       if (data.success !== true) {
                           alert('Failed to save bill: ' + (data.message || 'Unknown error'));
                           return;
                       }
               
                       const totalEl = document.getElementById('totalCharge_' + orderId);
                       if (totalEl && data.order?.total_charge != null) {
                           totalEl.value = Number(data.order.total_charge).toFixed(2);
                       }
               
                       const billOutModalEl = document.getElementById('billOutModal' + orderId);
                       if (billOutModalEl) {
                           const billOutModal = bootstrap.Modal.getInstance(billOutModalEl);
                           if (billOutModal) {
                               billOutModalEl.addEventListener('hidden.bs.modal', function onHidden() {
                                   billOutModalEl.removeEventListener('hidden.bs.modal', onHidden);
               
                                   if (data.order) {
                                       showUpdatedBillOutPreview(orderId, data.order);
                                   } else {
                                       showPreviewModal(orderId);
                                   }
                               }, { once: true });
               
                               billOutModal.hide();
                               return;
                           }
                       }
               
                       if (data.order) {
                           showUpdatedBillOutPreview(orderId, data.order);
                       } else {
                           showPreviewModal(orderId);
                       }
                   })
                   .catch(err => {
                       console.error(err);
                       alert('Error submitting bill-out. Please try again.');
                   });
               }
               
               function showUpdatedBillOutPreview(orderId, orderData) {
                   const modalEl = document.getElementById('billOutPreviewModal' + orderId);
                   if (!modalEl) {
                       console.error(`Preview modal #billOutPreviewModal${orderId} not found`);
                       alert('Preview modal not found. Please refresh the page.');
                       return;
                   }
               
                   // Helper to update text safely
                   const setText = (className, value) => {
                       const el = modalEl.querySelector(`.${className}`);
                       if (el) {
                           const formatted = '₱' + Number(value || 0).toFixed(2);
                           el.textContent = formatted;
                           console.log(`Updated ${className} → ${formatted}`);
                       } else {
                           console.warn(`Element with class .${className} not found in preview modal`);
                       }
                   };
               
                   // Calculate gross fallback
                   const gross = Number(orderData.gross_amount || 0) ||
                                 (orderData.details || []).reduce((sum, d) => {
                                     return sum + (Number(d.price || 0) * Number(d.quantity || 0) - Number(d.discount || 0));
                                 }, 0);
               
                   // Update all fields
                   setText('gross-charge', gross);
               
                   // Compute Less Discount = vat_exempt_12 + 20% discount
                   const vatExempt = Number(orderData.vat_exempt_12 || 0);
                   // Prefer the client-side calculated 20% discount if present in the DOM (form)
                   let discount20Val = 0;
                   try {
                       const discountEl = document.getElementById('discount20_' + orderId);
                       if (discountEl) {
                           discount20Val = Number(discountEl.value || 0);
                       } else if (orderData.discount20 != null) {
                           discount20Val = Number(orderData.discount20 || 0);
                       } else {
                           // Fallback: assume standard 20% of vat_exempt (matches JS logic)
                           discount20Val = Number((vatExempt * 0.20) || 0);
                       }
                   } catch (e) {
                       discount20Val = Number((vatExempt * 0.20) || 0);
                   }
               
                   const lessDiscountVal = Number(gross - orderData.total_charge);
                   setText('less-discount', lessDiscountVal);
               
                   setText('vatable', orderData.vatable || 0);
                   setText('vat-12', orderData.vat_12 || 0);
               
                   // Compute Reg Bill from vatable so preview matches calculation
                   const vatRate = 0.12;
                   const vatableVal = Number(orderData.vatable || 0);
                   const computedRegBill = Number((vatableVal * (1 + vatRate)).toFixed(2));
                   setText('reg-bill', computedRegBill);
                   setText('sr-pwd-bill', orderData.sr_pwd_bill || 0);
                   setText('total-due strong', orderData.total_charge || gross);
               
                   // Force show the modal
                   try {
                       const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                       modalInstance.show();
                       console.log(`Preview modal ${orderId} shown successfully`);
                   } catch (e) {
                       console.error('Failed to show modal:', e);
                       alert('Could not open preview. Please check console for errors.');
                   }
               
                   // Reload page when preview is closed (to refresh order list)
                   modalEl.addEventListener('hidden.bs.modal', () => {
                       window.location.reload();
                   }, { once: true });
               }
               
               // Helper: show preview after old modal is gone
               function showPreviewModal(orderId) {
                   const previewEl = document.getElementById('billOutPreviewModal' + orderId);
                   if (previewEl) {
                       console.log('Showing Bill Out Slip preview for order ' + orderId);
                       const previewModal = bootstrap.Modal.getOrCreateInstance(previewEl);
                       previewModal.show();
               
                       // Reload page when user closes the preview (clean state)
                       previewEl.addEventListener('hidden.bs.modal', function onClose() {
                           previewEl.removeEventListener('hidden.bs.modal', onClose);
                           window.location.reload();
                       }, { once: true });
                   } else {
                       console.error('Preview modal not found: billOutPreviewModal' + orderId);
                       alert('Bill saved, but preview modal could not be displayed.');
                   }
               }
               
               const hasCalculatedCharges = {}
               
               // Add this event listener when the bill-out modal opens
               document.querySelectorAll('[id^="billOutModal"]').forEach(modal => {
                   modal.addEventListener('show.bs.modal', function () {
                       const orderId = this.id.replace('billOutModal', '');
                       
                       // ✅ Reset calculation state when modal opens
                       hasCalculatedCharges[orderId] = false;
                       
                       // ✅ Disable submit button initially
                       disableSubmitButton(orderId, 'Please calculate charges and discounts first');
                       
                       console.log(`[order ${orderId}] 📋 Bill-out modal opened - Submit button disabled`);
                   });
               });
               
               // Global tracker (per order) — whether the discount form was closed with valid data
               const discountFormClosedValid = {};
               
               // ──────────────────────────────────────────────────────────────
               // Helper: Force disable Calculate & Submit buttons
               // ──────────────────────────────────────────────────────────────
               function disableCalculateAndSubmit(orderId, tooltip = '') {
                   const calcBtn = document.getElementById('calculateBtn_' + orderId);
                   const subBtn  = document.getElementById('submitBtn_' + orderId);
               
                   if (calcBtn) {
                       calcBtn.disabled = true;
                       calcBtn.style.opacity = '0.6';
                       calcBtn.style.cursor = 'not-allowed';
                       if (tooltip) calcBtn.title = tooltip;
                   }
                   if (subBtn) {
                       subBtn.disabled = true;
                       subBtn.style.opacity = '0.6';
                       subBtn.style.cursor = 'not-allowed';
                   }
               }
               
               // ──────────────────────────────────────────────────────────────
               // Helper: Enable Calculate & Submit buttons
               // ──────────────────────────────────────────────────────────────
               function enableCalculateAndSubmit(orderId) {
                   const calcBtn = document.getElementById('calculateBtn_' + orderId);
                   const subBtn  = document.getElementById('submitBtn_' + orderId);
               
                   if (calcBtn) {
                       calcBtn.disabled = false;
                       calcBtn.style.opacity = '1';
                       calcBtn.style.cursor = 'pointer';
                       calcBtn.title = '';
                   }
                   if (subBtn) {
                       subBtn.disabled = false;
                       subBtn.style.opacity = '1';
                       subBtn.style.cursor = 'pointer';
                       subBtn.title = '';
                   }
               }
               
               // ──────────────────────────────────────────────────────────────
               // Updated toggleDiscountForm
               // ──────────────────────────────────────────────────────────────
               function toggleDiscountForm(orderId) {
                   const hidden = document.getElementById('discountIds_' + orderId);
                   const selectedIds = hidden && hidden.value
                       ? hidden.value.split(',').map(s => s.trim()).filter(Boolean)
                       : [];
               
                   
                   if (!selectedIds.length) {
                       alert('Please select at least one discount first.');
                       return;
                   }
               
                   const container = document.getElementById('selectedDiscountsContainer_' + orderId);
                   const form = document.getElementById('discountForm_' + orderId);
                   const willOpen = form.style.display !== 'block';
               
                   if (willOpen) {
                       // ─── OPENING the Manage Discount form ────────────────────────
                       container.innerHTML = '';
               
                       selectedIds.forEach(id => {
                           const chk = document.getElementById(`discountCheck_${orderId}_${id}`);
                           if (!chk) return;
               
                           const discountName = chk.dataset.name || 'Discount';
                           const valuePct = chk.dataset.value || '0';
               
                           // Initialize storage if needed
                           if (!savedDiscountPersons[orderId]) savedDiscountPersons[orderId] = {};
                           if (!savedDiscountPersons[orderId][id]) {
                               savedDiscountPersons[orderId][id] = [{ name: '', id_number: '' }];
                           }
               
                           const persons = savedDiscountPersons[orderId][id];
               
                           const block = document.createElement('div');
                           block.className = 'mb-4 p-2 border rounded';
                           block.innerHTML = `
                               <h6 class="fw-bold">${discountName} (${valuePct}%)</h6>
                               <div class="form-group mb-2 d-flex align-items-center">
                                   <label class="me-2"># of Entries</label>
                                   <input type="number"
                                          id="discountCount_${orderId}_${id}"
                                          class="form-control"
                                          style="width:100px" min="1"
                                          value="${persons.length}"
                                          oninput="renderDiscountPersons(${orderId}, ${id})">
                               </div>
                               <div id="discountPersons_${orderId}_${id}"></div>
                           `;
                           container.appendChild(block);
               
                           renderDiscountPersons(orderId, id);
                       });
               
                       form.style.display = 'block';
                       console.log(`[order ${orderId}] Discount form OPENED`);
               
                       // IMPORTANT: While form is open → always disable buttons
                       discountFormClosedValid[orderId] = false; // reset flag
                       disableCalculateAndSubmit(orderId, 'Please close Manage Discount form first');
                   } 
                   else {
                   // ─── CLOSING the Manage Discount form ────────────────────────
                   form.style.display = 'none';
                   console.log(`[order ${orderId}] Discount form CLOSED`);
               
                   const allFieldsFilled = areAllDiscountFieldsFilled(orderId);
               
                   if (allFieldsFilled) {
                       discountFormClosedValid[orderId] = true;
                       enableCalculateAndSubmit(orderId);
                       
                       // ✅ NEW: But Submit should still be disabled until Calculate is clicked
                       if (!hasCalculatedCharges[orderId]) {
                           disableSubmitButton(orderId, 'Please click Calculate Charges and Discounts');
                       }
                   } else {
                       discountFormClosedValid[orderId] = false;
                       disableCalculateAndSubmit(orderId, 'Please complete all name and ID fields');
                   }
               }
               }
               
               function renderDiscountPersons(orderId, discountId) {
                   const countInput = document.getElementById(`discountCount_${orderId}_${discountId}`);
                   const count = Math.max(parseInt(countInput?.value) || 1, 1);
               
                   if (!savedDiscountPersons[orderId]) savedDiscountPersons[orderId] = {};
                   if (!savedDiscountPersons[orderId][discountId]) savedDiscountPersons[orderId][discountId] = [];
               
                   const persons = savedDiscountPersons[orderId][discountId];
               
                   // Adjust array length
                   while (persons.length < count) persons.push({ name: '', id_number: '' });
                   while (persons.length > count) persons.pop();
               
                   const container = document.getElementById(`discountPersons_${orderId}_${discountId}`);
                   if (!container) return;
               
                   container.innerHTML = '';
               
                   persons.forEach((p, idx) => {
                       const row = document.createElement('div');
                       row.className = 'row mb-2';
                       row.innerHTML = `
                           <div class="col-md-6">
                               <input type="text"
                                      class="form-control"
                                      placeholder="Enter name here"
                                      value="${p.name || ''}"
                                      oninput="updatePersonData(${orderId}, ${discountId}, ${idx}, 'name', this.value)">
                           </div>
                           <div class="col-md-6">
                               <input type="text"
                                      class="form-control"
                                      placeholder="Enter ID number here"
                                      value="${p.id_number || ''}"
                                      oninput="updatePersonData(${orderId}, ${discountId}, ${idx}, 'id_number', this.value)">
                           </div>
                       `;
                       container.appendChild(row);
                   });
               
                   // DO NOT call updateCalculateButtonState() here
               }
               
               /**
                * Update person data live when typing
                */
               function updatePersonData(orderId, discountId, index, field, value) {
                   if (!savedDiscountPersons[orderId]) return;
                   if (!savedDiscountPersons[orderId][discountId]) return;
                   
                   savedDiscountPersons[orderId][discountId][index][field] = value;
               
                   console.log(`[order ${orderId}] Updated person data: discount ${discountId}, index ${index}, ${field} = ${value}`);
                   
                   // Live update button state whenever user types
                  //  updateCalculateButtonState(orderId);
               }
               
            </script>
            <script>
               // helper to add a payment row in the modal table
               function addPaymentRow(orderId) {
                const counterEl = document.getElementById('payments_counter_' + orderId);
                let counter = counterEl ? parseInt(counterEl.value || 0) : 0;
                counter++;
                if (counterEl) counterEl.value = counter;
               
                const tbody = document.getElementById('payments_table_body_' + orderId);
                if (!tbody) return;
               
                // Build select options
                const paymentMethods = {!! json_encode($paymentMethods->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->name
                ])) !!};
               
                const cashEquivalents = {!! json_encode($cashEquivalents->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'account_number' => $c->account_number,
                    'creator_name' => $c->creator?->name ?? null,
                    'created_by' => $c->created_by
                ])) !!};
               
                window.cashEquivalentsList = window.cashEquivalentsList || cashEquivalents;
                window.currentUserId = window.currentUserId || {!! json_encode(auth()->id()) !!};
               
                try {
                    window.paymentMethodsMap = window.paymentMethodsMap || {};
                    paymentMethods.forEach(pm => { window.paymentMethodsMap[pm.id] = pm.name; });
               
                    window.cashEquivalentsMap = window.cashEquivalentsMap || {};
                    cashEquivalents.forEach(ce => { window.cashEquivalentsMap[ce.id] = ce.name; });
                } catch (e) { /* ignore */ }
               
                const tr = document.createElement('tr');
                tr.dataset.rowId = counter;
                tr.innerHTML = `
                    <td>
                        <select id="pm_${orderId}_${counter}" class="form-select form-select-sm">
                            <option value=""></option>
                            ${paymentMethods.map(m => `<option value="${m.id}">${m.name}</option>`).join('')}
                        </select>
                    </td>
                    <td>
                        <select id="pdest_${orderId}_${counter}" class="form-select form-select-sm" onchange="toggleReferenceInput(${orderId}, ${counter})">
                            <option value=""></option>
                            ${cashEquivalents.map(c => 
                                `<option value="${c.id}">${c.name} | ${c.account_number ?? ''}</option>`
                            ).join('')}
                        </select>
                    </td>
                    <td id="pref_td_${orderId}_${counter}" style="display:none;">
                        <input type="text" id="pref_${orderId}_${counter}" class="form-control form-control-sm" placeholder="Ref. No." />
                    </td>
                    <td class="text-end">
                        <input type="number" step="0.01" id="pamt_${orderId}_${counter}" class="form-control form-control-sm text-end" value="0.00" oninput="recalcPayments(${orderId})" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removePaymentRow(${orderId}, ${counter})">Remove</button>
                    </td>
                `;
               
                tbody.appendChild(tr);
               
                // ✅ Attach event listener for payment method change
                const pmSelect = document.getElementById(`pm_${orderId}_${counter}`);
                const pdestSelect = document.getElementById(`pdest_${orderId}_${counter}`);
                
                if (pmSelect && pdestSelect) {
                    pmSelect.addEventListener('change', function () {
                        try { 
                            updateDestOptions(orderId, counter, this.value); 
                        } catch (e) { 
                            console.error('Error updating destination options:', e); 
                        }
                    });
                    
                    // Initialize destination options
                    try { 
                        updateDestOptions(orderId, counter, pmSelect.value); 
                    } catch (e) { 
                        console.error('Error initializing destination options:', e); 
                    }
                }
               
                recalcPayments(orderId);
               }
               
                // Update payment destination options to show creator name when payment method is Cash
                // Update payment destination options and auto-select Cash On Hand for Cash method
               function updateDestOptions(orderId, rowId, paymentMethodId) {
                // Find payment method name from global map
                let pmName = (window.paymentMethodsMap && window.paymentMethodsMap[paymentMethodId]) || '';
                const pmLower = String(pmName).trim().toLowerCase();
                const pdest = document.getElementById(`pdest_${orderId}_${rowId}`);
                
                if (!pdest) return;
               
                // Preserve currently selected value
                const current = pdest.value;
               
                const list = window.cashEquivalentsList || [];
                const filteredList = list.filter(c => {
                    const nameLower = String(c.name || '').toLowerCase();
                    const isCashOnHand = nameLower.includes('cash on hand');
               
                    if (pmLower === 'cash') {
                        // For Cash payment method: show ONLY Cash On Hand entries created by current user
                        return isCashOnHand && Number(c.created_by) === Number(window.currentUserId);
                    }
               
                    // For non-cash payment methods: exclude Cash On Hand entries, include others
                    return !isCashOnHand;
                });
               
                // Build options HTML
                pdest.innerHTML = '<option value=""></option>' + filteredList.map(c => {
                    const nameLower = String(c.name || '').toLowerCase();
                    const isCashOnHand = nameLower.includes('cash on hand');
                    const suffix = isCashOnHand ? (c.creator_name || c.account_number || '') : (c.account_number || c.creator_name || '');
                    return `<option value="${c.id}">${c.name} | ${suffix}</option>`;
                }).join('');
               
                // ✅ AUTO-SELECT and VALIDATE for Cash payment method
                if (pmLower === 'cash') {
                    if (filteredList.length === 0) {
                        // No Cash On Hand found - show error
                        pdest.innerHTML = '<option value="">⚠️ No Cash On Hand available</option>';
                        pdest.style.borderColor = '#dc3545'; // Red border
                        
                        // Show alert
                        alert('❌ Error: Please create a Cash On Hand account first.\n\nGo to Settings → Cash Equivalents to create your Cash On Hand account.');
                        
                        // Clear payment method selection
                        const pmSelect = document.getElementById(`pm_${orderId}_${rowId}`);
                        if (pmSelect) pmSelect.value = '';
                        
                        return;
                    } else if (filteredList.length === 1) {
                        // Auto-select the only Cash On Hand option
                        pdest.value = filteredList[0].id;
                        pdest.style.borderColor = '#198754'; // Green border
                        
                        // Show reference input since destination is now selected
                        toggleReferenceInput(orderId, rowId);
                        
                        console.log(`✅ Auto-selected Cash On Hand: ${filteredList[0].name}`);
                    } else {
                        // Multiple Cash On Hand options - reset border
                        pdest.style.borderColor = '';
                    }
                } else {
                    // Non-cash payment - reset border
                    pdest.style.borderColor = '';
                    
                    // Restore selection if still present
                    if (current) pdest.value = current;
                }
               }
               
                  // ✅ new function: show/hide the reference input based on selection
                  function toggleReferenceInput(orderId, rowId) {
                     const select = document.getElementById(`pdest_${orderId}_${rowId}`);
                     const refTd = document.getElementById(`pref_td_${orderId}_${rowId}`);
                     if (select && refTd) {
                        if (select.value) {
                              refTd.style.display = ''; // show
                        } else {
                              refTd.style.display = 'none'; // hide
                              const input = document.getElementById(`pref_${orderId}_${rowId}`);
                              if (input) input.value = ''; // clear when hidden
                        }
                     }
                  }
               
                  function removePaymentRow(orderId, rowId) {
                     const tbody = document.getElementById('payments_table_body_' + orderId);
                     if (!tbody) return;
                     const tr = tbody.querySelector(`tr[data-row-id="${rowId}"]`);
                     if (tr) tr.remove();
                     recalcPayments(orderId);
                  }
               
                  function recalcPayments(orderId) {
                     const tbody = document.getElementById('payments_table_body_' + orderId);
                     let total = 0;
                     if (tbody) {
                        tbody.querySelectorAll('input[id^="pamt_"]').forEach(inp => {
                           const val = parseFloat(inp.value || 0) || 0;
                           total += val;
                        });
                     }
               
                     // display totals; total charge
                     const totalEl = document.getElementById('payments_total_' + orderId);
                     const changeEl = document.getElementById('payments_change_' + orderId);
                     const totalChargeStr = document.getElementById('pay_totalCharge_' + orderId)?.value || document.getElementById('totalCharge_' + orderId)?.value || 0;
                     const totalCharge = parseFloat(String(totalChargeStr).replace(/,/g, '')) || 0;
                     if (totalEl) totalEl.textContent = Number(total).toFixed(2);
                     if (changeEl) changeEl.textContent = Number(Math.max(0, total - totalCharge)).toFixed(2);
                  }
            </script>
            <!-- Invoice / Receipt Modal Template -->
            @foreach($orders as $order)
            <div class="modal fade" id="invoiceModal{{ $order->id }}" tabindex="-1" aria-labelledby="invoiceLabel{{ $order->id }}" aria-hidden="true">
               <div class="modal-dialog modal-sm modal-dialog-scrollable">
                  <div class="modal-content">
                     <div class="modal-body">
                        <div id="pos-invoice-{{ $order->id }}">
                           <div style="max-width: 400px; margin: 0px auto; font-family: Arial, Helvetica, sans-serif;">
                              {{-- Header Info --}}
                              <div class="info text-center">
                                 <div class="invoice_logo mb-2">
                                    <img src="/images/logo-default.png" alt="" width="60" height="60">
                                 </div>
                                 <div class="d-flex flex-column small">
                                    <span class="t-font-boldest">{{ $branch->name ?? 'Branch Name' }}</span>
                                    <span>{{ $branch->address ?? '' }}</span>
                                    <span>Permit #: {{ $branch->permit_number ?? '' }}</span>
                                    <span>DTI Issued: {{ $branch->dti_issued ?? '' }}</span>
                                    <span>POS SN: {{ $branch->pos_sn ?? '' }}</span>
                                    <span>MIN#: {{ $branch->min_number ?? '' }}</span>
                                 </div>
                                 <h6 class="t-font-boldest mt-3">
                                    @if($order->status === 'payments')
                                    OFFICIAL RECEIPT
                                    @else
                                    BILL-OUT INVOICE
                                    @endif
                                 </h6>
                                 <div class="mb-2">OR #: {{ sprintf('%08d', $order->id) }}</div>
                                 <div class="mb-1">Date: {{ $order->created_at->format('Y-m-d H:i') }}</div>
                                 <div class="mb-1">TBL: {{ $order->table_no }}</div>
                                 <div class="mb-1"># of Pax: {{ $order->number_pax }}</div>
                              </div>
                              {{-- Items Table --}}
                              <table class="table table-invoice-items mt-2" style="width:100%; font-size:13px;">
                                 <thead>
                                    <tr>
                                       <th style="text-align:left; width:10%">QTY</th>
                                       <th style="text-align:left; width:60%">DESCRIPTION</th>
                                       <th style="text-align:right; width:30%">AMOUNT</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    @foreach($order->details as $d)
                                    <tr>
                                       <td>{{ $d->quantity }}x</td>
                                       <td>
                                          <div class="d-flex flex-column">
                                             <span>{{ $d->item_name }}</span>
                                             <span style="font-size:11px; color:#666">@₱{{ number_format($d->price,2) }}</span>
                                          </div>
                                       </td>
                                       <td style="text-align:right;">₱{{ number_format($d->price * $d->quantity,2) }}</td>
                                    </tr>
                                    @endforeach
                                 </tbody>
                              </table>
                              {{-- Summary Table --}}
                              <table class="table table-invoice-data" style="width:100%; font-size:13px;">
                                 <tbody>
                                    <tr>
                                       <td>Gross Charge</td>
                                       <td class="text-right">₱{{ number_format($order->details->sum(fn($d) => ($d->price * $d->quantity) - ($d->discount ?? 0)), 2) }}</td>
                                    </tr>
                                    <tr>
                                       <td>Less Discount</td>
                                       <td class="text-right">
                                          @php
                                          $grossCharge = $order->gross_amount;
                                          $totalCharge = $order->total_charge ?? $order->net_amount ?? 0;
                                          $lessDiscount = $grossCharge - $totalCharge;
                                          @endphp
                                          ₱{{ number_format($lessDiscount, 2) }}
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>Vatable</td>
                                       <td class="text-right">₱{{ number_format($order->vatable ?? 0,2) }}</td>
                                    </tr>
                                    <tr>
                                       <td>Vat 12%</td>
                                       <td class="text-right">₱{{ number_format($order->vat_12 ?? 0,2) }}</td>
                                    </tr>
                                    <tr>
                                       <td>Reg Bill</td>
                                       <td class="text-right">₱{{ number_format(($order->vatable ?? 0) * 1.12, 2) }}</td>
                                    </tr>
                                    </tr>
                                    <tr>
                                       <td>SR/PWD Bill</td>
                                       <td class="text-right">₱{{ number_format($order->sr_pwd_bill ?? 0,2) }}</td>
                                    </tr>
                                    <tr>
                                       <td><strong>Total</strong></td>
                                       <td class="text-right"><strong>₱{{ number_format($order->total_charge ?? $order->net_amount ?? 0,2) }}</strong></td>
                                    </tr>
                                 </tbody>
                              </table>
                              <div class="d-flex justify-content-between fw-bold mt-2">
                                 <span>Total Charge</span> 
                                 <span>₱{{ number_format($order->total_charge ?? $order->net_amount ?? 0,2) }}</span>
                              </div>
                              {{-- PAYMENT BREAKDOWN - Show for both billout and payments status --}}
                              @php
                              $hasPayments = $order->paymentDetails && $order->paymentDetails->count() > 0;
                              @endphp
                              @if($hasPayments)
                              <hr style="border-top: 2px dashed #333; margin: 15px 0;">
                              <div class="fw-bold mb-2" style="text-align: center;">PAYMENT BREAKDOWN</div>
                              {{-- Group payments by payment method name --}}
                              @php
                              $paymentsByMethod = $order->paymentDetails->groupBy(function($pd) {
                              return $pd->payment?->name ?? 'Unknown';
                              })->map(function($group) {
                              return $group->sum('amount_paid');
                              });
                              @endphp
                              @if($paymentsByMethod->isNotEmpty())
                              @foreach($paymentsByMethod as $method => $amount)
                              <div class="d-flex justify-content-between" style="padding: 2px 0;">
                                 <span>{{ $method }}</span>
                                 <span>₱{{ number_format($amount, 2) }}</span>
                              </div>
                              @endforeach
                              <hr style="margin: 8px 0; border-top: 1px dashed #999;">
                              <div class="d-flex justify-content-between fw-bold" style="padding: 4px 0;">
                                 <span>Total Rendered</span>
                                 <span>₱{{ number_format($order->total_payment_rendered ?? 0, 2) }}</span>
                              </div>
                              <div class="d-flex justify-content-between fw-bold" style="padding: 4px 0;">
                                 <span>Change</span>
                                 <span>₱{{ number_format($order->change_amount ?? 0, 2) }}</span>
                              </div>
                              @else
                              <div class="d-flex justify-content-between text-muted">
                                 <span>No payments recorded</span>
                                 <span>₱0.00</span>
                              </div>
                              @endif
                              @endif
                              {{-- Footer --}}
                              <p class="d-flex justify-content-between fw-bold mt-3 mb-1">
                                 <span>POS Provided by:</span> 
                                 <span>OMNI Systems Solutions</span>
                              </p>
                              <div class="d-flex flex-column small">
                                 <span class="t-font-boldest">TIN: {{ $branch->tin ?? '' }}</span>
                                 <span>OMNI Address: A. C. Cortes Ave, Mandaue, 6014 Cebu</span>
                              </div>
                              {{-- Thank you message --}}
                              @if(!$hasPayments)
                              <p class="text-center small mt-3" style="color: #666;">
                                 <em>This is not an official receipt. Payment required to complete transaction.</em>
                              </p>
                              @else
                              <p class="text-center small mt-3" style="color: #666;">
                                 <em>Thank you for your business!</em>
                              </p>
                              @endif
                              {{-- ACKNOWLEDGEMENT SLIP - Display below "Thank you" message --}}
                              @php
                              $srpwdEntries = $order->discountEntries?->filter(function($de) {
                              $name = strtolower($de->name ?? '');
                              return str_contains($name, 'senior') || str_contains($name, 'sr') || str_contains($name, 'pwd');
                              }) ?? collect();
                              $hasSrpwd = $srpwdEntries->isNotEmpty();
                              $totalBill = $order->sr_pwd_bill ?? 0;
                              $lessVatExempt = $order->vat_exempt_12 ?? 0;
                              $srPwdVatable = $totalBill / 1.12; 
                              $discount20 = $srPwdVatable * 0.20;  // ← update this
                              $totalPrivDiscount = $lessVatExempt + $discount20;
                              $netSrBill = $totalBill - $totalPrivDiscount;
                              $otherDiscount = $order->other_discounts ?? 0;
                              @endphp
                              @if($hasSrpwd || (float)($totalPrivDiscount ?? 0) > 0 || (float)($otherDiscount ?? 0) > 0)
                              <hr style="border-top: 2px dashed #333; margin: 20px 0;">
                              <div style="border: 2px solid #333; padding: 15px; margin-top: 15px;">
                                 <div class="text-center fw-bold mb-3" style="font-size: 14px;">ACKNOWLEDGEMENT SLIP</div>
                                 <div class="small mb-2" style="text-align: center;">
                                    <div>OR #: {{ sprintf('%08d', $order->id) }}</div>
                                    <div>Date: {{ $order->created_at->format('Y-m-d H:i') }}</div>
                                    <div class="mt-2">MEAL FOR (1) SENIOR</div>
                                 </div>
                                 <hr style="border-top: 1px solid #333; margin: 10px 0;">
                                 <table style="width: 100%; font-size: 12px; margin-top: 10px;">
                                    <tbody>
                                       <tr>
                                          <td style="padding: 3px 0;">Total Bill</td>
                                          <td style="text-align: right; padding: 3px 0;">₱{{ number_format($totalBill, 2) }}</td>
                                       </tr>
                                       <tr>
                                          <td style="padding: 3px 0;">Less Vat Exemption 12%</td>
                                          <td style="text-align: right; padding: 3px 0;">₱{{ number_format($lessVatExempt, 2) }}</td>
                                       </tr>
                                       <tr>
                                          <td style="padding: 3px 0;">and Senior Discount 20%</td>
                                          <td style="text-align: right; padding: 3px 0;">₱{{ number_format($discount20, 2) }}</td>
                                       </tr>
                                       <tr style="border-top: 1px solid #333;">
                                          <td style="padding: 5px 0; font-weight: bold;">Total Priv. Discount</td>
                                          <td style="text-align: right; padding: 5px 0; font-weight: bold;">₱{{ number_format($totalPrivDiscount, 2) }}</td>
                                       </tr>
                                       <tr>
                                          <td style="padding: 5px 0; font-weight: bold;">Net Sr. Citizen Bill</td>
                                          <td style="text-align: right; padding: 5px 0; font-weight: bold;">₱{{ number_format($netSrBill, 2) }}</td>
                                       </tr>
                                    </tbody>
                                 </table>
                                 <div style="margin-top: 15px;">
                                    <div style="font-size: 11px; margin-bottom: 5px;">SIGN:</div>
                                    <div style="border-bottom: 1px solid #000; height: 30px; margin-bottom: 10px;"></div>
                                 </div>
                                 <div style="margin-top: 10px;">
                                    <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px;">NAME AND ID#</div>
                                    @foreach($srpwdEntries as $ent)
                                    <div style="display: flex; justify-content: space-between; padding: 3px 0; font-size: 12px;">
                                       <span>{{ $ent->person_name ?? ($ent->name ?? '-') }}</span>
                                       <span>{{ $ent->person_id_number ?? ($ent->id_number ?? '-') }}</span>
                                    </div>
                                    @endforeach
                                 </div>
                              </div>
                              @endif
                           </div>
                        </div>
                     </div>
                     <div class="modal-footer d-flex justify-content-center">
                        <button class="btn btn-outline-primary btn-sm me-2" onclick="window.print()">Print</button>
                        <button class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                     </div>
                  </div>
               </div>
            </div>
            @endforeach
            <script>
               window.openInvoiceModalFromResponse = function(orderData) {
                   console.log('🧾 openInvoiceModalFromResponse called', orderData);
               
                   try {
                       if (!orderData || !orderData.id) {
                           console.error('❌ Invalid order data passed to invoice modal');
                           return;
                       }
               
                       const orderId = orderData.id;
                       const modalId = 'invoiceModal' + orderId;
                       let modalEl = document.getElementById(modalId);
               
                       if (modalEl) {
                           modalEl.remove();
                       }
               
                       const branch = window.appBranch || {};
                       modalEl = document.createElement('div');
                       modalEl.className = 'modal fade';
                       modalEl.id = modalId;
                       modalEl.tabIndex = -1;
                       modalEl.setAttribute('aria-hidden', 'true');
               
                       // Build payment breakdown HTML
                       const paymentsArr = orderData.payment_details || orderData.paymentDetails || [];
                       const paymentsMap = {};
                       let computedTotalPaid = 0;
               
                       paymentsArr.forEach(pd => {
                           const methodName = pd.payment?.name || pd.payment_name || 'Unknown';
                           const amt = Number(pd.amount_paid || pd.amount || 0) || 0;
                           computedTotalPaid += amt;
                           paymentsMap[methodName] = (paymentsMap[methodName] || 0) + amt;
                       });
               
                       let paymentsHtml = '';
                       const hasPayments = Object.keys(paymentsMap).length > 0;
               
                       if (hasPayments) {
                           paymentsHtml = `
                               <hr style="border-top:2px dashed #333;margin:15px 0"/>
                               <div style="font-weight:bold;margin-bottom:10px;text-align:center">PAYMENT BREAKDOWN</div>
                           `;
                           
                           Object.keys(paymentsMap).forEach(method => {
                               paymentsHtml += `
                                   <div style="display:flex;justify-content:space-between;padding:2px 0">
                                       <div>${method}</div>
                                       <div>₱${Number(paymentsMap[method]).toFixed(2)}</div>
                                   </div>
                               `;
                           });
               
                           const totalRendered = Number(orderData.total_payment_rendered ?? computedTotalPaid ?? 0);
                           const change = Number(orderData.change_amount ?? 0);
               
                           paymentsHtml += `
                               <hr style="margin:8px 0;border-top:1px dashed #999"/>
                               <div style="display:flex;justify-content:space-between;font-weight:bold;padding:4px 0">
                                   <div>Total Rendered</div>
                                   <div>₱${totalRendered.toFixed(2)}</div>
                               </div>
                               <div style="display:flex;justify-content:space-between;font-weight:bold;padding:4px 0">
                                   <div>Change</div>
                                   <div>₱${change.toFixed(2)}</div>
                               </div>
                           `;
                       }
               
                       const gross = Number(orderData.gross_amount || 0) ||
                                     (orderData.details || []).reduce((sum, d) => {
                                         return sum + (Number(d.price || 0) * Number(d.quantity || 0) - Number(d.discount || 0));
                                     }, 0);
               
                       const isPaymentComplete = hasPayments;
               
                       // Build acknowledgement slip HTML
                       const srpwdArr = orderData.discount_entries || orderData.discountEntries || [];
                       let ackHtml = '';
               
                       // Show acknowledgement slip when there are SR/PWD entries OR any computed privilege/other discounts
                       const hasAckCondition = (Array.isArray(srpwdArr) && srpwdArr.length > 0)
                           || (Number(orderData.other_discounts || 0) > 0)
                           || (Number(orderData.vat_exempt_12 || 0) > 0)
                           || (Number(orderData.discount20 || 0) > 0);
               
                       if (hasAckCondition && hasPayments) {
                           const nf = new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                           const totalBill = Number(orderData.sr_pwd_bill || 0) || 0;   // ← changed: use sr_pwd_discount
                           const lessVatExempt = Number(orderData.vat_exempt_12 || 0) || 0;
                           const srPwdVatable = totalBill / 1.12;
                           const discount20 = srPwdVatable * 0.20; 
                           const totalPrivDiscount = lessVatExempt + discount20;
                           const netSrBill = totalBill - totalPrivDiscount;
               
                           ackHtml = `
                               <hr style="border-top:2px dashed #333;margin:20px 0"/>
                               <div style="border:2px solid #333;padding:15px;margin-top:15px">
                                   <!-- Header -->
                                           <div class="text-center mb-2">
                                               <img src="/images/logo-default.png" width="60" height="60" alt="logo" />
                                               <div style="margin-top:8px">
                                                   <div><strong>${branch.name || 'Branch Name'}</strong></div>
                                                   <div style="font-size:12px">${branch.address || ''}</div>
                                                   <div style="font-size:11px">Permit #: ${branch.permit_number || ''}</div>
                                                   <div style="font-size:11px">DTI: ${branch.dti_issued || ''}</div>
                                                   <div style="font-size:11px">POS SN: ${branch.pos_sn || ''}</div>
                                                   <div style="font-size:11px">MIN#: ${branch.min_number || ''}</div>
                                               </div>
                                           </div>
                                           
                                   <div style="text-align:center;font-weight:bold;font-size:14px;margin-bottom:15px">ACKNOWLEDGEMENT SLIP</div>
                                   
                                   <div style="text-align:center;font-size:12px;margin-bottom:10px">
                                       <div>OR #: ${String(orderId).padStart(8,'0')}</div>
                                       <div>Date: ${orderData.created_at || ''}</div>
                                       <div style="margin-top:8px">MEAL FOR (1) SENIOR</div>
                                   </div>
               
                                   <hr style="border-top:1px solid #333;margin:10px 0"/>
               
                                   <table style="width:100%;font-size:12px;margin-top:10px">
                                       <tr>
                                           <td style="padding:3px 0">Total Bill</td>
                                           <td style="text-align:right;padding:3px 0">₱${nf.format(totalBill)}</td>
                                       </tr>
                                       <tr>
                                           <td style="padding:3px 0">Less Vat Exemption 12%</td>
                                           <td style="text-align:right;padding:3px 0">₱${nf.format(lessVatExempt)}</td>
                                       </tr>
                                       <tr>
                                           <td style="padding:3px 0">and Senior Discount 20%</td>
                                           <td style="text-align:right;padding:3px 0">₱${nf.format(discount20)}</td>
                                       </tr>
                                       <tr style="border-top:1px solid #333">
                                           <td style="padding:5px 0;font-weight:bold">Total Priv. Discount</td>
                                           <td style="text-align:right;padding:5px 0;font-weight:bold">₱${nf.format(totalPrivDiscount)}</td>
                                       </tr>
                                       <tr>
                                           <td style="padding:5px 0;font-weight:bold">Net Sr. Citizen Bill</td>
                                           <td style="text-align:right;padding:5px 0;font-weight:bold">₱${nf.format(netSrBill)}</td>
                                       </tr>
                                   </table>
               
                                   <div style="margin-top:15px">
                                       <div style="font-size:11px;margin-bottom:5px">SIGN:</div>
                                       <div style="border-bottom:1px solid #000;height:30px;margin-bottom:10px"></div>
                                   </div>
               
                                   <div style="margin-top:10px">
                                       <div style="font-size:11px;font-weight:bold;margin-bottom:5px">NAME / ID#</div>
                                       ${srpwdArr.map(e => `
                                           <div style="display:flex;justify-content:space-between;padding:3px 0;font-size:12px">
                                               <span>${(e.person_name || e.name || '-')}</span>
                                               <span>${(e.person_id_number || e.id_number || '')}</span>
                                           </div>
                                       `).join('')}
                                   </div>
               
                                     <!-- Footer -->
                                           <div style="margin-top:20px;font-size:11px">
                                               <div style="display:flex;justify-content:space-between;font-weight:bold">
                                                   <span>POS Provided by:</span>
                                                   <span>OMNI Systems Solutions</span>
                                               </div>
                                               <div style="margin-top:5px">
                                                   <div><strong>TIN: ${branch.tin || '123-456-789'}</strong></div>
                                                   <div>OMNI Address: A. C. Cortes Ave, Mandaue, 6014 Cebu</div>
                                               </div>
                                           </div>
                               </div>
                           `;
                       }
               
                       modalEl.innerHTML = `
                           <div class="modal-dialog modal-sm modal-dialog-scrollable">
                               <div class="modal-content">
                                   <div class="modal-body" style="max-height:80vh; overflow-y:auto;">
                                       <div style="max-width:400px;margin:0 auto;font-family:Arial,Helvetica,sans-serif;font-size:13px;">
                                           
                                           <!-- Header -->
                                           <div class="text-center mb-2">
                                               <img src="/images/logo-default.png" width="60" height="60" alt="logo" />
                                               <div style="margin-top:8px">
                                                   <div><strong>${branch.name || 'Branch Name'}</strong></div>
                                                   <div style="font-size:12px">${branch.address || ''}</div>
                                                   <div style="font-size:11px">Permit #: ${branch.permit_number || ''}</div>
                                                   <div style="font-size:11px">DTI: ${branch.dti_issued || ''}</div>
                                                   <div style="font-size:11px">POS SN: ${branch.pos_sn || ''}</div>
                                                   <div style="font-size:11px">MIN#: ${branch.min_number || ''}</div>
                                               </div>
                                           </div>
                                           
                                           <div style="text-align:center;margin:15px 0">
                                               <strong>${isPaymentComplete ? 'OFFICIAL RECEIPT' : 'BILL-OUT INVOICE'}</strong>
                                           </div>
                                           
                                           <div style="font-size:12px;margin-bottom:10px;text-align:center">
                                               <div>Date: ${orderData.created_at || ''}</div>
                                               <div>OR #: ${String(orderId).padStart(8, '0')}</div>
                                               <div>TBL: ${orderData.table_no || ''}</div>
                                               <div># of Pax: ${orderData.number_pax || ''}</div>
                                           </div>
                                           
                                           <hr style="margin:10px 0"/>
                                           
                                           <!-- Items -->
                                           <table style="width:100%;font-size:13px;margin-bottom:10px">
                                               <thead>
                                                   <tr>
                                                       <th style="text-align:left;width:10%">QTY</th>
                                                       <th style="text-align:left;width:60%">DESCRIPTION</th>
                                                       <th style="text-align:right;width:30%">AMOUNT</th>
                                                   </tr>
                                               </thead>
                                               <tbody>
                                                   ${(orderData.details || []).map(d => `
                                                       <tr>
                                                           <td>${d.quantity}x</td>
                                                           <td>
                                                               <div>${(d.product?.name || d.component?.name || d.item_name || 'Item')}</div>
                                                               <div style="font-size:11px;color:#666">@₱${Number(d.price || 0).toFixed(2)}</div>
                                                           </td>
                                                           <td style="text-align:right">₱${((d.price || 0) * (d.quantity || 1)).toFixed(2)}</td>
                                                       </tr>
                                                   `).join('')}
                                               </tbody>
                                           </table>
                                           
                                           <hr style="margin:10px 0"/>
                                           
                                           <!-- Totals -->
                                           <table style="width:100%;font-size:13px">
                                               <tr>
                                                   <td>Gross Charge</td>
                                                   <td style="text-align:right">₱${gross.toFixed(2)}</td>
                                               </tr>
                                               <tr>
                                                   <td>Less Discount</td>
                                                   <td style="text-align:right">₱${(gross - Number(orderData.total_charge || 0)).toFixed(2)}</td>
                                               </tr>
                                               <tr>
                                                   <td>Vatable</td>
                                                   <td style="text-align:right">₱${Number(orderData.vatable || 0).toFixed(2)}</td>
                                               </tr>
                                               <tr>
                                                   <td>Vat 12%</td>
                                                   <td style="text-align:right">₱${Number(orderData.vat_12 || 0).toFixed(2)}</td>
                                               </tr>
                                               <tr>
                                                   <td>Reg Bill</td>
                                                   <td style="text-align:right">₱${(Number(orderData.vatable || 0) * 1.12).toFixed(2)}</td>
                                               </tr>
                                               <tr>
                                                   <td>SR/PWD Bill</td>
                                                   <td style="text-align:right">₱${Number(orderData.sr_pwd_bill || 0).toFixed(2)}</td>
                                               </tr>
                                               <tr>
                                                   <td><strong>Total</strong></td>
                                                   <td style="text-align:right"><strong>₱${Number(orderData.total_charge || orderData.net_amount || 0).toFixed(2)}</strong></td>
                                               </tr>
                                           </table>
               
                                           <div style="display:flex;justify-content:space-between;font-weight:bold;margin-top:10px;padding:5px 0;border-top:2px solid #000">
                                               <div>Total Charge</div>
                                               <div>₱${Number(orderData.total_charge || orderData.net_amount || 0).toFixed(2)}</div>
                                           </div>
                                           
                                           ${paymentsHtml}
                                           
                                           <!-- Footer -->
                                           <div style="margin-top:20px;font-size:11px">
                                               <div style="display:flex;justify-content:space-between;font-weight:bold">
                                                   <span>POS Provided by:</span>
                                                   <span>OMNI Systems Solutions</span>
                                               </div>
                                               <div style="margin-top:5px">
                                                   <div><strong>TIN: ${branch.tin || '123-456-789'}</strong></div>
                                                   <div>OMNI Address: A. C. Cortes Ave, Mandaue, 6014 Cebu</div>
                                               </div>
                                           </div>
               
                                           <p style="text-align:center;font-size:11px;margin-top:15px;color:#666">
                                               <em>${isPaymentComplete ? 'Thank you for your business!' : 'This is not an official receipt. Payment required to complete transaction.'}</em>
                                           </p>
               
                                           ${ackHtml}
                                       </div>
                                   </div>
                                   <div class="modal-footer d-flex justify-content-center">
                                       <button class="btn btn-outline-primary btn-sm me-2" onclick="window.print()">Print</button>
                                       <button class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                                   </div>
                               </div>
                           </div>
                       `;
               
                       document.body.appendChild(modalEl);
                       
                       modalEl.addEventListener('hidden.bs.modal', () => {
                           modalEl.remove();
                           try { window.location.reload(); } catch (e) { /* ignore */ }
                       }, { once: true });
               
                       const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                       modal.show();
               
                       console.log(`✅ Invoice modal ${orderId} shown`);
               
                   } catch (e) {
                       console.error('💥 openInvoiceModalFromResponse error', e);
                       alert('Failed to show receipt. Please refresh and try again.');
                   }
               };
               
               // =====================================
               // Intercept submitPayment() function
               // =====================================
                     window.submitPayment = function(orderId) {
                        console.log('💳 Submitting payment for order', orderId);
               
                        const payments = [];
                        const tbody = document.getElementById('payments_table_body_' + orderId);
                        if (tbody) {
                           tbody.querySelectorAll('tr[data-row-id]').forEach(tr => {
                                 const rid = tr.dataset.rowId;
                                 const method = document.getElementById(`pm_${orderId}_${rid}`)?.value || '';
                                 const ref = document.getElementById(`pref_${orderId}_${rid}`)?.value || '';
                                 const dest = document.getElementById(`pdest_${orderId}_${rid}`)?.value || '';
                                 const amount = parseFloat(document.getElementById(`pamt_${orderId}_${rid}`)?.value || 0);
                                 if (method && dest && amount > 0) {
                                    payments.push({ payment_method_id: method, reference_no: ref, cash_equivalent_id: dest, amount_paid: amount });
                                 }
                           });
                        }
               
                        if (payments.length === 0) {
                           alert('⚠️ No payments to submit. Please add at least one valid payment row.');
                           return;
                        }
               
                        const totalRendered = parseFloat(document.getElementById('payments_total_' + orderId)?.textContent?.replace(/[^\d.-]/g, '') || 0);
                        const changeAmount = parseFloat(document.getElementById('payments_change_' + orderId)?.textContent?.replace(/[^\d.-]/g, '') || 0);
               
                        // Validate that rendered payment covers total charge
                        const totalChargeStr = document.getElementById('pay_totalCharge_' + orderId)?.value || document.getElementById('totalCharge_' + orderId)?.value || 0;
                        const totalCharge = parseFloat(String(totalChargeStr).replace(/,/g, '')) || 0;
                        if (totalRendered < totalCharge) {
                           alert('⚠️ Insufficient payment. Total rendered (' + Number(totalRendered).toFixed(2) + ') is less than total charge (' + Number(totalCharge).toFixed(2) + ').');
                           return;
                        }
               
                        const payload = new FormData();
                        payload.append('payments', JSON.stringify(payments));
                        payload.append('total_payment_rendered', totalRendered);
                        payload.append('change_amount', changeAmount);
               
                        const token = document.querySelector('meta[name="csrf-token"]').content;
               
                        fetch('/orders/' + orderId + '/payment', {
                           method: 'POST',
                           headers: { 'X-CSRF-TOKEN': token },
                           body: payload,
                           credentials: 'same-origin'
                        })
                        .then(res => res.json())
                        .then(data => {
                           console.log('✅ Payment response:', data);
               
                           if (data.success) {
               
                           // Update UI
                           const row = document.querySelector(`.toggle-details[data-id="${orderId}"]`)?.closest('tr');
                           if (row) {
                                 const statusCell = row.querySelectorAll('td')[5];
                                 if (statusCell) statusCell.textContent = 'Paid';
                           }
               
                           // Close payment modal, then show invoice
                           const paymentModal = document.getElementById('paymentModal' + orderId);
                        if (paymentModal && typeof bootstrap !== 'undefined') {
                           const pm = bootstrap.Modal.getInstance(paymentModal) || new bootstrap.Modal(paymentModal);
                           paymentModal.addEventListener('hidden.bs.modal', function onHidden() {
                              paymentModal.removeEventListener('hidden.bs.modal', onHidden);
                              if (data.order) {
                                 window.openInvoiceModalFromResponse(data.order);
                              }
                           });
                           pm.hide();
               
                               // (removed immediate reload — page will refresh when the receipt modal is closed)
                        } else if (data.order) {
                           // fallback if no modal found: open invoice then reload
                           window.openInvoiceModalFromResponse(data.order);
                               // (removed immediate reload — page will refresh when the receipt modal is closed)
                        }
                       } else {
                           alert('❌ Failed to save payment: ' + (data.message || 'Unknown error.'));
                       }
                   })
                   .catch(err => {
                       console.error('💥 Payment error', err);
                       alert('Error saving payment.');
                   });
               };
            </script>
            <script>
               function toggleDiscountDropdown(orderId) {
               const dropdown = document.getElementById('discountDropdown_' + orderId);
               dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
               }
               
               function updateSelectedDiscounts(orderId) {
               const dropdown = document.getElementById('discountDropdown_' + orderId);
               const input = document.getElementById('selectedDiscountName_' + orderId);
               const hidden = document.getElementById('discountIds_' + orderId);
               
               const checked = dropdown.querySelectorAll('input[type="checkbox"]:checked');
               const names = Array.from(checked).map(chk => chk.dataset.name);
               const ids = Array.from(checked).map(chk => chk.value);
               
               input.value = names.join(', ');
               hidden.value = ids.join(',');
               }
            </script>
         </div>
         <!----> 
         <div class="vgt-wrap__footer vgt-clearfix">
            <div class="footer__row-count vgt-pull-left">
               <form>
                  <label for="vgt-select-rpp-1724262253017" class="footer__row-count__label">Rows per page:</label> 
                  <select id="vgt-select-rpp-1724262253017" autocomplete="off" name="perPageSelect" aria-controls="vgt-table" class="footer__row-count__select">
                     <option value="10">
                        10
                     </option>
                     <option value="20">
                        20
                     </option>
                     <option value="30">
                        30x1
                     </option>
                     <option value="40">
                        40
                     </option>
                     <option value="50">
                        50
                     </option>
                     <!---->
                  </select>
               </form>
            </div>
            <div class="footer__navigation vgt-pull-right">
               <div data-v-347cbcfa="" class="footer__navigation__page-info">
                  <div data-v-347cbcfa="">
                     0 - 0 of 0
                  </div>
               </div>
               <!----> <button type="button" aria-controls="vgt-table" class="footer__navigation__page-btn disabled"><span aria-hidden="true" class="chevron left"></span> <span>prev</span></button> <button type="button" aria-controls="vgt-table" class="footer__navigation__page-btn disabled"><span>next</span> <span aria-hidden="true" class="chevron right"></span></button> <!---->
            </div>
         </div>
      </div>
   </div>
</div>

</div>
</div>
</div>
@endsection
@section('scripts')
<script>
   window.currentPage = "{{ request()->is('components*') ? 'components' : 'products' }}";
   
   document.addEventListener("DOMContentLoaded", function () {
   const toggles = document.querySelectorAll(".toggle-details");
   
   toggles.forEach(toggle => {
   toggle.addEventListener("change", function () {
       const orderId = this.dataset.id;
   
       // Hide all detail rows
       document.querySelectorAll(".order-details").forEach(row => {
           row.style.display = "none";
       });
   
       // Uncheck all checkboxes
       toggles.forEach(t => {
           if (t !== this) t.checked = false;
       });
   
       // Show selected details if checked
       const detailsRow = document.getElementById("details-" + orderId);
       if (this.checked && detailsRow) {
           detailsRow.style.display = "table-row";
       }
   });
   });
   });
</script>
<script src="{{ asset('js/tableFunctions.js') }}"></script>
<script>
   function openBillOutFromPayment(orderId) {
   // Close payment modal
   const paymentModalEl = document.getElementById('paymentModal' + orderId);
   const paymentModal = bootstrap.Modal.getInstance(paymentModalEl);
   if (paymentModal) paymentModal.hide();
   
   // Small delay to avoid overlap animation glitch
   setTimeout(() => {
       const billOutModalEl = document.getElementById('billOutModal' + orderId);
       const billOutModal = new bootstrap.Modal(billOutModalEl);
       billOutModal.show();
   }, 300);
   }
   
   function submitPayment(orderId) {
   console.log('💳 Submitting payment for order', orderId);
   
   const payments = [];
   const tbody = document.getElementById('payments_table_body_' + orderId);
   
   if (tbody) {
       tbody.querySelectorAll('tr[data-row-id]').forEach(tr => {
           const rid = tr.dataset.rowId;
           const methodId = document.getElementById(`pm_${orderId}_${rid}`)?.value || '';
           const ref = document.getElementById(`pref_${orderId}_${rid}`)?.value || '';
           const dest = document.getElementById(`pdest_${orderId}_${rid}`)?.value || '';
           const amount = parseFloat(document.getElementById(`pamt_${orderId}_${rid}`)?.value || 0);
           
           if (methodId && amount > 0) {
               // ✅ Validate Cash payment has destination
               const methodName = (window.paymentMethodsMap && window.paymentMethodsMap[methodId]) || '';
               const isCash = String(methodName).trim().toLowerCase() === 'cash';
               
               if (isCash && !dest) {
                   alert('⚠️ Cash payment requires a Cash On Hand destination.\n\nPlease create a Cash On Hand account if you haven\'t already.');
                   return;
               }
               
               if (dest && amount > 0) {
                   payments.push({ 
                       payment_method_id: methodId, 
                       reference_no: ref, 
                       cash_equivalent_id: dest, 
                       amount_paid: amount 
                   });
               }
           }
       });
   }
   
   if (payments.length === 0) {
       alert('⚠️ No valid payments to submit. Please add at least one payment with valid amount and destination.');
       return;
   }
   
   const totalRendered = parseFloat(
       document.getElementById('payments_total_' + orderId)?.textContent?.replace(/[^\d.-]/g, '') || 0
   );
   const changeAmount = parseFloat(
       document.getElementById('payments_change_' + orderId)?.textContent?.replace(/[^\d.-]/g, '') || 0
   );
   
   const totalChargeStr = document.getElementById('pay_totalCharge_' + orderId)?.value || 
                          document.getElementById('totalCharge_' + orderId)?.value || 0;
   const totalCharge = parseFloat(String(totalChargeStr).replace(/,/g, '')) || 0;
   
   if (totalRendered < totalCharge) {
       alert(`⚠️ Insufficient payment.\n\nTotal Rendered: ₱${Number(totalRendered).toFixed(2)}\nTotal Charge: ₱${Number(totalCharge).toFixed(2)}\nShortfall: ₱${Number(totalCharge - totalRendered).toFixed(2)}`);
       return;
   }
   
   const payload = new FormData();
   payload.append('payments', JSON.stringify(payments));
   payload.append('total_payment_rendered', totalRendered);
   payload.append('change_amount', changeAmount);
   
   const token = document.querySelector('meta[name="csrf-token"]').content;
   
   fetch('/orders/' + orderId + '/payment', {
       method: 'POST',
       headers: { 'X-CSRF-TOKEN': token },
       body: payload,
       credentials: 'same-origin'
   })
   .then(res => res.json())
   .then(data => {
       console.log('✅ Payment response:', data);
   
       if (data.success) {
           // Update UI and show receipt
           const row = document.querySelector(`.toggle-details[data-id="${orderId}"]`)?.closest('tr');
           if (row) {
               const statusCell = row.querySelectorAll('td')[6];
               if (statusCell) statusCell.textContent = 'Payments';
           }
   
           const paymentModal = document.getElementById('paymentModal' + orderId);
           if (paymentModal && typeof bootstrap !== 'undefined') {
               const pm = bootstrap.Modal.getInstance(paymentModal) || new bootstrap.Modal(paymentModal);
               paymentModal.addEventListener('hidden.bs.modal', function onHidden() {
                   paymentModal.removeEventListener('hidden.bs.modal', onHidden);
                   if (data.order) {
                       window.openInvoiceModalFromResponse(data.order);
                   }
               });
               pm.hide();
           } else if (data.order) {
               window.openInvoiceModalFromResponse(data.order);
           }
       } else {
           alert('❌ Failed to save payment: ' + (data.message || 'Unknown error.'));
       }
   })
   .catch(err => {
       console.error('💥 Payment error', err);
       alert('❌ Error saving payment. Please try again.');
   });
   }
   
   new Vue({
   el: '#orderTypeApp',
   data: {
   orderType: 'Dine-In', // default value
   },
   methods: {
   submitOrderType() {
     const modal = bootstrap.Modal.getInstance(document.getElementById('orderTypeModal'));
     modal.hide();
     let url = `{{ url('order/create') }}?type=${encodeURIComponent(this.orderType)}`;
     if (window._pendingTableNo != null) {
       url += `&table_no=${encodeURIComponent(window._pendingTableNo)}`;
       window._pendingTableNo = null;
     }
     window.location.href = url;
   }
   }
   });
   
   
</script>
<script>
   document.addEventListener('DOMContentLoaded', function () {
      // Attach reload-on-close to any existing (server-rendered) invoice modals so
      // the orders list refreshes after the user closes the receipt (e.g., after printing).
      document.querySelectorAll('[id^="invoiceModal"]').forEach(function(modalEl) {
         // mark when shown so we only reload when it was actually opened by the user
         modalEl.addEventListener('show.bs.modal', function () { modalEl.dataset._shown = '1'; });
         modalEl.addEventListener('hidden.bs.modal', function () {
            if (modalEl.dataset._shown) {
               try { window.location.reload(); } catch (e) { /* ignore */ }
            }
         });
      });
   });
</script>
@if($status === 'serving')
<script>
   // ===== SERVING TAB: TABULAR / LAYOUT VIEW =====
   
   // Orders currently on the serving tab (table_no + status)
   @php
   $_lvOrders = ($filteredOrders ?? collect())->values()->map(fn($o) => [
       'id'       => $o->id,
       'table_no' => $o->table_no,
       'status'   => $o->status,
       'waiter'   => $o->user?->name ?? 'N/A',
       'pax'      => $o->number_pax,
   ]);
   @endphp
   const _servingOrders = @json($_lvOrders);
   
   function setServingView(type) {
      const tableWrap  = document.getElementById('vgt-order-wrap');
      const layoutView = document.getElementById('layout-view');
      const btnTabular = document.getElementById('btn-tabular');
      const btnLayout  = document.getElementById('btn-layout');
   
      if (type === 'tabular') {
         if (tableWrap)  tableWrap.style.display  = '';
         if (layoutView) layoutView.style.display  = 'none';
         if (btnTabular) btnTabular.classList.add('active');
         if (btnLayout)  btnLayout.classList.remove('active');
      } else {
         if (tableWrap)  tableWrap.style.display  = 'none';
         if (layoutView) layoutView.style.display  = '';
         if (btnTabular) btnTabular.classList.remove('active');
         if (btnLayout)  btnLayout.classList.add('active');
         _loadLayoutList();
      }
   }
   
   async function _loadLayoutList() {
      const select = document.getElementById('layoutSelect');
      if (select.dataset.loaded) return; // already populated
      try {
         const res     = await fetch('{{ route("table-layouts.list") }}');
         const layouts = await res.json();
         select.innerHTML = '<option value="">-- Select a layout --</option>';
         layouts.forEach(l => {
            const opt = document.createElement('option');
            opt.value       = l.id;
            opt.textContent = l.name;
            select.appendChild(opt);
         });
         select.dataset.loaded = '1';
         // Auto-render the first layout if available
         if (layouts.length) {
            select.value = layouts[0].id;
            loadSelectedLayout();
         }
      } catch (e) {
         console.error('Failed to load layout list', e);
      }
   }
   
   async function loadSelectedLayout() {
      const id = document.getElementById('layoutSelect').value;
      if (!id) return;
      try {
         const res    = await fetch(`{{ url('settings/table-layouts') }}/${id}`);
         const result = await res.json();
         if (!result.success) { alert('Failed to load layout.'); return; }
         _renderLayout(result.data);
      } catch (e) {
         console.error('Failed to load layout', e);
         alert('Error loading layout.');
      }
   }
   
   // Build ordersMap keyed by table_no for the shared canvas viewer
   function _buildOrdersMap() {
      const map = {};
      _servingOrders.forEach(o => { map[String(o.table_no)] = o; });
      return map;
   }
   
   // Occupied-table click handler: show order detail modal
   function _onOccupiedClick(order) {
      // Header info
      var header = document.getElementById('lodm-header');
      header.innerHTML =
         '<div class="col-6 col-md-3"><label class="fw-bold d-block">Order No</label><span>' + order.id + '</span></div>' +
         '<div class="col-6 col-md-3"><label class="fw-bold d-block">Table No</label><span>' + order.table_no + '</span></div>' +
         '<div class="col-6 col-md-3"><label class="fw-bold d-block">Waiter</label><span>' + (order.waiter || 'N/A') + '</span></div>' +
         '<div class="col-6 col-md-3"><label class="fw-bold d-block">No. of Pax</label><span>' + (order.pax || 'N/A') + '</span></div>' +
         '<div class="col-6 col-md-3"><label class="fw-bold d-block">Status</label><span class="badge bg-warning text-dark">' + order.status + '</span></div>';

      // Details table — clone from the hidden row already rendered by Blade
      var detailsEl = document.getElementById('lodm-details');
      var sourceRow = document.getElementById('details-' + order.id);
      if (sourceRow) {
         detailsEl.innerHTML = sourceRow.querySelector('td').innerHTML;
      } else {
         detailsEl.innerHTML = '<p class="text-muted">No details available.</p>';
      }

      // Edit link
      document.getElementById('lodm-edit-btn').href = '{{ route("orders.edit", "__ID__") }}'.replace('__ID__', order.id);

      bootstrap.Modal.getOrCreateInstance(document.getElementById('layoutOrderDetailModal')).show();
   }
   
   // ── Floor management ──────────────────────────────────────────────────────────
   let _currentLayoutData = null;
   
   function _renderLayout(data) {
      _currentLayoutData = data;
      _buildFloorButtons(data);
   
      // Auto-activate the first floor
      const floors = (data && data.floors) ? data.floors : {};
      const firstFloor = Object.keys(floors).sort()[0];
      if (firstFloor) _switchFloor(firstFloor);
   }
   
   function _buildFloorButtons(data) {
      const floorsEl = document.getElementById('layout-floors');
      floorsEl.innerHTML = '';

      const floors   = (data && data.floors) ? data.floors : {};
      const floorIds = Object.keys(floors).sort();

      floorsEl.style.display = floorIds.length > 1 ? '' : 'none';

      floorIds.forEach(function(floorId) {
         const btn = document.createElement('button');
         btn.type          = 'button';
         btn.textContent   = 'Floor ' + floorId;
         btn.dataset.floor = floorId;
         btn.style.cssText = 'padding:5px 16px;border-radius:6px;border:1px solid #ccc;' +
                             'font-size:13px;cursor:pointer;background:#fff;color:#333;margin-right:8px;';
         btn.addEventListener('click', function() { _switchFloor(floorId); });
         floorsEl.appendChild(btn);
      });
   }

   function _applyFloorBtnStyles(activeFloorId) {
      document.querySelectorAll('#layout-floors button').forEach(function(btn) {
         if (btn.dataset.floor === String(activeFloorId)) {
            btn.style.background  = '#ff630f';
            btn.style.color       = '#fff';
            btn.style.borderColor = '#ff630f';
         } else {
            btn.style.background  = '#fff';
            btn.style.color       = '#333';
            btn.style.borderColor = '#ccc';
         }
      });
   }

   // Available table clicked: open the Add-order modal with table number pre-filled
   function _onAvailableClick(tableNo) {
      window._pendingTableNo = tableNo;
      var modal = new bootstrap.Modal(document.getElementById('orderTypeModal'));
      modal.show();
   }

   // Clear pending table number if modal is dismissed without submitting
   document.getElementById('orderTypeModal').addEventListener('hidden.bs.modal', function() {
      window._pendingTableNo = null;
   });

   function _switchFloor(floorId) {
      _applyFloorBtnStyles(floorId);

      const canvas = document.getElementById('layout-canvas');
      const floors = (_currentLayoutData && _currentLayoutData.floors) ? _currentLayoutData.floors : {};
      const items  = floors[String(floorId)] || [];
      renderFloorItems(canvas, items, _buildOrdersMap(), _onOccupiedClick, _onAvailableClick);
   }
</script>
{{-- Shared canvas viewer: CSS + renderLayoutCanvas() --}}
@include('settings.table_layouts._canvas_viewer')
@endif
@endsection