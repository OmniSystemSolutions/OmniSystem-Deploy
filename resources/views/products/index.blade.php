@extends('layouts.app')
@section('content')
<style>
       .modal-dialog {
         max-width: 90%;
       }
</style>
<div class="main-content" id="app">
   <div>
      <div class="breadcrumb">
         <h1 class="mr-3">Products and Components</h1>
         <ul>
            <li><a href=""> Inventory </a></li>
         </ul>
         <div class="breadcrumb-action"></div>
      </div>
      <div class="separator-breadcrumb border-top"></div>
   </div>
   <!-- Import Modal -->
   <div class="modal fade" id="importModal" tabindex="-1">
      <div class="modal-dialog modal-xl">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Import Products</h5>
               <button type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  @click="resetImport">
               </button>
            </div>
            <div class="modal-body">
               <form @submit.prevent="submitImport">
                  <div class="row">
                     <!-- File upload -->
                     <div class="col-12 mb-4">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                           <div class="flex-grow-1">
                              <label class="form-label">Choose CSV / Excel file</label>
                             
                              <!-- File Input -->
                              <input
                                 type="file"
                                 @change="handleFileUpload"
                                 accept=".csv, .xlsx"
                                 class="form-control mb-3"
                              />
                              <small class="text-muted" v-if="importFileName">
                              Selected: @{{ importFileName }}
                              </small>
                           </div>
                           <div>
                              <a href="/import/product_sample/import_for_products.csv"
                                 class="btn btn-info btn-sm"
                                 download>
                              Download Example
                              </a>
                           </div>
                        </div>
                     </div>
                     <!-- CSV Import Preview Table -->
                     <div class="mb-3 col-sm-12 col-md-12">
                        <div class="list-group">
                           <div class="list-group-item p-0">
                              <div class="table-responsive import-table-container">
                                 <table class="table table-bordered" v-if="importRows.length">
                                    <thead>
                                       <tr>
                                          <th>SKU</th>
                                          <th>Name</th>
                                          <th>Category</th>
                                          <th>Subcategory</th>
                                          <th>Quantity</th>
                                          <th>Price</th>
                                          <th>Unit</th>
                                          <th>Status</th>
                                          <th>Action</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                       <tr v-for="(row, index) in importRows" :key="index">
                                          <td>@{{ row.code }}</td>
                                          <td>@{{ row.name }}</td>
                                          <td>@{{ row.category ? row.category.name : '' }}</td>
                                          <td>@{{ row.subcategory ? row.subcategory.name : '' }}</td>
                                          <td>@{{ row.quantity }}</td>
                                          <td>@{{ row.price }}</td>
                                          <td>@{{ row.unit ? row.unit.name : '' }}</td>
                                          <td>
                                          <span v-if="row.status === 'ready'" class="text-success font-weight-bold">✔ Ready</span>
                                          <span v-else-if="row.status === 'duplicate'" class="text-danger font-weight-bold">⚠ Duplicate</span>
                                          <span v-else class="text-muted">Pending</span>
                                          </td>
                                          <td>
                                          <button class="btn btn-sm btn-danger" @click="removeRow(index)">Remove</button>
                                          </td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                        </div>
                     </div>
                     <!-- Actions -->
                     <div class="col-12">
                        <div class="d-flex gap-2 justify-content-end">
                           <button type="button" class="btn btn-primary" @click="verifyImport">
                              Verify
                           </button>
                           <button type="submit"
                                 class="btn btn-primary"
                                 :disabled="!canSubmit"
                                 @click.prevent="submitImport">
                           Submit
                           </button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>

   <div class="wrapper">
      <div class="row">
         <div class="col-md-4">
            <label class="col-form-label pt-0">Select Type</label>
            <v-select 
               :options="types" 
               label="label" 
               :reduce="type => type.value" 
               v-model="selectedType" 
               @input="goToPage" 
               placeholder="Select type" 
               />
         </div>
      </div>
      <div class="card mt-4">
         <div class="card-body">
            <nav class="card-header">
               <ul class="nav nav-tabs card-header-tabs">
                  <li class="nav-item">
                     <a href="#" 
                        class="nav-link"
                        :class="{ active: statusFilter === 'active' }"
                        @click.prevent="setStatus('active')">
                     Active
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="#"
                        class="nav-link"
                        :class="{ active: statusFilter === 'archived' }"
                        @click.prevent="setStatus('archived')">
                     Archived
                     </a>
                  </li>
               </ul>
            </nav>
            <div class="card-body">
               <div class="vgt-wrap">
                  <div class="vgt-inner-wrap">
                     <div class="vgt-global-search vgt-clearfix">
                        <div class="vgt-global-search__input vgt-pull-left">
                           <span aria-hidden="true" class="input__icon">
                              <div class="magnifying-glass"></div>
                           </span>
                           <form role="search" method="GET" action="{{ route('products.index') }}" class="mb-3" style="position: relative;">
                              <label for="tableSearch" style="cursor: pointer;" onclick="this.closest('form').submit()">
                              <span class="sr-only">Search</span>
                              </label>
                              <input 
                                 id="tableSearch" 
                                 name="search" 
                                 type="text" 
                                 value="{{ request('search') }}" 
                                 placeholder="Search this table" 
                                 class="vgt-input vgt-pull-left" 
                                 onkeydown="if(event.key === 'Enter') this.form.submit()"
                                 >
                           </form>
                        </div>
                        <div class="vgt-global-search__actions vgt-pull-right">
                           <div>
                              <div id="dropdown-form" class="dropdown b-dropdown mx-1 btn-group" :class="{ show: showColumnDropdown }">
                                 <button 
                                    type="button" 
                                    class="btn dropdown-toggle btn-light dropdown-toggle-no-caret" 
                                    @click="toggleDropdown" 
                                    aria-haspopup="menu">
                                 <i class="i-Gear"></i>
                                 </button>
                                 <ul class="dropdown-menu dropdown-menu-right" v-show="showColumnDropdown" role="menu" style="display: block;">
                                    <li role="presentation">
                                       <header class="dropdown-header">Columns</header>
                                    </li>
                                    <li role="presentation" style="width: 220px;">
                                       <form class="b-dropdown-form p-0">
                                          <section>
                                             <div class="px-4" style="max-height: 400px; overflow:auto;">
                                                <ul class="list-unstyled">
                                                   <li v-for="col in columns" :key="col.field">
                                                      <div class="my-1 custom-control custom-checkbox">
                                                         <input 
                                                            type="checkbox" 
                                                            class="custom-control-input" 
                                                            :id="`col-${col.field}`" 
                                                            :checked="!col.hidden" 
                                                            @change="toggleColumn(col.field)"
                                                            >
                                                         <label class="custom-control-label" :for="`col-${col.field}`">
                                                         @{{ col.label }}
                                                         </label>
                                                      </div>
                                                   </li>
                                                </ul>
                                             </div>
                                          </section>
                                       </form>
                                    </li>
                                 </ul>
                              </div>
                              <button
                                 type="button"
                                 class="btn mx-1 btn-outline-info btn-sm"
                                 @click="showFilterSidebar = true"
                                 >
                              <i class="i-Filter-2"></i> Filter
                              </button>
                              <button
                                 class="btn btn-sm btn-outline-danger ripple mx-1"
                                 @click="exportExcel"
                                 >
                              <i class="i-File-Excel"></i> Export
                              </button>
                              {{-- Import button: hide if archived --}}
                              {{-- @if ($status !== 'archived') --}}
                              <button 
                                 v-if="statusFilter !== 'archived'"
                                 type="button"
                                 class="btn btn-info m-1 btn-sm"
                                 @click="openImportModal"
                                 >
                              <i class="i-Upload"></i> Import
                              </button>
                              {{-- @endif --}}
                              {{-- Add button: hide if archived --}}
                              {{-- @if ($status !== 'archived') --}}
                              <button type="button" class="btn mx-1 btn-btn btn-primary btn-icon" onclick="window.location='{{ url('products/create') }}'">
                              <i class="i-Add"></i> Add
                              </button>
                              {{-- @endif --}}
                              {{-- Stock Alert Summary: show only if not active and not archived --}}
                              {{-- @if ($status !== 'active' && $status !== 'archived')
                              <button type="button" class="btn mx-1 btn-btn btn-primary">
                              Stock Alert Summary
                              </button>
                              @endif --}}
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="vgt-fixed-header"></div>
            <div class="vgt-responsive" style="max-height: 400px; overflow-y: auto;">
               <table id="vgt-table" class="table-hover tableOne vgt-table custom-vgt-table">
                  <colgroup>
                     <col v-for="(col, i) in visibleColumns" :key="i">
                  </colgroup>
                  <thead>
                     <tr>
                        <th v-for="col in visibleColumns" 
                           :key="col.field" 
                           class="vgt-left-align text-left sortable" 
                           :data-column="col.field">
                           <span>@{{ col.label }}</span>
                        </th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr v-for="row in filteredRows" :key="row.id">
                        <td v-for="col in visibleColumns" :key="col.field" :data-column="col.field">
                           <template v-if="col.field === 'product_sku'">
                              @{{ row.code }}
                           </template>
                           <template v-else-if="col.field === 'product_name'">
                              @{{ row.name }}
                           </template>
                           <template v-else-if="col.field === 'category'">
                              @{{ row.category?.name || 'N/A' }}
                           </template>
                           <template v-else-if="col.field === 'subcategory'">
                              @{{ row.subcategory?.name || 'N/A' }}
                           </template>
                           <template v-else-if="col.field === 'product_quantity'">
                              @{{ Number(row.quantity).toFixed(2) }}
                           </template>
                           <template v-else-if="col.field === 'product_price'">
                              @{{ Number(row.price).toFixed(2) }}
                           </template>
                           <template v-else-if="col.field === 'product_unit'">
                              @{{ row.unit?.name || 'N/A' }}
                           </template>
                           <template v-else-if="col.field === 'action'">
                              <actions-dropdown 
                              :row="row" 
                              @edit-route="editRoute"
                              @delete-route="deleteRoute"
                              @archive-route="archiveRoute"
                              @restore-route="restoreRoute"
                              @stock-card-route="stockCardRoute"
                              @logs-route="logsRoute"
                              @remarks-route="remarksRoute"
                              @open-remarks-modal="openRemarksModal"
                              >
                              </actions-dropdown>
                           </template>
                        </td>
                     </tr>
                     <tr v-if="!filteredRows.length">
                        <td :colspan="visibleColumns.length" class="vgt-center-align vgt-text-disabled">
                           No data for table
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
            <div class="modal fade" id="remarksModal" tabindex="-1" role="dialog" aria-labelledby="remarksModalLabel" aria-hidden="true">
               <div class="modal-dialog" role="document">
                  <div class="modal-content">
                     <header class="modal-header">
                        <h5 class="modal-title" id="remarksModalLabel">Remarks</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                     </header>
                     <div class="modal-body">
                        <form id="remarksForm">
                           @csrf
                           <input type="hidden" id="remarksItemId" value="{{ $product->id ?? '' }}">
                           <fieldset class="form-group">
                              <textarea 
                                 name="remarks" 
                                 id="remarksText"
                                 placeholder="Type your message" 
                                 rows="3" 
                                 class="form-control" 
                                 cols="30">
                                 </textarea>
                              <div class="invalid-feedback">This field is required</div>
                           </fieldset>
                           <div class="d-flex justify-content-end">
                              <button type="submit" class="btn btn-primary btn-icon btn-rounded">
                              <i class="i-Yes me-2 font-weight-bold"></i> Submit
                              </button>
                           </div>
                        </form>
                        <hr>
                        <div class="remarks-history">
                           <div class="mb-3">
                              <div class="d-flex align-items-center">
                                 <i class="i-User me-2"></i>
                                 <span class="text-primary fw-bold">
                                 {{ Auth::check() ? Auth::user()->name : 'Guest User' }}
                                 </span>
                              </div>
                           </div>
                           <ul class="timeline" id="remarksTimeline"></ul>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="vgt-wrap__footer vgt-clearfix mt-3">
               <div class="footer__row-count vgt-pull-left">
                  <label class="footer__row-count__label">Rows per page:</label>
                  <select v-model="perPage" @change="fetchProducts(1)">
                     <option v-for="size in [10,20,30,40,50]" :key="size" :value="size">
                        @{{ size }}
                     </option>
                  </select>
               </div>
               <div class="footer__navigation vgt-pull-right">
                  <div class="footer__navigation__page-info me-3">
                     <div>@{{ pageFrom }} - @{{ pageTo }} of @{{ pagination.total }}</div>
                  </div>
                  <button 
                     class="footer__navigation__page-btn" 
                     :class="{ disabled: pagination.current_page === 1 }" 
                     @click="fetchProducts(pagination.current_page - 1)" 
                     :disabled="pagination.current_page === 1">
                  <span class="chevron left"></span>
                  <span>prev</span>
                  </button>
                  <button 
                     class="footer__navigation__page-btn" 
                     :class="{ disabled: pagination.current_page === pagination.last_page }" 
                     @click="fetchProducts(pagination.current_page + 1)" 
                     :disabled="pagination.current_page === pagination.last_page">
                  <span>next</span>
                  <span class="chevron right"></span>
                  </button>
               </div>
            </div>
            <div tabindex="-1" class="b-sidebar-outer">
               <!-- Sidebar -->
               <div
                  id="sidebar-right"
                  tabindex="-1"
                  class="b-sidebar shadow b-sidebar-right bg-white text-dark sidebar-open"
                  v-show="showFilterSidebar"
                  >
                  <!-- Header -->
                  <header class="b-sidebar-header">
                     <button
                        type="button"
                        aria-label="Close"
                        class="close text-dark"
                        @click="showFilterSidebar = false"
                        >
                     ✕
                     </button>
                     <strong id="sidebar-right___title__">Filter</strong>
                  </header>
                  <!-- Body -->
                  <div class="b-sidebar-body">
                     <div class="px-3 py-2">
                        <div class="row">
                           <!-- Product SKU -->
                           <div class="col-md-12">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">
                                    Product SKU
                                 </legend>
                                 <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Search by product code"
                                    v-model="filters.code"
                                    >
                              </fieldset>
                           </div>
                           <!-- Product Name -->
                           <div class="col-md-12">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">
                                    Product Name
                                 </legend>
                                 <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Search by product name"
                                    v-model="filters.name"
                                    >
                              </fieldset>
                           </div>
                           <!-- Category -->
                           <div class="col-md-12">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">
                                    Category
                                 </legend>
                                 <v-select
                                    :options="categories"
                                    label="name"
                                    :reduce="c => c.id"
                                    v-model="filters.category"
                                    placeholder="Select category"
                                    clearable
                                    />
                              </fieldset>
                           </div>
                           <!-- Subcategory -->
                           <div class="col-md-12">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">
                                    Subcategory
                                 </legend>
                                 <v-select
                                    :options="filteredSubcategories"
                                    label="name"
                                    :reduce="s => s.id"
                                    v-model="filters.subcategory"
                                    placeholder="Select subcategory"
                                    :disabled="!filters.category"
                                    clearable
                                    />
                              </fieldset>
                           </div>
                           <!-- Quantity Range -->
                           <div class="col-md-12">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">
                                    Quantity Range
                                 </legend>
                                 <div class="d-flex gap-2">
                                    <input
                                       type="number"
                                       class="form-control"
                                       placeholder="From"
                                       v-model.number="filters.quantity_from"
                                       >
                                    <input
                                       class="form-control"
                                       placeholder="To"
                                       v-model.number="filters.quantity_to"
                                       >
                                 </div>
                              </fieldset>
                           </div>
                           <!-- Price Range -->
                           <div class="col-md-12">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">
                                    Price Range
                                 </legend>
                                 <div class="d-flex gap-2">
                                    <input
                                       type="number"
                                       class="form-control"
                                       placeholder="From"
                                       v-model.number="filters.price_from"
                                       >
                                    <input
                                       type="number"
                                       class="form-control"
                                       placeholder="To"
                                       v-model.number="filters.price_to"
                                       >
                                 </div>
                              </fieldset>
                           </div>
                           <!-- Unit -->
                           <div class="col-md-12">
                              <fieldset class="form-group">
                                 <legend class="col-form-label pt-0">
                                    Product Unit
                                 </legend>
                                 <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Search by product unit"
                                    v-model="filters.unit"
                                    >
                              </fieldset>
                           </div>
                           <!-- Buttons -->
                           <div class="col-sm-12 col-md-6">
                              <button
                                 type="button"
                                 class="btn btn-primary btn-sm btn-block"
                                 @click="applyFilter"
                                 >
                              <i class="i-Filter-2"></i>
                              Filter
                              </button>
                           </div>
                           <div class="col-sm-12 col-md-6">
                              <button
                                 type="button"
                                 class="btn btn-danger btn-sm btn-block"
                                 @click="resetFilter"
                                 >
                              <i class="i-Power-2"></i>
                              Reset
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <span data-v-03022ced="">
               <!---->
            </span>
         </div>
      </div>
   </div>
