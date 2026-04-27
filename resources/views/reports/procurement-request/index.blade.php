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
          <h1 class="mr-3">Inventory</h1>
          <ul>
          <li><a href=""> PRF - Procurement Request Form</a></li>
          <!----> <!---->
          </ul>
          <div class="breadcrumb-action"></div>
      </div>
      <div class="separator-breadcrumb border-top"></div>
  </div>
  <div class="modal fade" id="ItemDetailsModal" tabindex="-1" aria-labelledby="ItemDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        
            <div class="modal-header">
                <h5 class="modal-title" id="ItemDetailsModalLabel">PRF Details</h5>
            </div>

            <div class="modal-body">
                <div class="list-group">
                    <div class="list-group-item p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Sub Type</th>
                                    <th>SKU</th>
                                    <th>Name</th>
                                    <th>Quantity</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in modalItems" :key="index">
                                    <td>@{{ item.subtype }}</td>
                                    <td>@{{ item.code }}</td>
                                    <td>@{{ item.name }}</td>
                                    <td>@{{ item.quantity }}</td>
                                    <td>@{{ item.category }}</td>
                                    <td>@{{ item.unit?.name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                              onclick="window.location='{{ route('procurement-request.create') }}'">
                           <i class="i-Add"></i> Add
                           </button>
                        </div>
                     </div>
                  </div>
            <div class="vgt-fixed-header">
            </div>
                <div class="vgt-responsive">
                    <table id="vgt-table"  class="table-hover tableOne vgt-table">
                    <thead>
                        <tr>
                        <th class="vgt-left-align text-left">Date and Time Created</th>
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
                        <td class="vgt-left-align text-left w-190px"> @{{ row.requested_by }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.department }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.prf_reference_no }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.proforma_reference_no }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.type }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.origin }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.requesting_branch }}</td>
                        <td class="vgt-left-align text-left w-190px"> @{{ row.status }}</td>
                        <td><button class="btn btn-sm btn-primary" @click="openModal(row.items)">View</button></td>
                        {{-- <td class="vgt-left-align text-left w-190px"> @{{ row.details }}</td> --}}
                        <td class="vgt-left-align text-right">
                                <actions-dropdown :row="row" 
                                @add-attachment="addAttachment"
                                @view-attached-file="viewAttachedFiles"
                                @edit-prf="edit"
                                @move-to-canvassing="moveToCanvassing"
                                @disapprove-prf="disapprove"
                                @log-prf="log"
                                @remarks-prf="remarks"
                                @status-updated="fetchRecords()"
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
               <a class="dropdown-item" :href="`/inventory/procurement-request/${row.id}/edit`">
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

           <!-- Add Canvas Products -->
           <li v-if="row.status === 'canvassing'">
               <a class="dropdown-item" :href="`/inventory/procurement-request/${row.id}/canvass`">
                   <i class="nav-icon i-Tag-5 font-weight-bold mr-2"></i>
                   Add Canvas Products
               </a>
           </li>

           <!-- Approve -->
           <li v-if="row.status === 'canvassing' && row.has_canvass">
               <a class="dropdown-item" href="#" @click.prevent="changeStatus(row.id, 'approved')">
                   <i class="nav-icon i-Like-2 font-weight-bold mr-2"></i>
                   Approve
               </a>
           </li>

           <!-- Create PO -->
           <li v-if="row.status === 'approved' && row.has_canvass">
               <a class="dropdown-item" href="#" @click.prevent="generatePos(row.id)">
                   <i class="nav-icon i-Financial font-weight-bold mr-2"></i>
                   Create PO
               </a>
           </li>

           <!-- Log to Process -->
           <li v-if="row.status === 'approved'">
               <a class="dropdown-item" href="#" @click.prevent="changeStatus(row.id, 'processed')">
                   <i class="nav-icon i-Checked-User font-weight-bold mr-2"></i>
                   Log to Process
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
           toggleDropdown() { 
            this.isOpen = !this.isOpen; 
        },
           handleClickOutside(event) {
               if (!this.$refs.dropdown?.contains(event.target)) this.isOpen = false;
           },
           changeStatus(id, newStatus) {
                const labels = {
                    canvassing: 'CANVASSING',
                    approved:   'APPROVED',
                    processed:  'PROCESSED',
                    disapproved:'DISAPPROVED',
                };
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Change status to "${labels[newStatus] || newStatus}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, change it!',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios.put(`{{ url('/inventory/procurement-request') }}/${id}/update-status`, { status: newStatus })
                        .then(response => {
                            const res = response.data;
                            let html = `Status changed to <strong>${(labels[res.status] || res.status).toUpperCase()}</strong><br><small>${res.updated_at}</small>`;
                            if (res.pos_created > 0) {
                                html += `<br><span class="text-success"><i class="i-Check"></i> ${res.pos_created} Purchase Order(s) created.</span>`;
                            }
                            Swal.fire({ icon: 'success', title: 'Updated!', html, showConfirmButton: true });
                            this.$emit('status-updated');
                        })
                        .catch(() => {
                            Swal.fire({ icon: 'error', title: 'Failed!', text: 'Failed to update status.' });
                        });
                });
            },

            generatePos(id) {
                Swal.fire({
                    title: 'Create Purchase Orders?',
                    text: 'This will generate POs from the approved canvass entries.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Create PO!',
                    cancelButtonText: 'Cancel',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    axios.post(`{{ url('/inventory/procurement-request') }}/${id}/generate-pos`)
                        .then(response => {
                            Swal.fire({
                                icon: 'success',
                                title: 'POs Created!',
                                text: response.data.message,
                                showConfirmButton: true,
                            });
                            this.$emit('status-updated');
                        })
                        .catch(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed!',
                                text: err.response?.data?.message || 'Failed to create Purchase Orders.',
                            });
                        });
                });
            }
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
     records: [], 
     pagination: {
        current_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0,
        last_page: 1
    },
    statusFilter: 'pending',
    statusList: [
        { label: 'Pending', value: 'pending' },
        { label: 'For Canvassing', value: 'canvassing' },
        { label: 'Approved', value: 'approved' },
        { label: 'Processed', value: 'processed' },
        { label: 'Disapproved', value: 'disapproved' },
        { label: 'Archived', value: 'archived' },
    ],
    allItems: {
        products: [],
        components: []
        },
    modalItems: [],
  },
    mounted() {
        this.fetchRecords();
    },
  methods: {
           fetchRecords(page = 1) {
               axios.get("{{ route('procurement-request.fetch') }}", {
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
            openModal(items) {
                if (!items) {
                    this.modalItems = [];
                } else {
                    this.modalItems = [
                        ...(items.products || []),
                        ...(items.components || [])
                    ];
                }

                console.log('MODAL:', this.modalItems); // debug

                new bootstrap.Modal(document.getElementById('ItemDetailsModal')).show();
            }
       },
})
</script>
@endsection