@extends('layouts.app')
@section('content')
<style>
    .receipt-divider{
    border-top:1px dashed #000;
    margin:-10px 0px 20px 0px;
}
</style>
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
                    <button type="button" class="btn mt-2 btn-primary" data-bs-toggle="modal" data-bs-target="#GenerateXReport">
                        Generate X Report
                    </button>
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
                    <!-- Date Picker -->
                    <div class="col-md-12 mb-3">
                        <label for="zdate" class="form-label">Select Date *</label>
                        <input type="date" id="zdate" name="zdate" class="form-control" required>
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
      </div>

      <!-- 🧾 Scrollable modal body -->
      <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 120px);">
        <div style="font-family: Arial, Helvetica, sans-serif; font-size: 13px;">
          <p><strong>Date:</strong> <span id="zReportDate"></span></p>
          <p><strong>Time:</strong> <span id="ztime"></span></p>

          <p><strong>SALES SUMMARY</strong></p>
          <div class="receipt-divider"></div>
          <div style="display:flex; justify-content:space-between;">
            <span>Total Orders</span>
            <span id="zTotalOrders">0.00</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Gross Sales:</span>
            <span id="zGrossSales">0.00</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Discounts:</span>
            <span id="zDiscounts">0.00</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Net Sales:</span>
            <span id="zNetSales">0.00</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Tax:</span>
            <span id="zTax">0.00</span>
          </div>
          
          <p><strong>PAYMENT METHODS</strong></p>
          <div class="receipt-divider"></div>
          <div id="zPaymentMethods"></div>

          <p><strong>ITEM SOLD</strong></p>
          <div class="receipt-divider"></div>
          <div id="zItemsSold"></div>

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

<!-- Generate X Report Modal -->
<div class="modal fade" id="GenerateXReport" tabindex="-1" aria-labelledby="GenerateXReportLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="GenerateXReportLabel">Generate X Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">

    <!-- Date Picker -->
    <div class="col-md-12 mb-3">
        <label class="form-label">Select Date *</label>
        <input type="date"
               id="xdate"
               name="date"
               class="form-control"
               value="{{ date('Y-m-d') }}"
               required>
    </div>

    <!-- Cashier Dropdown -->
    <div class="col-md-12 mb-3">
        <label class="form-label">Select Cashier *</label>

        <select id="cashier_id" class="form-control">
            <option value="">-- Select Cashier --</option>

            @foreach($users as $user)
                <option value="{{ $user->id }}">
                    {{ $user->name }}
                </option>
            @endforeach

        </select>
    </div>

                    <!-- Submit -->
                    <div class="col-md-12 mt-3">
                        <button type="button" class="btn btn-warning w-100 text-white"
                                style="background-color:#ff6600; border:none;" id="generateXReportBtn">
                            Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- X Report Modal -->
