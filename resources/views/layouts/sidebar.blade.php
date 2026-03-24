<!-- Vue -->
<script src="https://unpkg.com/vue@2.7.14/dist/vue.js"></script>
<!-- Include Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
   .sidebar-left {
  height: 100vh;           /* Full screen height */
  overflow: hidden;        /* Let Perfect Scrollbar handle scrolling */
  position: relative;
}

.sidebar-left .navigation-left {
  padding: 0;
  margin: 0;
  list-style: none;
}
/* Sidebar */
.side-content-wrap {
  width: 240px;
  transition: transform 0.3s ease;
}

/* On small screens, hide by default */
@media (max-width: 1199px) {
  .side-content-wrap {
    transform: translateX(-100%);
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    background: #fff; /* adjust for your theme */
    z-index: 99;
  }

  .side-content-wrap.active {
    transform: translateX(0);
  }

  /* Overlay */
  .sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    z-index: 1040;
  }

  .sidebar-overlay.active {
    display: block;
  }
}


</style>
<div class="side-content-wrap" id="startEndApp">
   <section class="ps-container sidebar-left rtl-ps-none ps scroll open ps--active-y">
      <div>
         <ul class="navigation-left">
            @if($user->hasRole('Administrator') || $user->can('view Dashboard'))
            <li data-item="dashboard" data-submenu="true" class="nav-item">
               <a href="/" class="nav-item-hold"><i class="nav-icon i-Bar-Chart"></i> <span class="nav-text">Dashboard</span></a> 
               <div class="triangle"></div>
            </li>
            @endif

             @if($user->hasRole('Administrator') || $user->can('view POS'))
            <li data-item="Sales" data-submenu="true" class="nav-item">
               <a href="#" class="nav-item-hold"><i class="nav-icon i-Full-Basket"></i> <span class="nav-text">Sales</span></a> 
               <div class="triangle"></div>
            </li>
            @endif

            @if($user->hasRole('Administrator') || $user->can('view Inventory'))
            <li data-item="Inventory" data-submenu="true" class="nav-item">
               <a href="#" class="nav-item-hold"><i class="nav-icon i-Building"></i> <span class="nav-text">Inventory</span></a> 
               <div class="triangle"></div>
            </li>
            @endif

            <li data-item="EmployeeProfile" data-submenu="true" class="nav-item" style="display: none;">
               <a href="#" class="nav-item-hold"><i class="nav-icon i-ID-2"></i> <span class="nav-text">My Profile</span></a> 
               <div class="triangle"></div>
            </li>

            @if($user->hasRole('Administrator') || $user->can('view People'))
            <li data-item="EmployeeSubordinates" data-submenu="true" class="nav-item" style="display: none;"><a href="#" class="nav-item-hold"><i class="nav-icon i-Business-Mens"></i> <span class="nav-text">My Subordinates</span></a></li>
            <li data-item="People" data-submenu="true" class="nav-item">
               <a href="#" class="nav-item-hold"><i class="nav-icon i-Business-Mens"></i> <span class="nav-text">People</span></a> 
               <div class="triangle"></div>
            </li>
            @endif

            @if($user->hasRole('Administrator') || $user->can('view Workforce'))
            <li data-item="Workforce" data-submenu="true" class="nav-item">
               <a href="#" class="nav-item-hold"><i class="nav-icon i-Library"></i> <span class="nav-text">Workforce</span></a> 
               <div class="triangle"></div>
            </li>
            @endif

            @if($user->hasRole('Administrator') || $user->can('view Accounting'))
            <li data-item="Accounting" data-submenu="true" class="nav-item">
               <a href="#" class="nav-item-hold"><i class="nav-icon i-Edit-Map"></i> <span class="nav-text">Accounting</span></a> 
               <div class="triangle"></div>
            </li>
            @endif

            @if($user->hasRole('Administrator') || $user->can('view Reports'))
            <li data-item="reports" data-submenu="true" class="nav-item">
               <a href="#" class="nav-item-hold"><i class="nav-icon i-Line-Chart"></i> <span class="nav-text">Reports</span></a> 
               <div class="triangle"></div>
            </li>
            @endif

            @if($user->hasRole('Administrator') || $user->can('view Settings'))
            <li data-item="settings" data-submenu="true" class="nav-item">
               <a href="#" class="nav-item-hold"><i class="nav-icon i-Data-Settings"></i> <span class="nav-text">Settings</span></a> 
               <div class="triangle"></div>
            </li>
            @endif

            
         </ul>
      </div>
      <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
         <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
      </div>
      <div class="ps__rail-y" style="top: 0px; height: 830px; right: 0px;">
         <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 770px;"></div>
      </div>
   </section>
   <section class="ps-container sidebar-left-secondary ps rtl-ps-none">
      <div>

         <ul data-parent="dashboard" class="childNav d-none">
               <li class="nav-item"><a href="/" class=""><i class="nav-icon i-Bar-Chart"></i> <span class="item-name">Branch</span></a> </li>
               <li class="nav-item dropdown-sidemenu">
                  <a href="#">
                     <i class="nav-icon i-Line-Chart"></i>
                     <span class="item-name">Global</span>
                     <i class="dd-arrow i-Arrow-Down"></i>
                  </a>
                  <ul class="submenu">
                     <li class="nav-item">
                        <a href="#" class="">
                           <i class="nav-icon i-Library"></i>
                           <span class="item-name">Workforce</span>
                        </a>
                     </li>
                     <li class="nav-item">
                        <a href="#" class="">
                           <i class="nav-icon i-Building"></i>
                           <span class="item-name">Inventory</span>
                        </a>
                     </li>
                     <li class="nav-item">
                        <a href="#" class="">
                           <i class="nav-icon i-Full-Basket"></i>
                           <span class="item-name">Sales</span>
                        </a>
                     </li>
                     <li class="nav-item">
                        <a href="#" class="">
                           <i class="nav-icon i-Edit-Map"></i>
                           <span class="item-name">Accounting</span>
                        </a>
                     </li>
                     <li class="nav-item">
                        <a href="#" class="">
                           <i class="nav-icon i-Business-Mens"></i>
                           <span class="item-name">People</span>
                        </a>
                     </li>
                  </ul>
               </li>
               <li class="nav-item"><a href="#" class=""><i class="nav-icon i-Administrator"></i> <span class="item-name">Log History</span></a> </li>
         </ul>

         <ul data-parent="EmployeeProfile" class="childNav d-none">
            <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!---->
         </ul>
         <ul data-parent="EmployeeSubordinates" class="childNav d-none">
            <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!----> <!---->
         </ul>
         <ul data-parent="reports" class="childNav d-none">
            <li class="nav-item"><a href="/app/reports/x-reading" class=""><i class="nav-icon i-Full-Basket"></i> <span class="item-name">POS</span></a> </li>
            <li class="nav-item dropdown-sidemenu">
               <a href="#">
                  <i class="nav-icon i-Full-Cart"></i>
                  <span class="item-name">Sales</span>
                  <i class="dd-arrow i-Arrow-Down"></i>
               </a>
               <ul class="submenu">
                  <li class="nav-item">
                     <a href="{{ route('reports.sales-journal') }}" class="">
                        <i class="nav-icon i-Book"></i>
                        <span class="item-name">Sales Journal</span>
                     </a>
                  </li>
               </ul>
            </li>
            <li class="nav-item"><a href="/app/reports/y-reading" class=""><i class="nav-icon i-Building"></i> <span class="item-name">Inventory</span></a> </li>
            <li class="nav-item"><a href="/app/reports/y-reading" class=""><i class="nav-icon i-Library"></i> <span class="item-name">Workforce</span></a> </li>
            <li class="nav-item dropdown-sidemenu">
               <a href="#">
                  <i class="nav-icon i-Receipt-3"></i> 
                  <span class="item-name">Accounting</span>
                  <i class="dd-arrow i-Arrow-Down"></i>
               </a>
               <ul class="submenu">
                  <li class="nav-item">
                     <a href="{{ route('general-ledger.index') }}" class="">
                        <i class="fa-solid fa-stairs"></i>
                        <span class="item-name">General Ledger</span>
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="{{ route('procurement-request.index') }}" class="">
                        <i class="fa-regular fa-file-lines" style="font-size:20px;"></i>
                        <span class="item-name" style="margin-left: 5px;">PRF - Procurement Request Form</span>
                     </a>
                  </li>
               </ul>
            </li>
         </ul>
         <ul data-parent="Workforce" class="childNav d-none">
            {{-- <li class="nav-item"><a href="/app/workforce/upload-files" class=""><i class="nav-icon i-Upload-Window"></i> <span class="item-name">Upload Employee Files</span></a></li>
            <li class="nav-item"><a href="/app/workforce/assign-shifts" class=""><i class="nav-icon i-Business-Mens"></i> <span class="item-name">Assign Shifts</span></a></li>
            <li class="nav-item"><a href="/app/workforce/assign-leaves" class=""><i class="nav-icon i-Ticket"></i> <span class="item-name">Assign Leaves</span></a></li>
            <li class="nav-item"><a href="/app/workforce/assign-benefits" class=""><i class="nav-icon i-Betvibes"></i> <span class="item-name">Assign Benefits</span></a></li>
            <li class="nav-item"><a href="/app/workforce/assign-allowances" class=""><i class="nav-icon i-Money-2"></i> <span class="item-name">Assign Allowances</span></a></li> --}}
            {{-- <li class="nav-item"><a href="/app/workforce/leave-requests" class=""><i class="nav-icon i-Blinklist"></i> <span class="item-name">Daily Time Record</span></a></li> --}}
            <li class="nav-item">
               <a href="{{ route('dtr.index') }}" class="">
                  <i class="nav-icon i-Time-Machine"></i>
                  <span class="item-name">Daily Time Record</span>
               </a>
            </li>
            <li class="nav-item"><a href="/workforce/leave-requests" class=""><i class="nav-icon i-Blinklist"></i> <span class="item-name">Request For Leaves</span></a></li>
            <li class="nav-item"><a href="/app/workforce/overtime-requests" class=""><i class="nav-icon i-Over-Time"></i> <span class="item-name">Request for Overtime</span></a></li>
            {{-- <li class="nav-item"><a href="/app/workforce/restday-overtime-requests" class=""><i class="nav-icon i-Over-Time"></i> <span class="item-name">Request for Restday Overtime</span></a></li> --}}
            <li class="nav-item"><a href="/app/workforce/salary-loans" class=""><i class="nav-icon i-Credit-Card"></i> <span class="item-name">Salary Loan</span></a></li>
            <li class="nav-item"><a href="/app/workforce/payrolls" class=""><i class="nav-icon i-Receipt-4"></i> <span class="item-name">Process Payroll</span></a></li>
            {{-- <li class="nav-item"><a href="/app/workforce/time-keeper" class=""><i class="nav-icon i-Time-Machine"></i> <span class="item-name">Time Keeper</span></a></li>
            <li class="nav-item"><a href="/app/workforce/incident-reports" class=""><i class="nav-icon i-Link-2"></i> <span class="item-name">Create Incident Report</span></a></li>
            <li class="nav-item"><a href="/app/workforce/disciplinary-actions" class=""><i class="nav-icon i-Financial"></i> <span class="item-name">Create Disciplinary Actions</span></a></li>
            <li class="nav-item"><a href="/app/workforce/generate-workforce-labels" class=""><i class="nav-icon i-Network-Window"></i> <span class="item-name">Generate Workforce Labels</span></a></li>
            <li class="nav-item"><a href="/app/workforce/assign-commission-models" class=""><i class="nav-icon i-Tag-5"></i> <span class="item-name">Assign Commission Models</span></a></li>
            <li class="nav-item dropdown-sidemenu">
               <a href="#"><i class="nav-icon i-Gear"></i> <span class="item-name">Settings</span> <i class="dd-arrow i-Arrow-Down"></i></a> 
               <ul class="submenu">
                  <li><a href="/app/workforce/settings/departments" class=""><i class="nav-icon i-Window"></i> <span class="item-name">Departments</span></a></li>
                  <li><a href="/app/workforce/settings/designations" class=""><i class="nav-icon i-Worker"></i> <span class="item-name">Designations</span></a></li>
                  <li><a href="/app/workforce/settings/employment-status-list" class=""><i class="nav-icon i-ID-Card"></i> <span class="item-name">Status</span></a></li>
                  <li><a href="/app/workforce/settings/files" class=""><i class="nav-icon i-File-Cloud"></i> <span class="item-name">Files</span></a></li>
                  <li><a href="/app/workforce/settings/time_keeping" class=""><i class="nav-icon i-Time-Machine"></i> <span class="item-name">Time Keeping</span></a></li>
                  <li><a href="/app/workforce/settings/shifts" class=""><i class="nav-icon i-Chef"></i> <span class="item-name">Shifts</span></a></li>
                  <li><a href="/app/workforce/settings/night-differentials" class=""><i class="nav-icon i-Over-Time"></i> <span class="item-name">Night Differential</span></a></li>
                  <li><a href="/app/workforce/settings/leaves" class=""><i class="nav-icon i-Letter-Open"></i> <span class="item-name">Leaves</span></a></li>
                  <li><a href="/app/workforce/settings/holidays" class=""><i class="nav-icon i-Calendar-2"></i> <span class="item-name">Holidays</span></a></li>
                  <li><a href="/app/workforce/settings/benefits" class=""><i class="nav-icon i-Betvibes"></i> <span class="item-name">Benefits</span></a></li>
                  <li><a href="/app/workforce/settings/allowances" class=""><i class="nav-icon i-Money-Bag"></i> <span class="item-name">Allowances</span></a></li>
                  <li><a href="/app/workforce/settings/payroll_details" class=""><i class="nav-icon i-Mail-Money"></i> <span class="item-name">Additional Payroll Details</span></a></li>
                  <li><a href="/app/workforce/settings/commission-models" class=""><i class="nav-icon i-Tag-5"></i> <span class="item-name">Commission Models</span></a></li>
                  <li><a href="/app/workforce/settings/disciplinary-actions" class=""><i class="nav-icon i-Financial"></i> <span class="item-name">Disciplinary Actions</span></a></li>
               </ul>
            </li> --}}
         </ul>
         <ul data-parent="Inventory" class="childNav d-none">
            <li class="nav-item"><a href="/products" class=""><i class="nav-icon i-Posterous"></i> <span class="item-name">Products and Components</span></a></li>
            <li class="nav-item"><a href="/app/inventory/adjustments" class=""><i class="nav-icon i-Laptop-Secure"></i> <span class="item-name">Inventory Adjustments</span></a></li>
            {{-- <li class="nav-item"><a href="/app/inventory/procurements" class=""><i class="nav-icon i-Computer-Secure"></i> <span class="item-name">PRF - Procurement Request Form</span></a></li> --}}
            <li class="nav-item">
               <a href="{{ route('inventory_purchase_orders.index') }}" class="{{ request()->routeIs('inventory_purchase_orders.*') ? 'active' : '' }}">
                  <i class="nav-icon i-Billing"></i>
                  <span class="item-name">PO - Purchase Orders</span>
               </a>
            </li>
            <li class="nav-item">
               <a href="{{ route('kitchen-mrp.index') }}" class="{{ request()->routeIs('kitchen-mrp.*') ? 'active' : '' }}">
                  <i class="fa-solid fa-hands-holding"></i>
                  <span class="item-name">Kitchen MRP</span>
               </a>
            </li>
            <li class="nav-item"><a href="/inventory/transfer" class=""><i class="nav-icon i-Ambulance"></i> <span class="item-name">Inventory Transfer</span></a></li>
            {{-- <li class="nav-item"><a href="/app/inventory/transfers" class=""><i class="nav-icon i-Jeep-2"></i> <span class="item-name">Warehouse to Warehouse (Inbound)</span></a></li> --}}
            {{-- <li class="nav-item"><a href="/app/inventory/warehouse-transfers" class=""><i class="nav-icon i-Jeep-2"></i> <span class="item-name">Warehouse to Warehouse (Outbound)</span></a></li> --}}
            {{-- <li class="nav-item"><a href="/app/inventory/stock-requests" class=""><i class="nav-icon i-Safe-Box"></i> <span class="item-name">Branch to Branch (Inbound)</span></a></li> --}}
            {{-- <li class="nav-item"><a href="/app/inventory/send-out-stocks" class=""><i class="nav-icon i-Mail-Outbox"></i> <span class="item-name">Branch to Branch (Outbound)</span></a></li> --}}
            {{-- <li class="nav-item"><a href="/app/inventory/disbursements" class=""><i class="nav-icon i-Split-Vertical"></i> <span class="item-name">Inventory Request</span></a></li> --}}
            {{-- <li class="nav-item"><a href="/app/inventory/processed-goods" class=""><i class="nav-icon i-Recycling-2"></i> <span class="item-name">Log Processed Goods</span></a></li> --}}
            <li class="nav-item"><a href="/inventory/audits" class=""><i class="nav-icon i-Approved-Window"></i> <span class="item-name">Audits</span></a></li>
            {{-- <li class="nav-item"><a href="/app/inventory/print-labels" class=""><i class="nav-icon i-Tag-4"></i> <span class="item-name">Generate and Print Labels</span></a></li> --}}
            <li class="nav-item dropdown-sidemenu">
               {{-- <a href="#"><i class="nav-icon i-Gear"></i> <span class="item-name">Settings</span> <i class="dd-arrow i-Arrow-Down"></i></a>  --}}
               <ul class="submenu">
                  <li class="nav-item"><a href="/app/settings/warehouses" class=""><i class="nav-icon i-Building"></i> <span class="item-name">Warehouses</span></a></li>
                  <li><a href="/app/inventory/settings/assign-warehouses" class=""><i class="nav-icon i-Building"></i> <span class="item-name">Assign Default Warehouse</span></a></li>
                  <li><a href="/app/inventory/settings/categories" class=""><i class="nav-icon i-ID-Card"></i> <span class="item-name">Category</span></a></li>
                  <li><a href="/app/inventory/settings/brands" class=""><i class="nav-icon i-Landscape"></i> <span class="item-name">Brands</span></a></li>
                  <li><a href="/app/inventory/settings/units" class=""><i class="nav-icon i-Road-2"></i> <span class="item-name">Units</span></a></li>
                  <li><a href="/app/inventory/settings/supplier-types" class=""><i class="nav-icon i-Add-UserStar"></i> <span class="item-name">Supplier Types</span></a></li>
                  <li><a href="/app/inventory/settings/adjustment-types" class=""><i class="nav-icon i-Maximize-Window"></i> <span class="item-name">Adjustment Types</span></a></li>
                  <li><a href="/app/inventory/settings/discounts" class=""><i class="nav-icon i-Coins"></i> <span class="item-name">Discounts</span></a></li>
                  <li><a href="/app/inventory/settings/taxes" class=""><i class="nav-icon i-Dollar"></i> <span class="item-name">Taxes</span></a></li>
               </ul>
            </li>
         </ul>
         <ul data-parent="Sales" class="childNav d-none">
            <li class="nav-item">
               <a href="#" class="startEndTrigger">
                  <i class="nav-icon i-Clothing-Store"></i>
                  <span class="item-name">Start / End of the Day</span>
               </a>
            </li>
            
            <!-- POS link -->
            <li class="nav-item">
            <a href="/orders" class="posLink">
               <i class="nav-icon i-Receipt"></i>
               <span class="item-name">POS</span>
            </a>
            </li>
            <li class="nav-item"><a href="order-reservations" class=""><i class="nav-icon i-Jeep"></i> <span class="item-name">Orders and Reservations</span></a></li>
            <li class="nav-item">
               <a href="#" class="checkUnpaidTrigger">
                  <i class="nav-icon i-Hand"></i> 
                  <span class="item-name">Closing</span>
               </a>
            </li>
            <li class="nav-item"><a href="/kitchen" class=""><i class="nav-icon i-Full-Basket"></i> <span class="item-name">Kitchen Display System</span></a></li>
            {{-- <li class="nav-item"><a href="/app/sales/quotations" class=""><i class="nav-icon i-Receipt-3"></i> <span class="item-name">Quotations</span></a></li> --}}
            {{-- <li class="nav-item"><a href="/app/sales/manage-prospects" class=""><i class="nav-icon i-Checked-User"></i> <span class="item-name">Manage Prospects</span></a></li> --}}
            <li class="nav-item dropdown-sidemenu">
               {{-- <a href="#"><i class="nav-icon i-Gear"></i> <span class="item-name">Settings</span> <i class="dd-arrow i-Arrow-Down"></i></a>  --}}
               <ul class="submenu">
                  <li><a href="/app/sales/settings/payment-methods" class=""><i class="nav-icon i-Wallet"></i> <span class="item-name">Payment Methods</span></a></li>
                  <li><a href="/app/sales/settings/discounts" class=""><i class="nav-icon i-Coins"></i> <span class="item-name">Discounts</span></a></li>
                  <li><a href="/app/sales/settings/customer-types" class=""><i class="nav-icon i-Business-ManWoman"></i> <span class="item-name">Customer Types</span></a></li>
                  <li><a href="/app/sales/settings/sales-areas" class=""><i class="nav-icon i-Suitcase"></i> <span class="item-name">Sales Areas</span></a></li>
                  <li><a href="/app/sales/settings/sales-personnels" class=""><i class="nav-icon i-Add-UserStar"></i> <span class="item-name">Sales Personnel</span></a></li>
                  <li><a href="/app/sales/settings/taxes" class=""><i class="nav-icon i-Receipt-3"></i> <span class="item-name">Taxes</span></a></li>
                  <li><a href="/app/sales/settings/prospect-statuses" class=""><i class="nav-icon i-Approved-Window"></i> <span class="item-name">Prospect Status</span></a></li>
               </ul>
            </li>
         </ul>
         <ul data-parent="Accounting" class="childNav d-none">
            <li class="nav-item"><a href="/accounts-receivable" class=""><i class="nav-icon i-Add-Cart"></i> <span class="item-name">Accounts Receivable</span></a></li>
            <li class="nav-item"><a href="/accounts-payables" class=""><i class="nav-icon i-Bag-Coins"></i> <span class="item-name">Accounts Payable</span></a></li>
            <li class="nav-item"><a href="/app/accounting/assets" class=""><i class="nav-icon i-Building"></i> <span class="item-name">Assets Management</span></a></li>
            <li class="nav-item"><a href="{{ route('fund-transfers.index') }}" class=""><i class="nav-icon i-Letter-Sent"></i> <span class="item-name">Fund Transfer</span></a></li>
            <li class="nav-item"><a href="/app/accounting/liquidations" class=""><i class="nav-icon i-Maximize-Window"></i> <span class="item-name">Liquidate</span></a></li>
         </ul>
         <ul data-parent="People" class="childNav d-none">
            <li class="nav-item"><a href="/users" class=""><i class="nav-icon i-Engineering"></i> <span class="item-name">Users/Employees</span></a></li>
            <li class="nav-item"><a href="{{ route('customers.index') }}" class=""><i class="nav-icon i-Business-ManWoman"></i> <span class="item-name">Customers</span></a></li>
            <li class="nav-item"><a href="{{ route('suppliers.index') }}" class=""><i class="nav-icon i-Business-Mens"></i> <span class="item-name">Suppliers</span></a></li>
         </ul>
         <ul data-parent="settings" class="childNav d-none">
            <ul class="submenu" style="text-align: left; padding-left: 0;">
               <li class="nav-item">
                  <a href="{{ route('settings.create') }}" class="">
                     <i class="nav-icon i-Library"></i>
                     <span class="item-name">System Settings</span>
                  </a>
               </li>
               <li class="nav-item"><a href="{{ route('branches.index') }}" class=""><i class="nav-icon i-Location-2"></i> <span class="item-name">Branches</span></a></li>
               
               <li class="nav-item"><a href="/permission" class=""><i class="nav-icon i-Key"></i> <span class="item-name">Permission</span></a></li>
               <li class="nav-item"><a href="/settings/table-layouts" class=""><i class="fas fa-layer-group"></i> <span class="item-name">Table Layouts Settings</span></a></li>
               
               <li class="nav-item dropdown-sidemenu">
                  <a href="#"><i class="nav-icon i-Gear"></i> <span class="item-name">Kitchen Display Settings</span> <i class="dd-arrow i-Arrow-Down"></i></a> 
                  <ul class="submenu">
                     <li class="nav-item">
                           <a href="settings/stations">
                              <i class="nav-icon i-Bar-Chart"></i>
                              <span class="item-name">Station</span>
                           </a>
                        </a>
                     </li>
                  </ul>
               </li>
               <li class="nav-item"><a href="/app/settings/general/accounting" class=""><i class="nav-icon i-Data-Backup"></i> <span class="item-name">Back-Up Database</span></a></li>
            </ul>
            <li class="nav-item dropdown-sidemenu">
               <a href="#"><i class="nav-icon i-Gear"></i> <span class="item-name">General Settings</span> <i class="dd-arrow i-Arrow-Down"></i></a> 
               <ul class="submenu">
                   <li class="nav-item dropdown-sidemenu">
                     <a href="#">
                        <i class="nav-icon i-Library"></i>
                        <span class="item-name">Workforce</span>
                        <i class="dd-arrow i-Arrow-Down"></i>
                     </a>
                     <ul class="submenu">
                        <li class="nav-item">
                           <a href="{{ route('departments.index') }}">
                              <i class="nav-icon i-Bar-Chart"></i>
                              <span class="item-name">Departments</span>
                           </a>
                        </li>
                        <li class="nav-item">
                           <a href="{{ route('designations.index') }}">
                              <i class="nav-icon i-Road-2"></i>
                              <span class="item-name">Designations</span>
                           </a>
                        </li>
                        <li class="nav-item">
                           <a href="{{ route('statuses.index') }}">
                              <i class="nav-icon i-ID-Card"></i>
                              <span class="item-name">Status</span>
                           </a>
                        </li>
                         <li class="nav-item">
                           <a href="{{ route('shifts.index') }}">
                              <i class="nav-icon i-Chef"></i>
                              <span class="item-name">Shifts</span>
                           </a>
                        </li>
                        <li class="nav-item">
                           <a href="{{ route('leaves.index') }}">
                              <i class="nav-icon i-Ticket"></i>
                              <span class="item-name">Leaves</span>
                           </a>
                        </li>
                        <li class="nav-item">
                           <a href="{{ route('night-differentials.index') }}">
                              <i class="nav-icon i-Over-Time"></i>
                              <span class="item-name">Night Differentials</span>
                           </a>
                        </li>
                        <li class="nav-item">
                           <a href="{{ route('holidays.index') }}">
                              <i class="nav-icon i-Calendar-2"></i>
                              <span class="item-name">Holidays</span>
                           </a>
                        </li>
                        <li class="nav-item">
                           <a href="{{ route('benefits.index') }}">
                              <i class="nav-icon i-Betvibes"></i>
                              <span class="item-name">Benefits</span>
                           </a>
                        </li>
                        <li class="nav-item">
                           <a href="{{ route('allowances.index') }}">
                              <i class="nav-icon i-Money-2"></i>
                              <span class="item-name">Allowances</span>
                           </a>
                        </li>
                     </ul>
                  </li>

                  <li class="nav-item dropdown-sidemenu">
                     <a href="#">
                        <i class="nav-icon i-Building"></i>
                        <span class="item-name">Inventory</span>
                        <i class="dd-arrow i-Arrow-Down"></i>
                     </a>
                     <ul class="submenu">
                        <li class="nav-item">
                           <a href="{{ route('categories.index') }}">
                              <i class="nav-icon i-ID-Card"></i>
                              <span class="item-name">Category</span>
                           </a>
                        </li>
                        <li class="nav-item">
                           <a href="{{ route('units.index') }}">
                              <i class="nav-icon i-Road-2"></i>
                              <span class="item-name">Unit</span>
                           </a>
                        </li>
                     </ul>
                  </li>

                  <li class="nav-item dropdown-sidemenu">
                     <a href="#">
                        <i class="nav-icon i-Full-Basket"></i>
                        <span class="item-name">Sales</span>
                        <i class="dd-arrow i-Arrow-Down"></i>
                     </a>
                     <ul class="submenu">
                        <li class="nav-item">
                           <a href="{{ route('payments.index') }}">
                              <i class="nav-icon i-Wallet"></i>
                              <span class="item-name">Payment Methods</span>
                           </a>
                        </li>
                        <li class="nav-item">
                           <a href="{{ route('discounts.index') }}">
                              <i class="nav-icon i-Coins"></i>
                              <span class="item-name">Discounts</span>
                           </a>
                        </li>
                     </ul>
                  </li>
                  <li class="nav-item dropdown-sidemenu">
                     <a href="#">
                        <i class="nav-icon i-Edit-Map"></i>
                        <span class="item-name">Accounting</span>
                        <i class="dd-arrow i-Arrow-Down"></i>
                     </a>
                     <ul class="submenu">
                        <li class="nav-item">
                           <a href="{{ route('cash_equivalents.index') }}">
                              <i class="nav-icon i-Wallet"></i>
                              <span class="item-name">Cash Equivalents</span>
                           </a>
                        </li>
                     </ul>
                     <ul class="submenu">
                        <li class="nav-item">
                           <a href="{{ route('accounting-categories.index') }}">
                              <i class="nav-icon i-Add-Cart"></i>
                              <span class="item-name">Accounting Category</span>
                           </a>
                        </li>
                     </ul>
                     <ul class="submenu">
                        <li class="nav-item">
                           <a href="{{ route('chart-of-accounts.index') }}">
                              <i class="fa-solid fa-diagram-project"></i>
                              <span class="item-name">Chart of Account</span>
                           </a>
                        </li>
                     </ul>
                     <ul class="submenu">
                        <li class="nav-item">
                           <a href="{{ route('asset-categories.index') }}">
                              <i class="nav-icon i-Network-Window"></i>
                              <span class="item-name">Assets Category</span>
                           </a>
                        </li>
                     </ul>
                     <ul class="submenu">
                        <li class="nav-item">
                           <a href="{{ route('taxes.index') }}">
                              <i class="nav-icon i-Network-Window"></i>
                              <span class="item-name">Taxes</span>
                           </a>
                        </li>
                     </ul>
                  </li>
               </ul>
            </li>
         </ul>
      </div>
      <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
         <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
      </div>
      <div class="ps__rail-y" style="top: 0px; right: 0px; height: 830px;">
         <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 806px;"></div>
      </div>
   </section>
   <div class="sidebar-overlay"></div>
   @include('layouts.start_end_day_modal')
   @include('layouts.checkUnpaidOrders_modal')