</div>
@endsection


@section('scripts')
<script type="text/x-template" id="actions-dropdown-template">
<div class="dropdown btn-group" ref="dropdown">
    <!-- 3 Dots Button -->
    <button type="button" 
            class="btn dropdown-toggle btn-link btn-lg text-decoration-none dropdown-toggle-no-caret"
            @click.stop="toggleDropdown"
            :aria-expanded="isOpen.toString()">
        <span class="_dot _r_block-dot bg-dark"></span>
        <span class="_dot _r_block-dot bg-dark"></span>
        <span class="_dot _r_block-dot bg-dark"></span>
            <!-- 🔴 Remarks Badge (BESIDE the dots) -->
    <span
        :id="`remarksBadge-${row.id}`"
        class="badge bg-danger text-white remarks-badge d-none position-absolute "
        style="font-size: 0.55rem; transform: translate(40%, -40%) !important;"
    >
        1
    </span>
    </button>



    <!-- Dropdown menu -->
    <ul :class="['dropdown-menu dropdown-menu-right', { show: isOpen }]">

        <!-- Edit -->
           <li v-if="row.status == 'active'">
               <a class="dropdown-item" :href="`/products/${row.id}/edit`">
                   <i class="nav-icon i-Edit font-weight-bold mr-2"></i>
                   Edit
               </a>
           </li>

           <li v-if="row.status == 'active'">
               <a class="dropdown-item" @click="archiveProduct(row.id)">
                   <i class="nav-icon i-Letter-Close font-weight-bold mr-2"></i>
                   Archive
               </a>
           </li>

           <li v-if="row.status == 'archived'">
               <a class="dropdown-item" @click="restoreProduct(row.id)">
                   <i class="nav-icon i-Refresh font-weight-bold mr-2"></i>
                   Restore
               </a>
           </li>

           <li v-if="row.status == 'active'">
               <a class="dropdown-item" :href="`/products/${row.id}/stock-card`">
                   <i class="nav-icon i-Receipt font-weight-bold mr-2"></i>
                   View Stock Card
               </a>
           </li>

             <li v-if="row.status == 'active'">
                <a class="dropdown-item" :href="`/products/${row.id}/logs`">
                      <i class="nav-icon i-Computer-Secure font-weight-bold mr-2"></i>
                      Logs
                </a>
             </li>

               <li v-if="row.status == 'active'">
                  <button class="dropdown-item" @click="$emit('open-remarks-modal', row.id)">
                        <i class="nav-icon i-Mail-Attachement font-weight-bold mr-2"></i>
                        Remarks
                        
                  </button>
               </li>    

    </ul>
