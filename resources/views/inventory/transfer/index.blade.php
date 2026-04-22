@extends('layouts.app')
@section('content')
<div class="main-content" id="app">
   <div>
      <div class="breadcrumb">
         <h1 class="mr-3">
            Invetory Transfer
         </h1>
         <ul>
            <li><a href="/accounts-receivable">Invetory</a></li>
         </ul>
      </div>
      <div class="separator-breadcrumb border-top"></div>
   </div>
   <div class="wrapper">
      <div class="card-body">
         <!-- Status Tabs -->
         <nav class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
               <li class="nav-item" v-for="status in statusList" :key="status.value">
                  <a href="#"
                     class="nav-link"
                     :class="{ active: statusFilter === status.value }"
                     @click.prevent="setStatus(status.value)">
                  @{{ status.label }}
                  </a>
               </li>
            </ul>
         </nav>
         <div class="card-body">
            <div class="vgt-wrap">
               <div class="vgt-inner-wrap">
                  <div class="vgt-global-search vgt-clearfix">
                     <div class="vgt-global-search__input vgt-pull-left">
                        <form role="search">
                           <label for="vgt-search">
                              <span aria-hidden="true" class="input_icon">
                                 <div class="magnifying-glass">
                                 </div>
                              </span>
                              <span class="sr-only">Search:</span>
                           </label>
                           <input id="vgt-search" type="text" placeholder="Search this table" class="vgt-input vgt-pull-left">
                        </form>
                     </div>
                     <div class="vgt-global-search__actions vgt-pull-right">
                        <div class="mt-2 mb-3">
                           <button type="button" class="btn btn-outline-info ripple m-1 btn-sm collapsed" aria-expanded="false" aria-controls="sidebar-right" style="overflow-anchor: none;"><i class="i-Filter-2"></i>
                           Filter
                           </button> <button type="button" class="btn btn-outline-success ripple m-1 btn-sm"><i class="i-File-Copy"></i> PDF
                           </button> <button class="btn btn-sm btn-outline-danger ripple m-1"><i class="i-File-Excel"></i> EXCEL
                           </button>
                           <button class="btn btn-primary btn-rounded btn-icon m-1"
                              onclick="window.location='{{ route('transfers.create', ['transfer_type' => 'send']) }}'">
                           <i class="i-Add"></i> Send Out
                           </button>
                           <button class="btn btn-primary btn-rounded btn-icon m-1"
                              onclick="window.location='{{ route('transfers.create', ['transfer_type' => 'request']) }}'">
                           <i class="i-Add"></i> Request
                           </button>
                        </div>
                     </div>
                  </div>
                  <div class="vgt-fixed-header">
                  </div>
                  <div class="vgt-responsive">
                     <table id="vgt-table"  class="table-hover tableOne vgt-table">
                        <colgroup>
                           <col id="col-0">
                           <col id="col-1">
                           <col id="col-2">
                           <col id="col-3">
                           <col id="col-4">
                           <col id="col-5">
                           <col id="col-6">
                        </colgroup>
                        <thead>
                           <tr>
                              <!----> 
                              <th scope="col" class="vgt-checkbox-col"><input type="checkbox"></th>
                              <th scope="col" aria-sort="descending" aria-controls="col-0" class="vgt-left-align text-left w-190px sortable" style="min-width: auto; width: auto;"><span>Date and Time of Request</span> <button><span class="sr-only">
                                 Sort table by Date and Time of Request in descending order
                                 </span></button>
                              </th>
                              <th
                                scope="col"
                                aria-sort="descending"
                                aria-controls="col-1"
                                class="vgt-left-align text-left w-220px sortable"
                                >
                                <span>@{{ dynamicHeaderLabel }}</span>
                                <button>
                                    <span class="sr-only">
                                        Sort table by @{{ dynamicHeaderLabel }} in descending order
                                    </span>
                                </button>
                              </th>
                              <th scope="col" aria-sort="descending" aria-controls="col-2" class="vgt-left-align text-left w-160px sortable" style="min-width: auto; width: auto;"><span>Reference #</span> <button><span class="sr-only">
                                 Sort table by Reference # in descending order
                                 </span></button>
                              </th>
                              <th scope="col" aria-sort="descending" aria-controls="col-3" class="vgt-left-align text-left w-160px sortable" style="min-width: auto; width: auto;"><span>Source</span> <button><span class="sr-only">
                                 Sort table by Source in descending order
                                 </span></button>
                              </th>
                              <th scope="col" aria-sort="descending" aria-controls="col-3" class="vgt-left-align text-left w-160px sortable" style="min-width: auto; width: auto;"><span>Destination</span> <button><span class="sr-only">
                                 Sort table by Destination in descending order
                                 </span></button>
                              </th>
                              <th scope="col" aria-sort="descending" aria-controls="col-3" class="vgt-left-align text-left w-160px sortable" style="min-width: auto; width: auto;"><span>Status</span> <button><span class="sr-only">
                                 Sort table by Status in descending order
                                 </span></button>
                              </th>
                              <!----><!----><!----><!----><!----><!----><!----><!---->
                              <th scope="col" aria-sort="descending" aria-controls="col-23" class="vgt-left-align text-right" style="min-width: auto; width: auto;">
                                 <span>Action</span> <!---->
                              </th>
                           </tr>
                           <!---->
                        </thead>
                        <tbody>
                           <tr v-for="row in records" :key="row.id">
                              <td class="vgt-checkbox-col">
                                 <input type="checkbox" :value="row.id">
                              </td>
                              <td class="vgt-left-align text-left w-190px"> @{{ row.requested_datetime }}</td>
                              <td class="vgt-left-align text-left w-220px">@{{ row[dynamicRowField] || '—' }}</td>
                              <td class="vgt-left-align text-left w-160px">@{{ row.reference_no }}</td>
                              <td class="vgt-left-align text-left w-160px">@{{ row.source_branch }}</td>
                              <td class="vgt-left-align text-left w-160px">@{{ row.destination_branch }}</td>
                              <td class="vgt-left-align text-left w-160px">
                                 @{{ displayStatus(row) }}
                              </td>
                              <td class="vgt-left-align text-right">
                                 <actions-dropdown :row="row"
                                 @view-invoice="viewInvoice"
                                 @edit-transfer="editTransfer"
                                 @add-view-attached-files="addViewAttachedFiles"
                                 @approve-transfer="approveTransfer"
                                 @disapprove-transfer="disapproveTransfer"
                                 @archive-transfer="archiveTransfer"
                                 @restore-transfer="restoreTransfer"
                                 @delete-transfer="deleteTransfer"
                                 @open-receive-modal="openReceiveModal"
                                 ></actions-dropdown>
                              </td>
                           </tr>
                           <tr v-if="records.length === 0">
                              <td colspan="8" class="text-center text-muted">No data available.</td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <div class="vgt-wrap__footer vgt-clearfix">
                     <!-- Rows per page -->
                     <div class="footer__row-count vgt-pull-left">
                        <form>
                           <label class="footer__row-count__label">Rows per page:</label>
                           <select v-model.number="pagination.per_page" @change="fetchRecords(1)" class="footer__row-count__select">
                              <option value="10">10</option>
                              <option value="20">20</option>
                              <option value="50">50</option>
                              <option value="100">100</option>
                           </select>
                        </form>
                     </div>
                     <!-- Showing X to Y of Z -->
                     <div class="footer__navigation vgt-pull-right">
                        <div class="footer__navigation__page-info">
                           <div v-if="pagination.total > 0">
                              Showing @{{ pagination.from }} to @{{ pagination.to }} of @{{ pagination.total }} entries
                           </div>
                           <div v-else class="text-muted">
                              No entries found
                           </div>
                        </div>
                        <!-- Prev / Next Buttons -->
                        <button type="button"
                           class="footer__navigation__page-btn"
                           :class="{ disabled: pagination.current_page <= 1 }"
                           :disabled="pagination.current_page <= 1"
                           @click="fetchRecords(pagination.current_page - 1)">
                        <span class="chevron left"></span> prev
                        </button>
                        <button type="button"
                           class="footer__navigation__page-btn"
                           :class="{ disabled: pagination.current_page >= pagination.last_page }"
                           :disabled="pagination.current_page >= pagination.last_page"
                           @click="fetchRecords(pagination.current_page + 1)">
                        next <span class="chevron right"></span>
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Receive Delivery Modal (inside #app so Vue bindings work) -->
   <div class="modal fade" id="receiveDeliveryModal" tabindex="-1" role="dialog" aria-labelledby="receiveDeliveryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="receiveDeliveryModalLabel">Receive Delivery</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <div v-if="loadingSendOuts" class="text-center py-4">
                  <span>Loading deliveries...</span>
               </div>
               <div v-else-if="pendingSendOuts.length === 0" class="text-center text-muted py-4">
                  No pending deliveries to receive.
               </div>
               <div v-else>
                  <p class="text-muted mb-3">Select a delivery to accept and add stocks to your inventory.</p>
                  <div v-for="so in pendingSendOuts" :key="so.id" class="card mb-3">
                     <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                           <strong>DR #:</strong> @{{ so.delivery_request_no }}<br>
                           <small class="text-muted">Personnel: @{{ so.personel_name }} &nbsp;|&nbsp; Sent: @{{ so.created_at }}</small>
                        </div>
                        <button
                           class="btn btn-success btn-sm"
                           :disabled="receivingId === so.id"
                           @click="acceptDelivery(so.id)"
                        >
                           <span v-if="receivingId === so.id">Receiving...</span>
                           <span v-else><i class="i-Yes mr-1"></i> Accept</span>
                        </button>
                     </div>
                     <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                           <thead class="thead-light">
                              <tr>
                                 <th>Name</th>
                                 <th>Code</th>
                                 <th>Type</th>
                                 <th class="text-right">Quantity</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="(item, idx) in so.items" :key="idx">
                                 <td>@{{ item.name }}</td>
                                 <td>@{{ item.code }}</td>
                                 <td>@{{ item.type }}</td>
                                 <td class="text-right">@{{ item.quantity }}</td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>
</div>

<script type="text/x-template" id="actions-dropdown-template">
   <div class="dropdown btn-group" ref="dropdown">
       <button type="button" class="btn dropdown-toggle btn-link btn-lg text-decoration-none dropdown-toggle-no-caret"
               @click.stop="toggleDropdown">
           <span class="_dot _r_block-dot bg-dark"></span>
           <span class="_dot _r_block-dot bg-dark"></span>
           <span class="_dot _r_block-dot bg-dark"></span>
       </button>
   
       <ul :class="['dropdown-menu dropdown-menu-right', { show: isOpen }]">
   
           <!-- 1. View Invoice -->
           <li>
               <a class="dropdown-item" href="#" @click.prevent="$emit('view-invoice', row.id)">
                   <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                   View Invoice
               </a>
           </li>
   
           <!-- 1.5 View Delivery Reciept -->
           <li v-if="['completed','archived'].includes(row.status)">
               <a class="dropdown-item" href="#" @click.prevent="$emit('view-delivery-reciept', row.id)">
                   <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                   View Delivery Reciept
               </a>
           </li>
           <!-- 1.6 Send Out Stocks -->
           <li v-if="(row.status) == 'approved' && row.can_send_stocks">
               <a class="dropdown-item" :href="`/inventory/transfer/${row.id}/send-out`">
                   <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                   Send Out Stocks
               </a>
           </li>
   
           <!-- 1.7 Send Out Additional Stocks -->
           <li v-if="(row.status) == 'in_transit' && row.can_send_additional_stocks">
               <a class="dropdown-item" :href="`/inventory/transfer/${row.id}/send-out`">
                   <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                   Send Out Additional Stocks
               </a>
           </li>
   
            <!-- 4. Edit Receivable – Only for pending & approved -->
           <li v-if="['pending'].includes(row.status)">
               <a class="dropdown-item" :href="`/inventory/transfer/${row.id}/edit`">
                   <i class="nav-icon i-Edit font-weight-bold mr-2"></i>
                   Edit
               </a>
           </li>
   
           <!-- Approve – Only if pending AND allowed -->
           <li v-if="row.status === 'pending' && row.can_approve">
               <a class="dropdown-item" href="#"
               @click.prevent="changeStatus(row.id, 'approved')">
                   <i class="nav-icon i-Like font-weight-bold mr-2"></i>
                   Approve
               </a>
           </li>
   
           <li v-if="row.status === 'pending' && !row.can_approve">
               <a class="dropdown-item text-muted disabled" href="#">
                   <i class="nav-icon i-Lock mr-2"></i>
                   Approve (Not allowed)
               </a>
           </li>
   
   
   
           <!-- 3. Disapprove – Only for pending & approved -->
           <li v-if="['pending'].includes(row.status)">
               <a class="dropdown-item" href="#" @click.prevent="changeStatus(row.id, 'disapproved')">
                   <i class="nav-icon i-Unlike-2 font-weight-bold mr-2"></i>
                   Disapprove
               </a>
           </li>
           
           <!-- Add Stocks -->
           <li v-if="row.can_add_stocks && row.pending_send_outs_count > 0">
               <a
                   href="#"
                   class="dropdown-item"
                   @click.prevent="$emit('open-receive-modal', row)"
               >
                   <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                   Receive Delivery
               </a>
           </li>
   
   
           <!-- 3.1 Move to completed -->
           <li v-if="(row.status) == 'in_transit' && !row.is_partial">
               <a class="dropdown-item" href="#" @click.prevent="changeStatus(row.id, 'completed')">
                   <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                   Move to Completed
               </a>
           </li>
   
           <!-- 5. Add Attachment -->
           <li>
               <a class="dropdown-item" href="#" @click.prevent="$emit('add-attachment', row.id)">
                   <i class="nav-icon i-Add-File font-weight-bold mr-2"></i>
                   Add/View Attachmed File
               </a>
           </li>
   
           <!-- For Disapproved & Archived – Add Restore Option -->
           <li v-if="['disapproved', 'archived'].includes(row.status)">
               <a class="dropdown-item" href="#" @click.prevent="changeStatus(row.id, 'pending')">
                   <i class="nav-icon i-Restore-Window font-weight-bold mr-2"></i>
                   Restore to Pending
               </a>
           </li>
   
           <!-- 7. Edit Due Date – Only pending & approved -->
            <!--  <li v-if="['pending', 'approved'].includes(row.status)">
               <a class="dropdown-item" href="#" @click.prevent="$parent.openEditDueDateModal(row)">
                   <i class="nav-icon i-Calendar font-weight-bold mr-2"></i>
                   Edit Due Date
               </a>
           </li> -->
   
           <!-- 7.5 Receive Payment – Only approved -->
            <!-- <li v-if="['approved'].includes(row.status)">
               <a class="dropdown-item" href="#" 
               @click.prevent="$parent.openReceivePayment(row)">
                   <i class="nav-icon i-Money font-weight-bold mr-2"></i>
                   Receive Payment
               </a>
           </li> -->
   
   
           <!-- 8. Move to Archive – pending, approved, completed, disapproved -->
           <li v-if="['pending', 'approved', 'completed', 'disapproved'].includes(row.status)">
               <a class="dropdown-item" href="#" @click.prevent="changeStatus(row.id, 'archived')">
                   <i class="nav-icon i-Letter-Close font-weight-bold mr-2"></i>
                   Move to Archive
               </a>
           </li>
   
           <!-- ARCHIVED: Replace "Move to Archive" with these -->
           <template v-if="row.status === 'archived'">
               <li>
                   <a class="dropdown-item" href="#" @click.prevent="$emit('delete-permanently', row.id)">
                       <i class="nav-icon i-Close font-weight-bold mr-2"></i>
                       Permanently Delete
                   </a>
               </li>
           </template>
   
           <!-- 9. Logs -->
           <li>
               <a class="dropdown-item" href="#" @click.prevent="$emit('logs', row.id)">
                   <i class="nav-icon i-Computer-Secure font-weight-bold mr-2"></i>
                   Logs
               </a>
           </li>
   
           <!-- 10. Remarks -->
           <li>
               <a class="dropdown-item" href="#" @click.prevent="$emit('open-remarks', row.id)">
                   <i class="nav-icon i-Mail-Attachement font-weight-bold mr-2"></i>
                   Remarks
               </a>
           </li>
   
       </ul>
   </div>
</script>
<script>
   Vue.component("actions-dropdown", {
       template: "#actions-dropdown-template",
       props: {
           row: {
               type: Object,
               required: true
           }
       },
       data() {
           return {
               isOpen: false
           };
       },
       methods: {
           toggleDropdown() { this.isOpen = !this.isOpen; },
           handleClickOutside(event) {
               if (!this.$refs.dropdown?.contains(event.target)) this.isOpen = false;
           },
           changeStatus(id, newStatus) {
       Swal.fire({
           title: 'Are you sure?',
           text: `Do you want to change the status to "${newStatus}"?`,
           icon: 'question',
           showCancelButton: true,
           confirmButtonText: 'Yes, change it!',
           cancelButtonText: 'Cancel',
       }).then((result) => {
           if (result.isConfirmed) {
               axios.put(`{{ url('/inventory/transfer') }}/${id}/update-status`, {
                   status: newStatus
               })
               .then(response => {
                   const res = response.data;
   
                   let message = `Status updated successfully.`;
   
                   // If approved, show nicely formatted message
                   if (res.status === 'approved' && res.approved_by_name && res.approved_datetime) {
                       message = `
                           Status updated to APPROVED.
                           Approved by: ${res.approved_by_name}
                           At: ${res.approved_datetime}`;
                   }
   
                   Swal.fire({
                       icon: 'success',
                       title: 'Updated!',
                       html: message.replace(/\n/g, '<br>'), // preserve line breaks
                       showConfirmButton: true,
                   });
   
                   this.$emit('status-updated');
               })
               .catch(error => {
                   console.error("Error updating status:", error);
                   Swal.fire({
                       icon: 'error',
                       title: 'Failed!',
                       text: 'Failed to update status.'
                   });
               });
           }
       });
   },
       },
       mounted() {
           document.addEventListener("click", this.handleClickOutside);
       },
       beforeDestroy() {
           document.removeEventListener("click", this.handleClickOutside);
       }
   });
   
   new Vue({
       el: '#app',
   
       data() {
           return {
               records: [],
               pagination: {
                   current_page: 1,
                   per_page: 10,
                   total: 0,
                   from: 1,
                   to: 0,
                   last_page: 1,
               },
               statusFilter: 'pending',
               statusList: [
                   { label: 'Pending', value: 'pending' },
                   { label: 'Approved', value: 'approved' },
                   { label: 'In-Transit', value: 'in_transit' },
                   { label: 'Completed', value: 'completed' },
                   { label: 'Disapproved', value: 'disapproved' },
                   { label: 'Archived', value: 'archived' },
               ],
               headerLabelMap: {
                    pending: 'Requested By',
                    approved: 'Approved By',
                    in_transit: 'Sent By',
                    completed: 'Completed By',
                    disapproved: 'Disapproved By',
                    archived: 'Archived By',
                },
                rowFieldMap: {
                pending: 'requested_by',
                approved: 'approved_by_name',
                in_transit: 'in_transit_by_name',
                completed: 'completed_by_name',
                disapproved: 'disapproved_by_name',
                archived: 'archived_by_name',
            },
            pendingSendOuts: [],
            loadingSendOuts: false,
            receivingId: null,
            currentReceiveTransferId: null,
           }
       },
       mounted() {
           // this.fetchRecords();
           console.log(this.statusFilter);
           const lastTab = localStorage.getItem('inventory_transfer_last_tab');
   
           if (lastTab) {
               this.statusFilter = lastTab;
           }
   
           this.fetchRecords();
   
       },
       computed: {
            dynamicHeaderLabel() {
                return this.headerLabelMap[this.statusFilter] || 'Requested By';
            },
            dynamicRowField() {
                return this.rowFieldMap[this.statusFilter] || 'requested_by';
            }
        },
       methods: {
           fetchRecords(page = 1) {
               axios.get("{{ route('transfers.fetch') }}", {
                   params: {
                       status: this.statusFilter,
                       page: page,
                       per_page: this.pagination.per_page,
                   }
               })
               .then(response => {
   
                   const res = response.data;
   
                   // Main data
                   this.records = res.data || res;
   
                   console.log("✅ Fetched records:", this.records);
   
                   // Pagination (if API paginated)
                   if (res.current_page) {
                       this.pagination.current_page = res.current_page;
                       this.pagination.per_page = res.per_page;
                       this.pagination.total = res.total;
                       this.pagination.from = res.from;
                       this.pagination.to = res.to;
                       this.pagination.last_page = res.last_page;
                   }
               })
               .catch(error => {
                   console.error("❌ Error fetching records:", error);
               });
           },
           setStatus(status) {
               this.statusFilter = status;
   
                // ✅ Remember last opened tab
               localStorage.setItem('inventory_transfer_last_tab', status);
               this.fetchRecords(1);
           },
           displayStatus(row) {
       if (row.status === 'in_transit') {
           console.log("Row is_partial:", row.is_partial);
           return row.is_partial
               ? 'In-Transit (Partial)'
               : 'In-Transit';
       }
   
       return row.status.replace('_', ' ').toUpperCase();
   },
   
           getStatusBadgeClass(row) {
           if (row.status === 'in_transit') {
               const isPartial = row.items?.some(
                   item => item.quantity_sent < item.quantity_requested
               );
   
               return isPartial ? 'badge badge-warning' : 'badge badge-info';
           }
   
           return 'badge badge-secondary';
       },
       async openReceiveModal(row) {
           this.pendingSendOuts = [];
           this.receivingId = null;
           this.currentReceiveTransferId = row.id;
           this.loadingSendOuts = true;
           $('#receiveDeliveryModal').modal('show');

           try {
               const res = await axios.get(`/inventory/transfer/${row.id}/pending-send-outs`);
               this.pendingSendOuts = res.data.send_outs;
           } catch (error) {
               console.error(error);
               Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load deliveries.' });
               $('#receiveDeliveryModal').modal('hide');
           } finally {
               this.loadingSendOuts = false;
           }
       },
       async acceptDelivery(sendOutId) {
           this.receivingId = sendOutId;
           try {
               await axios.post(`/inventory/transfer/send-out/${sendOutId}/receive`);
               this.pendingSendOuts = this.pendingSendOuts.filter(s => s.id !== sendOutId);
               Swal.fire({ icon: 'success', title: 'Received!', text: 'Delivery accepted and stocks added to inventory.' });
               if (this.pendingSendOuts.length === 0) {
                   $('#receiveDeliveryModal').modal('hide');
                   this.fetchRecords(this.pagination.current_page);
               }
           } catch (error) {
               console.error(error);
               const msg = error.response?.data?.message || 'Something went wrong.';
               Swal.fire({ icon: 'error', title: 'Error', text: msg });
           } finally {
               this.receivingId = null;
           }
       },
   },
   });
</script>
@endsection