</div>


<script>
  document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector(".sidebar-left");
    if (sidebar) {
      new PerfectScrollbar(sidebar, {
        wheelPropagation: true,   // lets the mouse wheel work properly
        suppressScrollX: true     // disable horizontal scrollbar
      });
    }
  });
  
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const toggleBtn = document.querySelector(".menu-toggle");
  const sidebar = document.getElementById("sidebar");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("active");
    });
  }
  const modal = document.getElementById('startPOSModal');
  if (!modal) return;

  const unpaidModal = document.getElementById('checkUnpaidModal');
  if (!unpaidModal) return;

  const body = modal.querySelector('.modal-body');

  const unpaidModalBody = unpaidModal.querySelector('.modal-body');

  // If mouse wheel isn't scrolling, forward wheel events to the modal body.
  modal.addEventListener('wheel', function(e) {
    if (!body) return;

    const maxScrollTop = body.scrollHeight - body.clientHeight;
    const current = body.scrollTop;
    const delta = e.deltaY;

    // Determine if the body can scroll in the requested direction
    const scrollingDown = delta > 0;
    const canScrollDown = current < maxScrollTop;
    const canScrollUp = current > 0;

    // If the body can scroll in the wheel direction, scroll it and prevent the event bubbling to parent
    if ((scrollingDown && canScrollDown) || (!scrollingDown && canScrollUp)) {
      body.scrollTop = Math.min(Math.max(0, current + delta), maxScrollTop);
      e.preventDefault(); // stop outer scrolling
    }
    // else allow it to bubble (optional) so page/backdrop could handle it
  }, { passive: false }); // must be non-passive to call preventDefault()

});