</div>
</script>


<script>
Vue.component("actions-dropdown", {
    template: "#actions-dropdown-template",
    props: {
        row: { type: Object, required: true }
    },
    data() {
        return {
            isOpen: false
        };
    },
    methods: {
      archiveProduct(productId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to move this unit to archive!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, archive it!',
            }).then((result) => {
               if (result.isConfirmed) {
                     axios.put(`/products/${productId}/archive`)
                        .then(res => {
                           Swal.fire('Archived!', res.data.message, 'success')
                                 .then(() => {
                                    // Reload after user clicks Okay
                                    window.location.reload();
                                 });
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Failed!', 'Could not archive product.', 'error');
                        });
                }
            });
        },
        restoreProduct(productId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to restore this product!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore it!',
            }).then((result) => {
               if (result.isConfirmed) {
                     axios.put(`/products/${productId}/restore`)
                        .then(res => {
                           Swal.fire('Restored!', res.data.message, 'success')
                                 .then(() => {
                                    // Reload after user clicks Okay
                                    window.location.reload();
                                 });
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Failed!', 'Could not restore product.', 'error');
                        });
                }
            });
        },
        toggleDropdown() { this.isOpen = !this.isOpen; },
        handleClickOutside(event) {
            if (!this.$refs.dropdown?.contains(event.target)) this.isOpen = false;
        }
    },
    mounted() {
        document.addEventListener("click", this.handleClickOutside);
    },
    beforeDestroy() {
        document.removeEventListener("click", this.handleClickOutside);
    }
});

