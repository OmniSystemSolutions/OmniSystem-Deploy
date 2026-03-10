@extends('layouts.app')
@section('content')

<div class="main-content">
    <div>
        <div class="breadcrumb">
            <h1 class="mr-3">Sales Journal</h1>
            <ul>
                <li><a href="">Reports</a></li>
                <li>Sales</li>
            </ul>
            <div class="breadcrumb-action"></div>
        </div>
        <div class="separator-breadcrumb border-top"></div>
    </div>

    {{-- Summary Cards --}}
   {{-- Filters and Summary --}}
        <div class="wrapper">
            <div class="row mb-4 justify-content-between">
                <div class="col-sm-12 col-md-4">
                    
                <form method="GET" action="{{ route('reports.sales-journal') }}">
                    <div class="row">
                        <div class="col-sm-12 col-lg-6">
                            <fieldset class="form-group">
                                <legend class="col-form-label pt-0">Select Year *</legend>
                                <select name="year" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Years</option>
                                    @for($y = now()->year; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </fieldset>
                        </div>

                        <div class="col-sm-12 col-lg-6">
                            <fieldset class="form-group">
                                <legend class="col-form-label pt-0">Select Month *</legend>
                                <select name="month" class="form-control" onchange="this.form.submit()">
                                    <option value="all" {{ request('month') == 'all' ? 'selected' : '' }}>All Months</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </fieldset>
                        </div>
                    </div>
                </form>
                    <button type="button" class="btn mt-2 btn-primary">Generate X Report</button>
                    <button type="button" class="btn mt-2 btn-primary" data-bs-toggle="modal" data-bs-target="#GenerateZReport">
    Generate Z Report
</button>

                </div>

                
<!-- Generate Z Report Modal -->
<div class="modal fade" id="GenerateZReport" tabindex="-1" aria-labelledby="GenerateZReportLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="GenerateZReportLabel">Generate Z Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <!-- Date Range Picker -->
                    <div class="col-md-12 mb-3">
                        <label for="date_range" class="form-label">Select Date Range *</label>
                        <input type="text" id="date_range" name="date_range" class="form-control" required readonly>
                    </div>

                    <!-- Cashier -->
                    <div class="col-md-12 mb-3">
                        <label for="cashier_name" class="form-label">Select Cashier</label>
                        <input type="text" class="form-control" id="cashier_name" 
                               value="{{ auth()->user()->name ?? '' }}" readonly>
                    </div>

                    <!-- Submit -->
                    <div class="col-md-12 mt-3">
                        <button type="button" class="btn btn-warning w-100 text-white"
                                style="background-color:#ff6600; border:none;" id="generateZReportBtn">
                            Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Z Report Modal -->
<div class="modal fade" id="ZReportModal" tabindex="-1" aria-labelledby="ZReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-scrollable"> 
    <div class="modal-content" style="max-height: 90vh;"> <!-- Limit modal height -->
      
      <div class="modal-header">
        <h5 class="modal-title" id="ZReportModalLabel">Z READING REPORT</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- 🧾 Scrollable modal body -->
      <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 120px);">
        <div style="font-family: Arial, Helvetica, sans-serif; font-size: 13px;">
          <p><strong>DATE (From):</strong> <span id="zFromDate"></span></p>
          <p><strong>DATE (To):</strong> <span id="zToDate"></span></p>
          <hr>

          <p><strong>Gross Sales:</strong> ₱<span id="zGrossSales">0.00</span></p>
          <p><strong>Less Discount:</strong> ₱<span id="zLessDiscount">0.00</span></p>
          <p><strong>Less Tax Exempt:</strong> ₱<span id="zTaxExempt">0.00</span></p>
          <p><strong>Total:</strong> ₱<span id="zTotal">0.00</span></p>
          <hr>

          <p><strong>12% VAT:</strong> ₱<span id="zVat12">0.00</span></p>
          <p><strong>VAT Incl Sales:</strong> ₱<span id="zVatIncl">0.00</span></p>
          <p><strong>VAT Excl Sales:</strong> ₱<span id="zVatExcl">0.00</span></p>
          <hr>

          <p><strong>REVENUE</strong></p>
          <p>Food: ₱<span id="zFood">0.00</span></p>
          <p>Drinks: ₱<span id="zDrinks">0.00</span></p>
          <p>Discount: ₱<span id="zDiscount">0.00</span></p>
          <p>Tax Exempt: ₱<span id="zTaxExempt2">0.00</span></p>
          <hr>

          <p><strong>COLLECTION</strong></p>
          <p>Cash: ₱<span id="zCash">0.00</span></p>
          <p>GCash: ₱<span id="zGcash">0.00</span></p>
          <p>Debit Card: ₱<span id="zDebitCard">0.00</span></p>
          <p>Credit Card: ₱<span id="zCreditCard">0.00</span></p>
          <p>Check: ₱<span id="zCheck">0.00</span></p>
          <hr>

          <p><strong>TOTAL TRANSACTIONS:</strong> <span id="zTotalTransactions">0</span></p>
        </div>
      </div>

      <!-- 📎 Sticky footer always visible -->
      <div class="modal-footer d-flex justify-content-end" 
           style="background-color: #f8f9fa; position: sticky; bottom: 0; z-index: 100;">
        <button class="btn btn-outline-primary btn-sm me-2" onclick="window.print()">Print</button>
        <button class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#date_range').daterangepicker({
        locale: { format: 'YYYY-MM-DD' },
        opens: 'center'
    });

    $('#generateZReportBtn').on('click', function() {
        const dateRange = $('#date_range').val();
        if (!dateRange) {
            alert('Please select a date range first.');
            return;
        }

        const [from, to] = dateRange.split(' - ');

        $.ajax({
            url: "{{ route('reports.sales-journal') }}",
            type: "GET",
            data: { from_date: from, to_date: to },
            beforeSend: function() {
                $('#generateZReportBtn').prop('disabled', true).text('Loading...');
            },
            success: function(response) {
                console.log('✅ Response:', response);
                $('#zFromDate').text(from);
                $('#zToDate').text(to);
                $('#zGrossSales').text(
                    parseFloat(response.gross_total || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );
                $('#zLessDiscount').text(
                    parseFloat(response.less_discount || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );
                $('#zTaxExempt').text(
                    parseFloat(response.tax_exempt || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );
                $('#zTotal').text(
                    parseFloat(response.net_total || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );
                $('#zVat12').text(
                    parseFloat(response.vat_12 || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );
                $('#zVatIncl').text(
                    parseFloat(response.vat_inclusive || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );
                $('#zVatExcl').text(
                    parseFloat(response.vat_exclusive || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );
                $('#zFood').text(
                    parseFloat(response.food_total || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );
                $('#zDrinks').text(
                    parseFloat(response.drinks_total || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );
                $('#zDiscount').text(
                    parseFloat(response.food_and_drinks_discount_total || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );

                // 💸 Collections
                $('#zCash').text(
                    parseFloat(response.collections.cash || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );

                $('#zGcash').text(
                    parseFloat(response.collections.gcash || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );

                $('#zDebitCard').text(
                    parseFloat(response.collections.debit_card || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );

                $('#zCreditCard').text(
                    parseFloat(response.collections.credit_card || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );

                $('#zCheck').text(
                    parseFloat(response.collections.check || 0)
                        .toLocaleString('en-PH', { minimumFractionDigits: 2 })
                );

                const modal1 = bootstrap.Modal.getInstance(document.getElementById('GenerateZReport'));
                if (modal1) modal1.hide();

                const modal2 = new bootstrap.Modal(document.getElementById('ZReportModal'));
                modal2.show();
            },
            error: function(xhr) {
                console.error('❌ AJAX Error:', xhr.responseText);
                alert('Server error! Check console for details.');
            },
            complete: function() {
                $('#generateZReportBtn').prop('disabled', false).text('Generate Z Report');
            }
        });
    });
});
</script>

<!-- Initialize Date Range Picker + Filtering -->
<script>
$(function() {
    // Initialize the Date Range Picker
    $('#date_range').daterangepicker({
        opens: 'right',
        autoApply: true,
        locale: {
            format: 'MM/DD/YYYY'
        },
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Year': [moment().startOf('year'), moment().endOf('year')]
        }
    });

    // Filter data when clicking Submit
    $('#filterZReportBtn').on('click', function() {
        const dateRange = $('#date_range').val().trim();
        const cashier = $('#cashier_name').val().trim();
        if (!dateRange) {
            alert('Please select a date range.');
            return;
        }

        const [start, end] = dateRange.split('-').map(d => new Date(d.trim()));
        $('#zReportTable tbody tr').each(function() {
            const rowDate = new Date($(this).data('date'));
            const rowCashier = $(this).data('cashier');
            const inRange = rowDate >= start && rowDate <= end;
            const cashierMatch = cashier === rowCashier;
            $(this).toggle(inRange && cashierMatch);
        });

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('GenerateZReport'));
        modal.hide();
    });
});
</script>

                <div class="col-sm-12 col-md-2"></div>

                <div class="col-sm-12 col-md-6">
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="card card-icon mb-4 text-center">
                                <div class="card-body">
                                    <p class="mt-2 mb-2 text-uppercase">Total Sales Transactions</p>
                                    <p class="text-primary text-24 line-height-1 m-0">{{ number_format($summary['total_transactions'] ?? 0) }}</p>
                                </div>
                            </div>
                            <div class="card card-icon text-center">
                                <div class="card-body">
                                    <p class="mt-2 mb-2 text-uppercase">Total Gross Sales</p>
                                    <p class="text-primary text-24 line-height-1 m-0">₱{{ number_format($summary['gross_total'] ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-6">
                            <div class="card card-icon">
                                <div class="card-body p-3">
                                    <p class="mb-2">{{ now()->year }} Sales Breakdown</p>
                                    <div class="chart" style="height: 260px;">
                                        {{-- You can insert your Chart.js or ECharts canvas here --}}
                                        <canvas id="salesBreakdownChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('salesBreakdownChart').getContext('2d');
    const salesBreakdownChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Walk-in', 'Take-out', 'Delivery'],
            datasets: [{
                data: [2800, 550, 110], // static sample values
                backgroundColor: ['#f44336', '#4caf50', '#2196f3'],
                borderColor: '#fff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const value = context.parsed;
                            return `${context.label}: ₱${value.toLocaleString()}`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush

    {{-- Table Wrapper --}}
    <div class="card wrapper">
        <div class="card-body">
            <div class="vgt-wrap">
                <div class="vgt-inner-wrap">
                    {{-- Search & Action Buttons --}}
                    <div class="vgt-global-search vgt-clearfix">
                        <div class="vgt-global-search__input vgt-pull-left">
                            <form role="search">
                                <label for="sales-search">
                                    <span aria-hidden="true" class="input__icon">
                                        <div class="magnifying-glass"></div>
                                    </span>
                                    <span class="sr-only">Search</span>
                                </label>
                                <input id="sales-search" type="text" placeholder="Search this table"
                                       class="vgt-input vgt-pull-left">
                            </form>
                        </div>

                        <div class="vgt-global-search__actions vgt-pull-right">
                            <div class="mt-2 mb-3">
                                <button type="button" class="btn btn-outline-success ripple m-1 btn-sm">
                                    <i class="i-File-Copy"></i> PDF
                                </button>
                                <button type="button" class="btn btn-outline-danger ripple m-1 btn-sm">
                                    <i class="i-File-Excel"></i> EXCEL
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Sales Journal Table --}}
                    <div class="vgt-responsive mt-3">
                        <table id="vgt-table" class="table-hover tableOne vgt-table">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Order ID</th>
                                    <th>Cashier</th>
                                    <th>Invoice No.</th>
                                    <th>Total Charge</th>
                                    <th>Amount Paid</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                @if($order->status == 'payments')
                    <tr>
                        <td>{{ $order->created_at }}</td>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->cashier->name }}</td>
                        <td>{{ $order->id }}</td>
                        <td>₱{{ number_format($order->total_charge, 2) }}</td>
                        <td>₱{{ number_format($order->total_payment_rendered, 2) }}</td>
                        {{-- <td>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#invoiceModal{{ $order->id }}">
                                View Sales Invoice
                            </button>
                        </td> --}}

                        <td class="text-right">
                        @include('layouts.actions-dropdown', [
                            'id' => $order->id,
                            // This is the dropdown option that triggers the modal
                            'viewRoute' => '#',
                            'viewLabel' => 'View Sales Invoice',
                            'viewModalId' => "invoiceModal{$order->id}",
                            'remarksRoute' => '#',
                        ])
                        </td>
                    </tr>
                @endif
            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Footer --}}
                    <div class="vgt-wrap__footer vgt-clearfix">
                        <div class="footer__row-count vgt-pull-left">
                            <form>
                                <label for="rows" class="footer__row-count__label">Rows per page:</label>
                                <select id="rows" name="perPageSelect" class="footer__row-count__select">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="30">30</option>
                                    <option value="40">40</option>
                                    <option value="50">50</option>
                                    <option value="-1">All</option>
                                </select>
                            </form>
                        </div>
                        <div class="footer__navigation vgt-pull-right">
    {{-- Pagination disabled (collection returned). Enable by paginating in controller) --}}
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($orders as $order)
<div class="modal fade" id="invoiceModal{{ $order->id }}" tabindex="-1" aria-labelledby="invoiceLabel{{ $order->id }}" aria-hidden="true">
   <div class="modal-dialog modal-sm modal-dialog-scrollable">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">POS Receipt</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
            <div id="pos-invoice-{{ $order->id }}">
               <div style="max-width: 400px; margin: 0px auto; font-family: Arial, Helvetica, sans-serif;">
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

                     <h6 class="t-font-boldest mt-3">SALES INVOICE</h6>
                     <div class="mb-2">INV: {{ sprintf('%08d', $order->id) }}</div>
                     <div class="mb-1">Date: {{ $order->created_at->format('Y-m-d H:i') }}</div>
                     <div class="mb-1">TBL: {{ $order->table_no }}</div>
                     <div class="mb-1"># of Pax: {{ $order->number_pax }}</div>
                  </div>

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

                  <table class="table table-invoice-data" style="width:100%; font-size:13px;">
                     <tbody>
                        <tr>
                           <td>Gross Charge</td>
                           <td class="text-right">₱{{ number_format($order->details->sum(fn($d) => ($d->price * $d->quantity) - ($d->discount ?? 0)), 2) }}</td>
                        </tr>
                        <tr>
                           <td>Less Discount</td>
                           <td class="text-right">₱{{ number_format($order->sr_pwd_discount ?? 0,2) }}</td>
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
                           <td class="text-right">₱{{ number_format($order->vatable ?? 0,2) }}</td>
                        </tr>
                        <tr>
                           <td>SR/PWD Bill</td>
                           <td class="text-right">₱{{ number_format($order->sr_pwd_discount ?? 0,2) }}</td>
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
                  <div class="d-flex justify-content-between fw-bold">
                     <span>Total Rendered</span>
                     <span>₱{{ number_format($order->paymentDetails->last()?->total_rendered ?? 0, 2) }}</span>
                  </div>
                  <div class="d-flex justify-content-between fw-bold">
                     <span>Change</span>
                     <span>₱{{ number_format($order->paymentDetails->last()?->change_amount ?? 0, 2) }}</span>
                  </div>

                  <p class="d-flex justify-content-between fw-bold mt-2">
                     <span>POS Provided by:</span> <span>OMNI Systems Solutions</span>
                  </p>

                  <div class="d-flex flex-column small">
                     <span class="t-font-boldest">TIN: {{ $branch->tin ?? '' }}</span>
                     <span>OMNI Address: A. C. Cortes Ave, Mandaue, 6014 Cebu</span>
                  </div>
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


</div>
@endsection