</script>

<script>
const now = new Date();
window.userName = "{{ auth()->user()->name }}";

new Vue({
  el: "#startEndApp",
  data: {
    modalMode: 'open',
    endStep: 'confirm',
    unpaidStatus: '',
    sessionData: null,
    terminal_no: '',
    startingFund: '',
    tip: '',
    transferTo: '',
    transferAmount: '',
    remarks: '',
    closingDateTime: '',
    manualTimeEdit: false,
    loginDate: '', 
    loginTime: '',
    isProcessing: false,

    // Denominations
    denom_1000: '', denom_500: '', denom_200: '', denom_100: '',
    denom_50: '',   denom_20: '',  denom_10: '', denom_5: '',
    denom_1: '',    denom_050: '', denom_025: '', denom_010: '', denom_005: '',

    allPayments: [],
    manualBreakdown: {},

    hasStartedPOS: localStorage.getItem('hasStartedPOS') === '1' ? 1 : 0,
  },

  mounted() {
    this.setInitialDateTime();
    this.detectTerminalName();
    this.startAutoTimeUpdate();
    this.fetchAllPayments();
    this.setNow();
    setInterval(() => {
      if (!this.manualTimeEdit) this.setNow();
    }, 30000);

    // Attach listeners
    document.querySelector('.startEndTrigger')?.addEventListener('click', (e) => {
      e.preventDefault();
      this.promptStartEndModal();
    });

    document.querySelector('.posLink')?.addEventListener('click', (e) => {
      e.preventDefault();
      this.handlePOSNavigation('/orders');
    });

    if (window.location.pathname === '/orders') {
      this.handlePOSNavigation('/orders', true);
    }

    // Attach listeners for checkUnpaidTrigger
    document.querySelector('.checkUnpaidTrigger')?.addEventListener('click', (e) => {
      e.preventDefault();
      this.promptcheckUnpaidModal();
    });
  },

  computed: {
    transaction_datetime() {
      return this.closingDateTime ? this.closingDateTime.replace('T', ' ') + ':00' : null;
    },
    denominationTotal() {
      const total =
        (parseInt(this.denom_1000) || 0) * 1000 +
        (parseInt(this.denom_500)  || 0) * 500 +
        (parseInt(this.denom_200)  || 0) * 200 +
        (parseInt(this.denom_100)  || 0) * 100 +
        (parseInt(this.denom_50)   || 0) * 50 +
        (parseInt(this.denom_20)   || 0) * 20 +
        (parseInt(this.denom_10)   || 0) * 10 +
        (parseInt(this.denom_5)    || 0) * 5 +
        (parseInt(this.denom_1)    || 0) * 1 +
        (parseInt(this.denom_050)  || 0) * 0.5 +
        (parseInt(this.denom_025)  || 0) * 0.25 +
        (parseInt(this.denom_010)  || 0) * 0.1 +
        (parseInt(this.denom_005)  || 0) * 0.05;
      return parseFloat(total.toFixed(2));
    },
    paymentBreakdown() {
      const breakdown = {};
      this.allPayments.forEach(p => {
        const key = this.slugify(p.payment_name);
        breakdown[key] = parseFloat(p.total_amount || 0);
      });
      Object.assign(breakdown, this.manualBreakdown);
      return breakdown;
    },
    cashSales() {
    const cashItem = this.allPayments.find(p =>
      /\bcash\b/i.test(p.payment_name)
      );
    const amount = cashItem ? parseFloat(cashItem.total_amount) || 0 : 0;

    // FIX: Round to 2 decimals to kill floating-point bugs
    const cleanAmount = Math.round(amount * 100) / 100;

   //  console.log('%cCash Sales (clean):', 'color: lime; font-weight: bold;', cleanAmount);
    return cleanAmount;
  },

  // 2. EXPECTED CASH IN DRAWER — now 100% accurate
  expectedCashInDrawer() {
    const starting = parseFloat(this.startingFund) || 0;
    const expected = starting + this.cashSales;

    // FINAL FIX: Round to 2 decimals
    const clean = Math.round(expected * 100) / 100;

   //  console.log('%cExpected Cash in Drawer:', 'color: gold; font-weight: bold;', clean);
    return clean;
  },

  // 3. SHORTAGE & OVERAGE — now perfect
  shortage() {
    const diff = this.denominationTotal - this.expectedCashInDrawer;
    return diff < -0.01 ? Math.abs(diff).toFixed(2) : '0.00'; // tolerance 1 cent
  },

  overage() {
    const diff = this.denominationTotal - this.expectedCashInDrawer;
    return diff > 0.01 ? diff.toFixed(2) : '0.00'; // tolerance 1 cent
  }
},

  methods: {
    slugify(text) {
      return text.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/(^_|_$)/g, '');
    },

    setNow() {
      const now = new Date();
      const offset = now.getTimezoneOffset() * 60000;
      const local = new Date(now.getTime() - offset);
      this.closingDateTime = local.toISOString().slice(0, 16);
    },

    detectTerminalName() {
      const platform = /win/i.test(navigator.userAgent) ? 'Windows' :
                       /mac/i.test(navigator.userAgent) ? 'MacOS' :
                       /linux/i.test(navigator.userAgent) ? 'Linux' :
                       /android/i.test(navigator.userAgent) ? 'Android' : 'iOS';
      this.terminal_no = `${platform}_${window.userName || 'User'}`;
    },

    setInitialDateTime() {
      const now = new Date();
      this.closingDateTime = now.toISOString().slice(0, 16);
    },

    startAutoTimeUpdate() {
      setInterval(() => {
        if (!this.manualTimeEdit) this.setNow();
      }, 1000);
    },

    setLoginDateTime(datetime) {
    if (!datetime) {
      const now = new Date();
      this.loginDate = now.toISOString().split('T')[0];
      this.loginTime = now.toTimeString().slice(0, 5);
      // console.log('No datetime → using current time:', this.loginDate, this.loginTime);
      return;
    }

    let datePart, timePart;

    // Case 1: ISO format → "2025-12-05T03:08:46.000000Z"
    if (datetime.includes('T')) {
      const [date, time] = datetime.split('T');
      datePart = date;                                      // "2025-12-05"
      timePart = time.split('.')[0].slice(0, 8);            // "03:08:46" → "03:08"
      
      // Optional: Convert UTC to local time (recommended!)
      const local = new Date(datetime);
      datePart = local.toISOString().split('T')[0];
      timePart = local.toTimeString().slice(0, 5);          // "10:59" in your timezone
    } 
    // Case 2: Old format → "2025-12-05 10:59:46"
    else if (datetime.includes(' ')) {
      const [date, time] = datetime.split(' ');
      datePart = date;
      timePart = time.slice(0, 5);
    } 
    else {
      console.warn('Invalid datetime format:', datetime);
      datePart = new Date().toISOString().split('T')[0];
      timePart = '00:00';
    }

    this.loginDate = datePart;
    this.loginTime = timePart;

  },

    // MAIN LOGIC: Check session and decide what to show
    async checkExistingSession() {
      try {
        const { data } = await axios.get('/pos/session/check', {
          params: { terminal_no: this.terminal_no }
        });

        // FIRST: Extract and set login date/time
        this.setLoginDateTime(data.transaction_datetime || null);

        // 1. Already open on THIS terminal → resume
        if (data.has_open_session && data.on_this_terminal) {
          this.hasStartedPOS = 1;
          this.sessionData = data.session;
          this.startingFund = data.session.starting_fund || 0;
          localStorage.setItem('hasStartedPOS', '1');
          this.modalMode = 'close';
          return;
        }

        // 2. Conflict: open session on different terminal → BLOCK + warn
        if (data.conflict) {
          this.hasStartedPOS = 0;
          localStorage.removeItem('hasStartedPOS');
          this.modalMode = 'open';

          await Swal.fire({
            icon: 'warning',
            title: 'Cannot Start Session',
            html: `
              <p>You already have an <strong>open session</strong> on:</p>
              <h4 class="text-danger fw-bold mb-3">${data.old_terminal}</h4>
              <p>Please <strong>close the session</strong> on that terminal first.</p>
            `,
            confirmButtonText: 'Understood',
            allowOutsideClick: false,
            allowEscapeKey: false,
          });
          return;
        }

        // 3. All clear → allow start
        this.hasStartedPOS = 0;
        localStorage.removeItem('hasStartedPOS');
        this.modalMode = 'open';
        this.startingFund = ''; // reset

      } catch (err) {
        console.error('Session check failed:', err);
        this.modalMode = 'open';
      }
    },

    promptStartEndModal() {
      this.checkExistingSession().then(() => {
        const modal = new bootstrap.Modal(document.getElementById('startPOSModal'));
        modal.show();
      });
    },
      async promptcheckUnpaidModal() {

           const result = await this.checkUnpaidOrders();

           if (result.has_unpaid_orders || result.has_unserved_products) {

            this.unpaidStatus = result;

         const modal = new bootstrap.Modal(document.getElementById('checkUnpaidModal'));
         modal.show();
           } else {
            window.location.href = '/pos-clossing';
           }
      },

    async handlePOSNavigation(url, auto = false) {
      await this.checkExistingSession();
      if (this.hasStartedPOS) {
        if (!auto) window.location.href = url;
      } else {
        const modal = new bootstrap.Modal(document.getElementById('startPOSModal'));
        modal.show();
      }
    },
    async handleConfirmEndDay() {
//       const unpaidOrders = await this.checkUnpaidOrders();
//       if (unpaidOrders) {
//         this.endStep = 'unpaid';
//       } else {
        this.endStep = 'form';
//       }

// THIS IS THE MISSING LINE!!!
   //  console.log('%c[END DAY] No unpaid orders → Loading payment breakdown now!', 'color: lime; font-size: 16px; background: black;');
    await this.fetchAllPayments();
    },
    handleUnpaidOk() {
    // Close the modal
    const modalEl = document.getElementById('startPOSModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    modalInstance.hide();
    // Reset for next open
    this.endStep = 'confirm';
  },
async checkUnpaidOrders() {
    try {
        const res = await axios.get('/check-unpaid-orders');
        return res.data;
    } catch (error) {
        console.error('Error checking unpaid orders:', error);
        return {
            has_unpaid_orders: false,
            has_unserved_products: false
        };
    }
},

async fetchAllPayments() {

  try {
    const response = await axios.get('/get-all-payments');
    const payments = response.data.order?.totals_by_payment || [];

    if (payments.length === 0) {
      console.warn('%c[POS] No payments found for this session yet.', 'color: #FF5722;');
      this.allPayments = [];
      return;
    }

    // Map and clean the data
    this.allPayments = payments.map(p => {
      const cleaned = {
        payment_name: p.payment_name || 'Unknown',
        total_amount: parseFloat(p.total_amount || 0)
      };
      return cleaned;
    });

  } catch (err) {
    console.error('%c[POS] Failed to fetch payments!', 'color: #F44336; font-weight: bold;', err);
    console.error('Error response:', err.response?.data);
    console.error('Status:', err.response?.status);
    
    this.allPayments = [];
   //  console.log(this.allPayments)
  }
},

    // SUCCESS + ERROR with SweetAlert2
    async submitStartPOS() {
      try {
      // Close modal immediately
      const modalElement = document.getElementById('startPOSModal');
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      if (modalInstance) modalInstance.hide();

        await axios.post('/pos/session/open', {
          terminal_no: this.terminal_no,
          cash_fund: this.startingFund,
          transaction_datetime: new Date().toISOString().slice(0, 19).replace('T', ' '),
        });

        await Swal.fire({
          icon: 'success',
          title: 'POS Session Started!',
          text: `Fund: ₱${this.startingFund.toLocaleString()}`,
          timer: 1500,
          showConfirmButton: false,
          allowOutsideClick: false
        });

        this.hasStartedPOS = 1;
        localStorage.setItem('hasStartedPOS', '1');
      //   this.modalMode = 'close';

        window.location.href = '/orders';

      } catch (err) {
        const msg = err.response?.data?.message || 'Failed to start session.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: msg,
          confirmButtonText: 'OK'
        });
      }
    },

    validateBeforeSubmit() {
        const denom = Number(this.denominationTotal) || 0;
        const transfer = Number(this.transferAmount) || 0;
        const shortage = Number(this.shortage) || 0;
        const overage = Number(this.overage) || 0;
        const remarks = this.remarks ? this.remarks.trim() : '';

        if (denom !== transfer) {
            Swal.fire({
                icon: 'error',
                title: 'Mismatch Detected',
                text: `Denomination total must match the transfer amount`,
            });
            return false;
        }

        if ((shortage !== 0 || overage !== 0) && remarks === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Remarks Required',
                text: 'Please enter remarks when there is a shortage or overage.',
            });
            return false;
        }

        return true;
    },

   async submitEndPOS() {
   // 🔹 VALIDATION: Require transfer_to + amount
   if (!this.transferTo) {
      return Swal.fire({
         icon: 'warning',
         title: 'Missing Transfer Account',
         text: 'Please select a "Transfer To" account.',
      });
   }

   if (!this.transferAmount || parseFloat(this.transferAmount) <= 0) {
      return Swal.fire({
         icon: 'warning',
         title: 'Invalid Amount',
         text: 'Please enter a valid transfer amount.',
      });
   }

   if (!this.validateBeforeSubmit()) return;

   // 🔰 Confirmation dialog
   const confirmResult = await Swal.fire({
      title: 'Are you sure?',
      text: 'Do you really want to close this session?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, close it',
      cancelButtonText: 'Cancel'
   });

   if (!confirmResult.isConfirmed) return;

   
    // 1. Immediately hide + disable the modal to prevent any flash
    const modalElement = document.getElementById('startPOSModal');
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
      modalInstance.hide();
    }

    modalElement.style.pointerEvents = 'none';
    modalElement.querySelector('.modal-content').style.opacity = '0.6';

   const payload = {
      terminal_no: this.terminal_no,
      transaction_datetime: this.transaction_datetime,
      starting_fund: parseFloat(this.startingFund) || 0,
      tip: parseFloat(this.tip) || 0,
      transfer_to: this.transferTo || null,
      transfer_amount: parseFloat(this.transferAmount) || 0,
      remarks: this.remarks,

      manual_breakdown: Object.keys(this.manualBreakdown || {}).length > 0 
      ? this.manualBreakdown 
      : null,

      d_1000: parseInt(this.denom_1000) || null,
      d_500:  parseInt(this.denom_500)  || null,
      d_200:  parseInt(this.denom_200)  || null,
      d_100:  parseInt(this.denom_100)  || null,
      d_50:   parseInt(this.denom_50)   || null,
      d_20:   parseInt(this.denom_20)   || null,
      d_10:   parseInt(this.denom_10)   || null,
      d_5:    parseInt(this.denom_5)    || null,
      d_1:    parseInt(this.denom_1)    || null,
      d_050:  parseInt(this.denom_050)  || null,
      d_025:  parseInt(this.denom_025)  || null,
      d_010:  parseInt(this.denom_010)  || null,
      d_005:  parseInt(this.denom_005)  || null,
   };

   console.log('%c[END DAY] Closing payload:', 'color: cyan; font-weight: bold;', payload);

   try {
      // 🔰 Loading state
      Swal.fire({
         title: 'Closing session...',
         text: 'Please wait',
         allowOutsideClick: false,
         didOpen: () => {
         Swal.showLoading();
         }
      });

      const res = await axios.post('/pos/session/close', payload);
      console.log('%c[END DAY] Close response:', 'color: lime; font-weight: bold;', res.data);

      // 🔰 Success alert
      if (res.data.success) {
         await Swal.fire({
         icon: 'success',
         title: 'Session Closed',
         text: 'Session closed successfully!',
         timer: 2000,
         timerProgressBar: true,
         showConfirmButton: false,
         allowOutsideClick: false,
         allowEscapeKey: false
         });

         // Update UI
         this.hasStartedPOS = 0;
         localStorage.removeItem('hasStartedPOS');
         // this.modalMode = 'open';
         // bootstrap.Modal.getInstance(document.getElementById('endOfDayModal'))?.hide();
         window.location.href = '/orders';
      }

   } catch (err) {
      // Re-enable modal on error
      const modalElement = document.getElementById('startPOSModal');
      modalElement.style.pointerEvents = '';
      modalElement.querySelector('.modal-content').style.opacity = '';

      const msg = err.response?.data?.message || 'Failed to end session.';
      Swal.fire('Error', msg, 'error');
   }
}
  },

  watch: {
    closingDateTime() {
      this.manualTimeEdit = true;
    }
  }
});
</script>