<div class="modal fade" id="XReportModal" tabindex="-1" aria-labelledby="XReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-scrollable"> 
    <div class="modal-content" style="max-height: 90vh;"> <!-- Limit modal height -->
      
      <div class="modal-header">
        <h5 class="modal-title" id="XReportModalLabel">X READING REPORT</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- 🧾 Scrollable modal body -->
      <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 120px);">
        <div style="font-family: Arial, Helvetica, sans-serif; font-size: 13px;">
          <div style="display:flex;">
            <span>Date:</span> 
            <span id="xDate"></span>
          </div>
          <div style="display:flex;">
            <span>Time:</span> 
            <span id="xTime"></span>
          </div>
          <div style="display:flex;">
            <span>Cashier:</span> 
            <span id="xCashier"></span>
          </div>

          <p><strong>SUMARRY</strong></p>
          <div class="receipt-divider"></div>
          <div style="display:flex; justify-content:space-between;">
            <span>Total Orders</span>
            <span id="totalOrders">0</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Gross Sales:</span>
            <span id="xGrossSales">0.00</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Discounts:</span>
            <span id="xDiscounts">0.00</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Net Sales:</span>
            <span id="xNetSales">0.00</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span>Tax (12%):</span>
            <span id="xTax">0.00</span>
          </div>

          <p><strong>PAYMENT METHODS</strong></p>
          <div class="receipt-divider"></div>
          <div id="xPaymentMethods"></div>

          <p><strong>ITEM SOLD</strong></p>
          <div class="receipt-divider"></div>
          <div id="xItemsSold"></div>

          <hr>
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

    // Default date = today
    const today = new Date().toISOString().split('T')[0];
    $('#zdate').val(today);

    $('#generateZReportBtn').on('click', function() {
        const zdate = $('#zdate').val();

        if (!zdate) {
            alert('Please select a date.');
            return;
        }

        $.ajax({
            url: "{{ route('reports.sales-journal.fetch-zreport') }}",
            type: "GET",
            data: { date: zdate }, // only date

            beforeSend: function() {
                $('#generateZReportBtn').prop('disabled', true).text('Loading...');
            },

            success: function(response){

                function money(val){
                    return parseFloat(val || 0).toLocaleString('en-PH',{
                        minimumFractionDigits:2
                    });
                }

                $('#zReportDate').text(response.date);
                $('#ztime').text(response.time);

                $('#zTotalOrders').text(response.total_orders);
                $('#zGrossSales').text(money(response.gross_sales));
                $('#zDiscounts').text(money(response.discounts));
                $('#zNetSales').text(money(response.net_sales));
                $('#zTax').text(money(response.tax));

                // PAYMENT METHODS
                let paymentHTML = '';
                $.each(response.payments, function(name, amount){
                    paymentHTML += `
                    <div style="display:flex; justify-content:space-between;">
                        <span>${name}</span>
                        <span>${money(amount)}</span>
                    </div>`;
                });
                $('#zPaymentMethods').html(paymentHTML);

                // ITEMS SOLD
                let itemsHTML = '';
                $.each(response.items, function(name, qty){
                    itemsHTML += `
                    <div style="display:flex; justify-content:space-between;">
                        <span>${name}</span>
                        <span>x${qty}</span>
                    </div>`;
                });
                $('#zItemsSold').html(itemsHTML);

                new bootstrap.Modal(document.getElementById('ZReportModal')).show();
            },

            error: function(xhr) {
                console.error(xhr.responseText);
                alert('Server error.');
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

{{-- Start For X-Report  --}}
<script>
$(document).ready(function() {

    $('#generateXReportBtn').on('click', function () {

    const date = $('#xdate').val();
    const cashierId = $('#cashier_id').val();

    if (!date || !cashierId) {
        alert('Please select date and cashier.');
        return;
    }

    $.ajax({
        url: "{{ route('reports.sales-journal.fetch-xreport') }}",
        type: "GET",
        data: {
            date: date,
            cashier_id: cashierId
        },

        beforeSend: function () {
            $('#generateXReportBtn').prop('disabled', true).text('Loading...');
        },

        success: function(response) {

            console.log('✅ X Report:', response);

            // SUMMARY
            $('#xDate').text(response.date);
            $('#xTime').text(new Date().toLocaleTimeString());
            $('#xCashier').text($('#cashier_id option:selected').text());

            $('#totalOrders').text(
                    Number(response.total_orders).toLocaleString()
                );
            $('#xGrossSales').text(parseFloat(response.gross_sales).toFixed(2));
            $('#xDiscounts').text(parseFloat(response.discounts).toFixed(2));
            $('#xNetSales').text(parseFloat(response.net_sales).toFixed(2));
            $('#xTax').text(parseFloat(response.tax).toFixed(2));

            /* =========================
            PAYMENT METHODS
            ========================= */

            let paymentHtml = '';

            if (response.payments.cash > 0) {
                paymentHtml += `
                    <div style="display:flex; justify-content:space-between;">
                        <span>Cash</span>
                        <span>₱${parseFloat(response.payments.cash).toFixed(2)}</span>
                    </div>
                    `;
            }

            if (response.payments.card > 0) {
               paymentHtml += `
                <div style="display:flex; justify-content:space-between;">
                    <span>Card</span>
                    <span>₱${parseFloat(response.payments.card).toFixed(2)}</span>
                </div>
                `;
            }

            if (response.payments.e_wallet > 0) {
                paymentHtml += `
                <div style="display:flex; justify-content:space-between;">
                    <span>E-Wallet</span>
                    <span>₱${parseFloat(response.payments.e_wallet).toFixed(2)}</span>
                </div>
                `;
            }

            $('#xPaymentMethods').html(paymentHtml);


            /* =========================
            ITEMS SOLD
            ========================= */

            let itemsHtml = '';

            if (response.order_details.length > 0) {

                response.order_details.forEach(function(item) {

                    itemsHtml += `
                        <div style="display:flex; justify-content:space-between;">
                            <span>${item.name}</span>
                            <span>x${item.quantity}</span>
                        </div>
                    `;

                });

            }

            $('#xItemsSold').html(itemsHtml);


            const xReportModal1 = bootstrap.Modal.getInstance(document.getElementById('GenerateXReport'));
            if (xReportModal1) xReportModal1.hide();

            const xReportModal2 = new bootstrap.Modal(document.getElementById('XReportModal'));
            xReportModal2.show();
        },

        error: function (xhr) {
            console.error('❌ AJAX Error:', xhr.responseText);
        },

        complete: function () {
            $('#generateXReportBtn').prop('disabled', false).text('Submit');
        }
    });

});
});
</script>

<!-- Initialize Date Range Picker + Filtering -->
<script>
$(function() {
    // Initialize the Date Range Picker
    $('#xdate_range').daterangepicker({
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
    $('#filterXReportBtn').on('click', function() {
        const dateRange = $('#xdate_range').val().trim();
        const cashier = $('#cashier_name').val().trim();
        if (!dateRange) {
            alert('Please select a date range.');
            return;
        }

        const [start, end] = dateRange.split('-').map(d => new Date(d.trim()));
        $('#xReportTable tbody tr').each(function() {
            const rowDate = new Date($(this).data('date'));
            const rowCashier = $(this).data('cashier');
            const inRange = rowDate >= start && rowDate <= end;
            const cashierMatch = cashier === rowCashier;
            $(this).toggle(inRange && cashierMatch);
        });

        // Close modal
        const xReportModal = bootstrap.Modal.getInstance(document.getElementById('GenerateXReport'));
        xReportModal.hide();
    });
});
</script>
{{-- End of X-Report --}}

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
                                    <p class="mb-2">{{ request('year') ?? now()->year }} Sales Breakdown</p>
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
const salesData = @json($chartData);
document.addEventListener('DOMContentLoaded', function () {

    const ctx = document.getElementById('salesBreakdownChart').getContext('2d');

    const salesBreakdownChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Dine-In', 'Take-Out', 'Delivery'],
            datasets: [{
                data: [
                    salesData['Dine-In'],
                    salesData['Take-Out'],
                    salesData['Delivery']
                ],
                backgroundColor: ['#f44336', '#4caf50', '#2196f3'],
                borderColor: '#fff',
                borderWidth: 2
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
                                    <th>Change</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                @if($order->status == 'payments')
                                    <tr>
                                        <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->cashier->name }}</td>
                                        <td>{{ $order->id }}</td>
                                        <td>₱{{ number_format($order->total_charge, 2) }}</td>
                                        <td>₱{{ number_format($order->total_payment_rendered, 2) }}</td>
                                        <td>₱{{ number_format($order->change_amount, 2) }}</td>
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