</script>

<script>
   document.addEventListener("DOMContentLoaded", function () {
   const table = document.querySelector("#vgt-table");
   if (!table) return;

   const headers = table.querySelectorAll("thead th");

   headers.forEach((header, index) => {
      // Make header visually clickable
      header.style.cursor = "pointer";

      header.addEventListener("click", function () {
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));
            const isAsc = header.classList.toggle("asc");

            // Remove sorting classes from other headers
            headers.forEach((h, i) => {
               if (i !== index) h.classList.remove("asc", "desc");
            });
            header.classList.toggle("desc", !isAsc);

            rows.sort((a, b) => {
               const aText = a.children[index].textContent.trim();
               const bText = b.children[index].textContent.trim();

               const aNum = parseFloat(aText.replace(/,/g, ""));
               const bNum = parseFloat(bText.replace(/,/g, ""));
               const bothNumbers = !isNaN(aNum) && !isNaN(bNum);

               if (bothNumbers) {
                  return isAsc ? aNum - bNum : bNum - aNum;
               } else {
                  return isAsc
                        ? aText.localeCompare(bText)
                        : bText.localeCompare(aText);
               }
            });

            // Reattach sorted rows
            rows.forEach(row => tbody.appendChild(row));
      });
   });
});
</script>
<script>
function openRemarksModal(productId) {
    // Set the hidden input
    document.getElementById('remarksItemId').value = productId;

    // Clear previous remarks
    document.getElementById('remarksText').value = '';

    // Fetch existing remarks via /remarks?product_id=ID
    fetch(`/remarks?product_id=${productId}`)
        .then(res => res.json())
        .then(data => {
            const timeline = document.getElementById('remarksTimeline');
            timeline.innerHTML = '';

            const filteredRemarks = data.filter(remark => remark.product_id == productId);

            if (filteredRemarks.length === 0) {
                timeline.innerHTML = '<li>No remarks yet for this product.</li>';
            } else {
                filteredRemarks.forEach(remark => {
                    const li = document.createElement('li');
                    li.classList.add('mb-3', 'p-2', 'border-start', 'border-3', 'border-primary');

                    // Format timestamp
                    const date = new Date(remark.created_at);
                    const formattedDate = date.toLocaleString('en-US', {
                        month: '2-digit',
                        day: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });

                    // HTML layout
                    li.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-primary">
                            </span>
                            <small class="text-muted">${formattedDate}</small>
                        </div>
                        <p class="mb-2">${remark.remarks}</p>
                        <div>
                             <button class="btn btn-sm btn-outline-primary me-1"
                                 onclick="markAsRead(${remark.id}, ${productId})">Mark as Read</button>
                              <button class="btn btn-sm btn-primary"
                                 onclick="markAsUnread(${remark.id}, ${productId})">Mark as Unread</button>
                        </div>
                    `;

                    timeline.appendChild(li);
                });
            }
        })
        .catch(err => console.error(err));

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('remarksModal'));
    modal.show();
}

        function showRemarksBadge(productId) {
    const badge = document.getElementById(`remarksBadge-${productId}`);
    if (badge) badge.classList.remove('d-none');
}

function hideRemarksBadge(productId) {
    const badge = document.getElementById(`remarksBadge-${productId}`);
    if (badge) badge.classList.add('d-none');
}

function markAsRead(remarkId, productId) {
    fetch(`/remarks/${remarkId}/mark-read`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(() => {
        hideRemarksBadge(productId);
        alert('✅ Marked as Read');
    })
    .catch(err => console.error('Error marking as read:', err));
}

function markAsUnread(remarkId, productId) {
    fetch(`/remarks/${remarkId}/mark-unread`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(() => {
        showRemarksBadge(productId);
        alert('🔔 Marked as Unread');
    })
    .catch(err => console.error('Error marking as unread:', err));
}

         document.addEventListener('DOMContentLoaded', () => {
            fetch('/remarks')
               .then(res => res.json())
               .then(data => {
                     // only show badge for products that have UNREAD remarks
                     const unreadRemarks = data.filter(r => r.status === 'unread');
                     unreadRemarks.forEach(remark => {
                        if (remark.product_id) {
                           showRemarksBadge(remark.product_id);
                        }
                     });
               })
               .catch(err => console.error('Error fetching remarks:', err));
         });

// Handle form submission
   document.addEventListener('DOMContentLoaded', function () {
      const remarksForm = document.getElementById('remarksForm');
      const remarksText = document.getElementById('remarksText');
      const timeline = document.getElementById('remarksTimeline');

      // Create success alert element
      const alertBox = document.createElement('div');
      alertBox.className = 'alert alert-success mt-2 d-none';
      alertBox.textContent = '✅ Remark added successfully!';
      remarksForm.appendChild(alertBox);

      remarksForm.addEventListener('submit', function (e) {
         e.preventDefault();

         const remarks = remarksText.value.trim();
         const productId = document.getElementById('remarksItemId').value;

         if (!remarks || !productId) {
               alert('Please enter a remark.');
               return;
         }

         fetch(`/remarks/store`, {
               method: 'POST',
               headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
               },
               body: JSON.stringify({
                  product_id: productId,
                  remarks: remarks
               })
         })
         .then(res => res.json())
         .then(data => {
               // Show success message
               alertBox.classList.remove('d-none');

               // Clear input field
               remarksText.value = '';

               // Refresh remarks list
               fetch(`/remarks?product_id=${productId}`)
                  .then(res => res.json())
                  .then(updatedData => {
                     timeline.innerHTML = '';
                     const filteredRemarks = updatedData.filter(r => r.product_id == productId);

                     if (filteredRemarks.length === 0) {
                           timeline.innerHTML = '<li>No remarks yet for this product.</li>';
                     } else {
                           filteredRemarks.forEach(r => {
                              const li = document.createElement('li');
                              li.textContent = `${r.remarks}`;
                              timeline.appendChild(li);
                           });
                     }

                     // Show badge for product
                     showRemarksBadge(productId);

                     // Hide alert after 2s
                     setTimeout(() => alertBox.classList.add('d-none'), 2000);
                  });
         })
         .catch(err => {
               console.error('Error:', err);
               alert('❌ Failed to add remark');
         });
      });
   });
</script>
    <script>
        window.currentPage = "{{ request()->is('components*') ? 'components' : 'products' }}";
    </script>

    <script src="{{ asset('js/tableFunctions.js') }}"></script>
<script>
      Vue.component('v-select', VueSelect.VueSelect);
      new Vue({
    el: '#app',
    data() {
        return {
         importFile: null,
         importRows: [],
         importFileName: '',
         importPreview: [],
         importVerified: false,
         statusList: [
            { label: 'Active', value: 'active' },
            { label: 'Archived', value: 'archived' }
        ],
         statusFilter: 'active',
         selectedType: 'products',
         types: [
         { label: 'Products', value: 'products', url: '/products' },
         { label: 'Components', value: 'components', url: '/components' },
         { label: 'Bundled Items', value: 'bundled_items', url: '/bundled-items' },
         ],
         showColumnDropdown: false,
         showFilterSidebar: false,
         filters: {
            code: '',
            name: '',
            category: null,
            subcategory: null,
            quantity_from: null,
            quantity_to: null,
            price_from: null,
            price_to: null,
            for_sale: null,
         },
         columns: [
            { label: 'SKU', field: 'product_sku', hidden: false },
            { label: 'Product Name', field: 'product_name', hidden: false },
            { label: 'Category', field: 'category', hidden: false },
            { label: 'Sub Category', field: 'subcategory', hidden: false },
            { label: 'Quantity', field: 'product_quantity', hidden: false },
            { label: 'Price', field: 'product_price', hidden: false },
            { label: 'Unit', field: 'product_unit', hidden: false },
            { label: 'Action', field: 'action', hidden: false },
         ],
          rows: [],
          search: '',
          perPage: 10,
          status: 'active',
          loading: false,
          categories: [],
          subcategories: [],

          pagination: {
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
         }
      }
   },
   computed: {
      canSubmit() {
    if (!this.importVerified) return false
    if (!this.importRows.length) return false
    return !this.importRows.some(r => r.status !== 'ready')
  },
  visibleColumns() {
    return this.columns.filter(col => !col.hidden);
  },
  pageFrom() {
    if (!this.pagination.total) return 0;
    return (this.pagination.current_page - 1) * this.pagination.per_page + 1;
  },
  pageTo() {
    return Math.min(
      this.pageFrom + this.rows.length - 1,
      this.pagination.total
    );
  },
  filteredRows() {
    return this.rows.filter(row => {
       if (this.filters.code &&
        !row.code?.toLowerCase().includes(this.filters.code.toLowerCase())
      ) return false;

      if (this.filters.name &&
        !row.name?.toLowerCase().includes(this.filters.name.toLowerCase())
      ) return false;

      // Category filter
      if (
        this.filters.category &&
        (!row.category || row.category.id !== this.filters.category)
      ) {
        return false;
      }

      // Subcategory filter
      if (
        this.filters.subcategory &&
        (!row.subcategory || row.subcategory.id !== this.filters.subcategory)
      ) {
        return false;
      }

      if (this.filters.cost_from !== null &&
        Number(row.cost) < this.filters.cost_from
      ) return false;

      if (this.filters.cost_to !== null &&
        Number(row.cost) > this.filters.cost_to
      ) return false;

      if (this.filters.price_from !== null &&
        Number(row.price) < this.filters.price_from
      ) return false;

      if (this.filters.price_to !== null &&
        Number(row.price) > this.filters.price_to
      ) return false;

      if (this.filters.for_sale !== null &&
        row.for_sale !== this.filters.for_sale
      ) return false;

      return true;
    });
  },
   filteredSubcategories() {
      if (!this.filters.category) return [];

      return this.subcategories.filter(
         s => Number(s.category_id) === Number(this.filters.category)
      );
   },
},
   mounted() {
      this.fetchProducts();
      document.addEventListener('click', this.handleClickOutside);
     document.addEventListener('click', this.handleOutside)
   },
   beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
   methods: {
       openRemarksModal(productId) {
        // call the OLD global function
        window.openRemarksModal(productId);
    },
      openImportModal() {
    const modalEl = document.getElementById('importModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  },
      handleFileChange(e) {
    this.importFile = e.target.files[0]
    this.importFileName = this.importFile?.name || ''
    this.importVerified = false
    this.importPreview = []
    this.importRows = []

    if (!this.importFile) return

    const reader = new FileReader()
    reader.onload = (evt) => {
      const text = evt.target.result
      this.importPreview = text
  .split('\n')
  .filter(row => row.trim() !== '')
        .slice(0, 10) // preview first 10 rows
    }
    reader.readAsText(this.importFile)
  },

 // Upload & auto-display
handleFileUpload(event) {
  this.importFile = event.target.files[0]
  if (!this.importFile) return

  const reader = new FileReader()
  const isCSV = this.importFile.name.endsWith('.csv')

  reader.onload = async (e) => {
    let jsonData = []

    if (isCSV) {
      const text = e.target.result
      const delimiter = text.includes('\t') ? '\t' : ','
      const lines = text.trim().split(/\r?\n/)
      const headers = lines.shift()
        .split(delimiter)
        .map(h => h.trim().toLowerCase().replace(/\s+/g, '_'))

      jsonData = lines.map(line => {
        const values = line.split(delimiter)
        const obj = {}
        headers.forEach((h, i) => obj[h] = values[i] ? values[i].trim() : '')
        return obj
      })
    } else {
      const data = new Uint8Array(e.target.result)
      const workbook = XLSX.read(data, { type: 'array' })
      const sheetName = workbook.SheetNames[0]
      const worksheet = workbook.Sheets[sheetName]

      const rawData = XLSX.utils.sheet_to_json(worksheet, { defval: '' })
      jsonData = rawData.map(row => {
        const normalizedRow = {}
        Object.keys(row).forEach(key => {
          const newKey = key.trim().toLowerCase().replace(/\s+/g, '_')
          normalizedRow[newKey] = row[key]
        })
        return normalizedRow
      })
    }

    // Map and replace the reactive array entirely
    this.importRows = jsonData.map(row => ({
      code: row.sku || '',
      name: row.product_name || row.name || '',
      category: row.category ? { name: row.category } : null,
      subcategory: row.sub_category || row.subcategory ? { name: row.sub_category || row.subcategory } : null,
      quantity: Number(row.quantity || 1),
      price: Number(row.price || 0),
      unit: row.unit && row.unit.toString().toLowerCase() !== 'n/a' ? { name: row.unit } : { name: 'pcs' },
      status: 'pending',
      errors: []
    }))

    this.importVerified = false
  }

  if (isCSV) reader.readAsText(this.importFile)
  else reader.readAsArrayBuffer(this.importFile)
},

removeRow(index) {
    this.importRows.splice(index, 1)
  },

// Verify against database
async verifyImport() {
  if (!this.importRows.length) return

  Swal.fire({
    title: 'Verifying...',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  })

  try {
    const response = await fetch('/products/verify-import', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ rows: this.importRows })
    })

    const result = await response.json()   // ✅ DEFINE result HERE

    this.importRows = this.importRows.map((row, index) => {
      if (result.errors && result.errors[index]) {
        row.status = 'duplicate'
      } else {
        row.status = 'ready'
      }
      return row
    })

    this.importVerified = true   // ✅ enable submit logic

    Swal.close()

  } catch (error) {
    Swal.close()
    console.error(error)
    Swal.fire('Error', 'Verification failed.', 'error')
  }
},


  async submitImport() {

  const validRows = this.importRows.filter(r => r.status === 'ready')

  if (!validRows.length) {
    Swal.fire('No Data', 'There are no ready rows to import.', 'warning')
    return
  }

  const formData = new FormData()
  formData.append('rows', JSON.stringify(validRows))

  Swal.fire({
    title: 'Importing...',
    text: 'Please wait while we save your data.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading()
    }
  })

  try {
    const response = await fetch('/products/import', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: formData
    })

    const result = await response.json()

    Swal.close()

    if (result.success) {

      await Swal.fire({
        icon: 'success',
        title: 'Import Successful',
        text: 'Products have been imported successfully!'
      })

      // Optional: reset table
      this.importRows = []
      this.importVerified = false

    } else {
      Swal.fire('Error', result.message || 'Import failed.', 'error')
    }

  } catch (error) {
    Swal.close()
    Swal.fire('Error', 'Something went wrong during import.', 'error')
    console.error(error)
  }
},
  resetImport() {
    this.importFile = null
    this.importFileName = ''
    this.importPreview = []
    this.importVerified = false
    if (this.$refs.importFile) {
      this.$refs.importFile.value = null
    }
  },
      setStatus(status) {
         console.log("Status filter changed to:", status);
            this.statusFilter = status;
            this.fetchProducts();
        },
      getCellValue(row, field) {
    switch (field) {
      case 'product_sku':
        return row.code;

      case 'product_name':
        return row.name;

      case 'category':
        return row.category?.name || 'N/A';

      case 'subcategory':
        return row.subcategory?.name || 'N/A';

      case 'product_quantity':
        return Number(row.quantity || 0);

      case 'product_price':
        return Number(row.price || 0);

      case 'product_unit':
        return row.unit?.name || 'N/A ';

      default:
        return '';
    }
   },
     exportExcel() {
    if (!this.filteredRows.length) {
      alert('No data to export');
      return;
    }

    // only visible columns (gear dropdown)
    const visibleCols = this.columns.filter(col => !col.hidden && col.field !== 'action');

    const data = this.filteredRows.map(row => {
      const obj = {};

      visibleCols.forEach(col => {
        obj[col.label] = this.getCellValue(row, col.field);
      });

      return obj;
    });

    const worksheet = XLSX.utils.json_to_sheet(data);
    const workbook  = XLSX.utils.book_new();

    XLSX.utils.book_append_sheet(workbook, worksheet, 'Products');

    const filename = `products_${new Date().toISOString().slice(0, 10)}.xlsx`;

    XLSX.writeFile(workbook, filename);
  },
   goToPage(value) {
      const selected = this.types.find(t => t.value === value)
      if (selected) {
        window.location.href = selected.url
      }
    },
   openSidebar() {
      this.showFilterSidebar = true;
      console.log("Button clicked! showFilterSidebar =", this.showFilterSidebar);
    },
    closeSidebar() {
      this.showFilterSidebar = false;
      console.log("Closed sidebar:", this.showFilterSidebar);
    },
    buildCategoryLists() {
  const categoryMap = {};
  const subcategoryMap = {};

  this.rows.forEach(c => {
    if (c.category) {
      categoryMap[c.category.id] = c.category;
    }

    if (c.subcategory) {
      subcategoryMap[c.subcategory.id] = {
        ...c.subcategory,
        category_id: c.category?.id,
      };
    }
  });

  this.categories = Object.values(categoryMap);
  this.subcategories = Object.values(subcategoryMap);
},



  toggleDropdown() {
    this.showColumnDropdown = !this.showColumnDropdown;
  },

  handleClickOutside(event) {
    const dropdown = this.$refs.dropdownWrapper;
    if (dropdown && !dropdown.contains(event.target)) {
      this.showColumnDropdown = false;
    }
  },
  resetFilter() {
    this.filters = {
      name: '',
      category: '',
      subcategory: '',
      cost_from: null,
      cost_to: null,
      price_from: null,
      price_to: null,
      for_sale: null,
    };
  },

  toggleColumn(field) {
    const col = this.columns.find(c => c.field === field);
    if (col) col.hidden = !col.hidden;
  },

  isColumnVisible(field) {
    const col = this.columns.find(c => c.field === field);
    return col && !col.hidden;
  },

  applyFilter() {
  this.fetchProducts(1);
  this.showFilterSidebar = false;
},
    
    fetchProducts(page = 1) {
   this.loading = true;

   axios.get('/products/fetch', {
      params: {
         search: this.search,
         perPage: this.perPage,
         status: this.statusFilter,
         page: page,
         type: 'simple',

         // 🔹 filters
         category: this.filters.category,
         subcategory: this.filters.subcategory,
      }
   })
   .then(res => {
      // table rows
      this.rows = res.data.data;

      // pagination
      this.pagination.current_page = res.data.current_page;
      this.pagination.per_page    = res.data.per_page;
      this.pagination.total       = res.data.total;
      this.pagination.last_page   = res.data.last_page;

      // 🔹 build dropdown lists
      this.buildCategoryLists();
   })
   .finally(() => {
      this.loading = false;
   });
   },
   buildCategoryLists() {
  const categoryMap = {};
  const subcategoryMap = {};

  this.rows.forEach(c => {
    if (c.category) {
      categoryMap[c.category.id] = c.category;
    }

    if (c.subcategory) {
      subcategoryMap[c.subcategory.id] = {
        ...c.subcategory,
        category_id: c.category?.id,
      };
    }
  });

  this.categories = Object.values(categoryMap);
  this.subcategories = Object.values(subcategoryMap);
},
   },
   watch: {
  'filters.category'() {
    this.filters.subcategory = null;
  },
},
});
</script>
@endsection