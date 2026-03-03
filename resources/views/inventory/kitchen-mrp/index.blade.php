@extends('layouts.app')
@section('content')
<style>
     .dropdown-menu {
        position: relative;
    }
</style>
<div class="main-content" id="app">
   <div>
      <div class="breadcrumb">
         <h1 class="mr-3">
            Kitchen Mass Production
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
                              onclick="window.location='{{ route('kitchen-mrp.create') }}'">
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
                              <th>Date and Time of Request</th>
                              <th>@{{dynamicHeaderLabel}}</th>
                              <th>Reference #</th>
                              <th>SKU</th>
                              <th>Product Name</th>
                              <th>Quantity</th>
                              <th>Status</th>
                              <th>Action</th>
                           </tr>
                           <!---->
                        </thead>
                        <tbody>
                           <tr v-for="row in records" :key="row.id">
                              <td class="vgt-left-align text-left w-190px"> @{{ row.created_at }}</td>
                              <td class="vgt-left-align text-left w-160px">@{{ row.created_by_name }}</td>
                              <td class="vgt-left-align text-left w-220px">@{{ row.reference_no }}</td>
                              <td class="vgt-left-align text-left w-160px">@{{ row.sku }}</td>
                              <td class="vgt-left-align text-left w-160px">@{{ row.product_name }}</td>
                              <td class="vgt-left-align text-left w-160px">@{{ row.quantity }}</td>
                              <td class="vgt-left-align text-left w-160px">
                                    <span :class="statusBadgeClass(row.status)" class="badge">
                                        @{{ capitalizeFirst(row.status) }}
                                    </span>
                                </td>
                              <td class="vgt-left-align text-right">
                                 <actions-dropdown :row="row" 
                                 @edit-item="editItem"
                                 @logprocess-item="logItem"
                                 @approve-item="approveItem"
                                 @disapprove-item="disapproveItem"
                                 @archive-item="archiveItem"
                                 @restore-item="restoreItem"
                                 @delete-item="deleteItem"
                                 @remark-item="remarkItem"
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
</div>
@endsection
@section('scripts')
<script type="text/x-template" id="actions-dropdown-template">
   <div class="dropdown btn-group" ref="dropdown">
       <button type="button" class="btn dropdown-toggle btn-link btn-lg text-decoration-none dropdown-toggle-no-caret"
               @click.stop="toggleDropdown">
           <span class="_dot _r_block-dot bg-dark"></span>
           <span class="_dot _r_block-dot bg-dark"></span>
           <span class="_dot _r_block-dot bg-dark"></span>
       </button>
   
       <ul :class="['dropdown-menu dropdown-menu-right', { show: isOpen }]">
   
            <!-- Edit -->
           <li v-if="['pending', 'approved'].includes(row.status)">
               <a class="dropdown-item" :href="`/inventory/kitchen-mrp/${row.id}/edit`">
                   <i class="nav-icon i-Edit font-weight-bold mr-2"></i>
                   Edit
               </a>
           </li>

           <!-- Log Process Goods -->
           <li v-if="row.status === 'approved'">
               <a class="dropdown-item" :href="`/inventory/kitchen-mrp/${row.id}/logGoods`">
                   <i class="fa-solid fa-arrow-right-to-bracket"></i>
                   Log Processed Goods
               </a>
           </li>
   
           <!-- Approve -->
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
   
   
   
           <!-- Disapprove -->
           <li v-if="['pending'].includes(row.status)">
               <a class="dropdown-item" href="#" @click.prevent="changeStatus(row.id, 'disapproved')">
                   <i class="nav-icon i-Unlike-2 font-weight-bold mr-2"></i>
                   Disapprove
               </a>
           </li>
   
           <!-- For Disapproved & Archived – Add Restore Option -->
           <li v-if="['disapproved', 'archived'].includes(row.status)">
               <a class="dropdown-item" href="#" @click.prevent="changeStatus(row.id, 'pending')">
                   <i class="nav-icon i-Restore-Window font-weight-bold mr-2"></i>
                   Restore to Pending
               </a>
           </li>
   
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
               axios.put(`{{ url('/inventory/kitchen-mrp') }}/${id}/update-status`, {
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
                approved: 'approved_by_name',
                completed: 'completed_by_name',
                disapproved: 'disapproved_by_name',
                archived: 'archived_by_name',
            },
           }
       },
       mounted() {
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
            // Capitalize first letter
            capitalizeFirst(text) {
                if (!text) return '';
                return text.charAt(0).toUpperCase() + text.slice(1);
            },

            // Return badge class based on status
            statusBadgeClass(status) {
                switch (status) {
                    case 'pending': return 'badge bg-warning text-dark';
                    case 'approved': return 'badge bg-success';
                    case 'disapproved': return 'badge bg-danger';
                    case 'completed': return 'badge bg-primary';
                    default: return 'badge bg-secondary';
                }
            },
           fetchRecords(page = 1) {
               axios.get("{{ route('kitchen-mrp.fetch') }}", {
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

               this.fetchRecords(1);
           },
       },
   });
</script>
@endsection