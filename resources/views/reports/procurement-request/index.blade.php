@extends('layouts.app')
@section('content')
<div class="main-content" id="app">
  <div>
      <div class="breadcrumb">
          <h1 class="mr-3">Reports</h1>
          <ul>
          <li><a href=""> PRF - Procurement Request Form</a></li>
          <!----> <!---->
          </ul>
          <div class="breadcrumb-action"></div>
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
      </div>
      <div class="card-body">
        <div class="vgt-wrap">
            <div class="vgt-fixed-header">
            </div>
                <div class="vgt-responsive">
                    <table id="vgt-table"  class="table-hover tableOne vgt-table">
                    <thead>
                        <tr>
                        <th class="vgt-left-align text-left">Date and Time Created</th>
                        <th class="vgt-left-align text-left">Created By</th>
                        <th class="vgt-left-align text-left">Requested By</th>
                        <th class="vgt-left-align text-left">Department</th>
                        <th class="vgt-left-align text-left">PRF Reference #</th>
                        <th class="vgt-left-align text-left">Proforma Reference #</th>
                        <th class="vgt-left-align text-left">Type of Request</th>
                        <th class="vgt-left-align text-left">Origin</th>
                        <th class="vgt-left-align text-left">Requesting Branch</th>
                        <th class="vgt-left-align text-left">Status</th>
                        <th class="vgt-left-align text-left">PRF Details</th>
                        <th class="vgt-left-align text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in records" :key="row.id">
                        <td class="vgt-left-align text-left w-190px"> @{{ row.requested_datetime }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.created_by }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.requested_by }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.department }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.prf_reference_no }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.proforma_reference_no }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.type }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.origin }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.requesting_branch }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.status }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.details }}</td>
                        <td class="vgt-left-align text-right">
                                <actions-dropdown :row="row" 
                                @add-attachment="viewInvoice"
                                @view-attached-file="editTransfer"
                                @edit-prf="addViewAttachedFiles"
                                @move-to-canvassing="approveTransfer"
                                @disapprove-prf="disapproveTransfer"
                                @log-prf="archiveTransfer"
                                @remarks-prf="restoreTransfer"
                                ></actions-dropdown>
                            </td>
                        </tr>
                        <tr v-if="records.length === 0">
                            <td colspan="11" class="text-center text-muted">No data available.</td>
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
   
           <!-- Add Attachment -->
           <li>
               <a class="dropdown-item" href="#" @click.prevent="$emit('add-attachment', row.id)">
                   <i class="nav-icon i-Add-File font-weight-bold mr-2"></i>
                   Add Attachment
               </a>
           </li>
   
           <!-- View Attached File -->
           <li>
               <a class="dropdown-item" href="#" @click.prevent="$emit('view-attached-file', row.id)">
                   <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                   View Attached File
               </a>
           </li>
           <!-- Edit -->
           <li>
               <a class="dropdown-item" :href="`#`">
                   <i class="nav-icon i-Edit font-weight-bold mr-2"></i>
                   Edit
               </a>
           </li>
   
           <!-- Move for Canvassing -->
           <li v-if="(row.status) == 'pending'">
               <a class="dropdown-item" :href="`#`"
               @click.prevent="changeStatus(row.id, 'canvassing')">
                   <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                   Move for Canvassing
               </a>
           </li>
   
            <!-- Disapprove -->
           <li v-if="(row.status) == 'pending'">
               <a class="dropdown-item" :href="`#`"
               @click.prevent="changeStatus(row.id, 'disapproved')">
                   <i class="nav-icon i-Unlike-2 font-weight-bold mr-2"></i>
                   Disapprove
               </a>
           </li>
   
           <!-- Logs -->
           <li>
               <a class="dropdown-item" href="#">
                   <i class="nav-icon i-Computer-Secure font-weight-bold mr-2"></i>
                   Logs
               </a>
           </li>
   
           <!-- Remarks -->
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
               axios.put(`{{ url('/reports/procurement-request') }}/${id}/update-status`, {
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
  el: "#app",
  data: {
    statusFilter: 'pending',
    statusList: [
        { label: 'Pending', value: 'pending' },
        { label: 'For Canvassing', value: 'canvassing' },
        { label: 'Approved', value: 'approved' },
        { label: 'Processed', value: 'processed' },
        { label: 'Disapproved', value: 'disapproved' },
        { label: 'Archived', value: 'archived' },
    ],
  }
})
</script>
@endsection