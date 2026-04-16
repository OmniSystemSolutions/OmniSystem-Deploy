@extends('layouts.app')
@section('content')
<div class="main-content">
<style>
  .container {
    display: flex;
    gap: 20px;
}
/* Hide scrollbars by default */
.categories::-webkit-scrollbar,
.products::-webkit-scrollbar {
  width: 0; /* fully hidden */
  transition: width 0.5s;
}

/* Show scrollbars only on hover */
.categories:hover::-webkit-scrollbar,
.products:hover::-webkit-scrollbar {
  width: 6px;
}

/* Thumb and track (only visible when width > 0) */
.categories::-webkit-scrollbar-thumb,
.products::-webkit-scrollbar-thumb {
  background: #bbb;
  border-radius: 3px;
}

.categories::-webkit-scrollbar-track,
.products::-webkit-scrollbar-track {
  background: #f1f1f1;
}

/* Categories sidebar */
.categories {
    display: flex;
    flex-direction: column;   /* stack vertically */
    width: 150px;             /* fixed width so it won’t shrink */
    max-height: 700px;        /* set height for scroll area */
    overflow-y: auto;         /* vertical scroll */
    padding: 10px;
    gap: 10px;
    scrollbar-width: thin;    /* Firefox */
    flex-shrink: 0;           /* don’t allow shrinking */
}

.categories::-webkit-scrollbar {
    width: 6px;
}

.categories::-webkit-scrollbar-thumb {
    background: #bbb;
    border-radius: 3px;
}

.categories::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.categories button {
    flex: 0 0 auto;           /* prevent shrinking */
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    background: #ff630f;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s, color 0.3s;
    text-align: left;
    white-space: nowrap;      /* keep text on one line */
    overflow: hidden;
    text-overflow: ellipsis;  /* truncate if too long */
}

.categories button:hover,
.categories button.active {
    /* background: #007bff; */
    color: #fff;
}

/* Products grid */
.products {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(3, 1fr); /* ✅ 3 items per row */
  gap: 16px; /* balanced spacing between cards */
  margin-top: 20px;
  max-height: 700px;
  overflow-y: auto;
  padding: 10px;
  box-sizing: border-box;
}

/* Product card layout */
.product-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  overflow: hidden;
  height: 220px; /* ✅ consistent card height */
}

/* Hover effect */
.product-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Image inside product card */
.product-card img {
  width: 100%;
  height: 120px;
  object-fit: cover; /* ✅ prevent image distortion */
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
}

.product-body {
    padding: 15px;
    text-align: center;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product-body h4 {
    margin: 10px 0 5px;
    font-size: 18px;
    font-weight: bold;
}

.product-body p {
    font-size: 14px;
    color: #555;
    flex-grow: 1;
}

.product-body button {
    background: #007bff;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.3s;
}

.product-body button:hover {
    background: #0056b3;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table,
th,
td {
    border: 1px solid #ddd;
}

th,
td {
    padding: 8px;
    text-align: center;
}

input[type=number] {
    width: 60px;
}

.d-flex {
    margin: 10px 0;
    gap: 15px;
}

.fade-enter-active, .fade-leave-active {
  transition: all 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

.form-control:disabled, .form-control[readonly] {
    font-size: 14px !important;
}

.waiter-select .vs__search::placeholder {
  font-size: 14px !important;
}

</style>
<div id="app">
  <div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow">

        <div class="modal-header">
          <h5 class="modal-title">
            Order Note - @{{ selectedNoteItem.name }}
          </h5>
        </div>

        <div class="modal-body">
          <textarea
            class="form-control"
            rows="4"
            v-model="selectedNoteItem.notes"
            placeholder="Enter special instructions..."
          ></textarea>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" @click="closeNoteModal">
            Cancel
          </button>
          <button class="btn btn-primary" @click="saveNote">
            Save
          </button>
        </div>

      </div>
    </div>
  </div>


<h2>@{{ isEdit ? 'Edit Order' : 'Order Entry' }}</h2>
<form @submit.prevent="submitOrder">
<!-- Order Header -->
<div style="display:flex; gap:10px;">
<div class="order-header mb-3" style="flex:1;">
   <div class="card h-40">
        <div class="card-body">
          <div class="row g-3">
            <!-- Order No -->
            <div class="col-md-6">
              <label class="fw-bold">Order No:</label>
              <input
                type="text"
                class="form-control form-control-sm"
                :value="orderNo"
                readonly
              />
            </div>

            <!-- Date -->
            <div class="col-md-6">
              <label class="fw-bold">Date:</label>
              <input
                type="text"
                class="form-control form-control-sm"
                :value="date"
                readonly
              />
            </div>

            <!-- Table No. -->
            <div class="col-md-6">
              <label class="fw-bold">Table No:<span v-if="!tableNo" class="text-danger">*</span></label>
              <input 
                v-model="tableNo" 
                name="table_no"
                type="number" 
                min="1"
                class="form-control form-control-sm" 
                :style="{ backgroundColor: tableNo ? '#e5e7eb' : '#fff' }"
                required
                >
            </div>

            <!-- No. of Pax -->
            <div class="col-md-6">
              <label class="fw-bold">No. of Pax:<span v-if="!pax" class="text-danger">*</span></label>
              <input
                type="number"
                name="number_pax"
                min="1"
                v-model.number="pax"
                class="form-control form-control-sm"
                :style="{ backgroundColor: pax ? '#e5e7eb' : '#fff' }"
              />
            </div>

            <!-- Waiter -->
            <div class="col-md-6">
              <label class="fw-bold">Waiter:<span v-if="!selectedWaiter" class="text-danger">*</span></label>
              <v-select
                class="waiter-select"
                :options="waiters"
                v-model="selectedWaiter"
                label="name"
                :reduce="user => user.id"
                placeholder="Select Waiter"
                ref="waiterSelect"
              ></v-select>
            </div>

            <!-- Status -->
            <div class="col-md-6">
              <label class="fw-bold">Status:</label>
              <input
                name="status"
                type="text"
                class="form-control form-control-sm"
                value="serving"
                readonly
              />
            </div>
            
          </div>
        </div>
      </div>
   <!-- Order Details -->
   <h2 class=" mt-4">Order Details</h2>
   <div class="card">
      <nav class="card-header">
         <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
               <a href="#" target="_self" class="nav-link active">
               Menu
               </a>
            </li>
         </ul>
      </nav>
      <div class="card">
         <div class="card-body">
            <div class="vgt-wrap">
               <div class="vgt-inner-wrap">
                  <!-- Table -->
                  <div class="vgt-responsive" style="max-height: 400px; overflow-y: auto;">
                     <table id="order-details-table" class="table-hover tableOne vgt-table custom-vgt-table">
                        <thead>
                           <tr>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>SKU</span>
                              </th>
                              <th scope="col" class="vgt-left-align text-left sortable">
                                 <span>Product</span>
                              </th>
                              <th scope="col" class="vgt-left-align text-right sortable">
                                 <span>Qty</span>
                              </th>
                              <th scope="col" class="vgt-left-align text-right sortable">
                                 <span>Amount</span>
                              </th>
                              <th scope="col" class="vgt-left-align text-right sortable">
                                 <span>Total</span>
                              </th>
                              <th scope="col" class="vgt-left-align text-right sortable">
                                 <span>Status</span>
                              </th>
                              <th scope="col" class="vgt-left-align text-right sortable">
                                 <span>Note</span>
                              </th>
                              <th scope="col" class="vgt-left-align text-right sortable">
                                 <span>Option</span>
                              </th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr v-for="(item, index) in orderDetails" :key="item.id">
                                <td>@{{ item.sku }}</td>
                                <td>@{{ item.name }}</td>

                                <input 
                                    type="hidden" 
                                    :name="`order_details[${index}][product_id]`" 
                                    :value="item.id"
                                >

                                <td>
  <div class="input-group input-group-sm" style="width: 120px;">
    <div class="input-group-prepend">
      <button
        type="button"
        class="btn btn-primary"
        @click="item.qty = Math.max(1, item.qty - 1)"
        :disabled="item.status === 'served' || item.status === 'walked'"
      >
        -
      </button>
    </div>

    <input
      type="number"
      step="0.01"
      min="1"
      class="form-control form-control-sm text-center"
      v-model.number="item.qty"
      :name="`order_details[${index}][quantity]`"
      :disabled="item.status === 'served' || item.status === 'walked'"
    >

    <div class="input-group-append">
      <button
        type="button"
        class="btn btn-primary"
        @click="item.qty++"
       :disabled="item.status === 'served' || item.status === 'walked'"
      >
        +
      </button>
    </div>
  </div>
</td>


                                <td>@{{ item.price.toFixed(2) }}</td>
                                <input 
                                    type="hidden" 
                                    :name="`order_details[${index}][price]`" 
                                    :value="item.price"
                                >

                                <td>@{{ (item.qty * item.price).toFixed(2) }}</td>

                                <td>@{{ item.status }}</td>
                                <td class="text-center">

    <!-- Edit Note Icon -->
    <i 
        class="fa-regular fa-pen-to-square me-1"
        :class="item.notes ? 'text-danger' : 'text-primary'"
        style="cursor:pointer;"
        @click="openNoteModal(item)"
    ></i>

    <!-- Show Info Icon ONLY if notes exist -->
        <i 
        v-if="item.notes && item.notes.trim() !== ''"
        class="fa-solid fa-circle-info text-info"
        :title="item.notes"
    ></i>

                                <!-- ✅ Remove Button Column -->
                                <td class="text-center">
                                  <button 
                                    type="button" 
                                    class="btn btn-sm btn-danger"
                                    @click="removeItem(index)"
                                  >
                                    <i class="fa fa-trash"></i> Remove
                                  </button>
                                </td>
                                </tr>
                           <tr v-if="orderDetails.length === 0">
                              <td colspan="8" class="vgt-center-align vgt-text-disabled">
                                 No order details available
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <!-- Footer -->
                  <div class="vgt-wrap__footer vgt-clearfix">
                     <div class="footer__row-count vgt-pull-left">
                        <span>Total Items: @{{ orderDetails.length }}</span>
                     </div>
                     <div class="footer__navigation vgt-pull-right">
                        <span class="font-weight-bold">
                          Grand Total: @{{ grandTotal }}
                        </span>
                     </div>
                  </div>
                  <button type="submit" class="primary-btn btn btn-primary mt-3">Submit Order</button>
               </div>
            </div>
        </form>
         </div>
      </div>
   </div>
</div>
<div class="card h-100" style="flex:1;">
<div class="card-body">
<div class="autocomplete product-search-box" style="position: relative; width: 100%; max-width: 400px;">
  <input 
    v-model="searchQuery"
    placeholder="Scan/Search Product by Code Or Name"
    class="autocomplete-input"
    style="width: 100%; padding-right: 35px;"
  >
  <button 
    v-if="searchQuery" 
    @click="clearSearch" 
    type="button" 
    class="btn search-product-clear-btn btn-link"
    style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); padding: 0; border: none;"
  >
    <i class="i-Close"></i>
  </button>
</div>
<div class="container" style="margin-left: 0;">
  <!-- Categories -->
  <div 
  class="categories" 
  v-if="categories && categories.length > 0 && !searchQuery.trim()" 
  style="padding-left: 0;"
>
  <div 
    v-for="category in categories" 
    :key="category.id"
    v-if="category.subcategories && category.subcategories.some(sub => sub.products?.length > 0 || sub.components?.length > 0)"
    class="mb-2"
  >
    <!-- Category Button -->
    <button 
      class="btn w-100 text-start"
      :class="{ 
        'btn-primary': selectedCategory?.id === category.id, 
        'btn-primary': selectedCategory?.id !== category.id 
      }"
      @click.prevent="toggleCategory(category)"
    >
      @{{ category.name }}
    </button>

    <!-- Collapsible Subcategories -->
    <transition name="fade">
      <div 
        v-if="expandedCategory === category.id" 
        class="ps-4 mt-2"
      >
<div class="subcategory-menu p-2">
  <div class="d-flex flex-column">
    <button
      v-for="sub in category.subcategories"
      :key="sub.id"
      v-if="sub.products?.length > 0 || sub.components?.length > 0"
      class="btn btn-outline-secondary btn-sm text-start mb-1"
      :class="{ 'btn-secondary': selectedSubcategory?.id === sub.id }"
      @click.prevent="selectSubcategory(sub)"
    >
      @{{ sub.name }}
    </button>
  </div>
</div>

      </div>
    </transition>
  </div>

</div>

  <!-- Products -->
  <div class="products">
  <div 
    class="product-card" 
    v-for="p in filteredProducts" 
    :key="p.type + '-' + p.id"
    @click="addToOrder(p)"
    style="cursor: pointer;"
  >
    <img :src="p.image" :alt="p.name">
    <div class="product-body">
      <p style="font-weight:800">@{{ p.name }}</p>
      <p>@{{ p.description }}</p>
    </div>
  </div>
</div>

</div>

</div>
</div>
</div>
<script>
   // Register vue-select globally
   Vue.component('v-select', VueSelect.VueSelect)
   new Vue({
   el: "#app",
   data: {
    selectedNoteItem: {},
noteModal: null,
       orderNo: @json($nextOrderNo ?? null),
       date: new Date().toLocaleString(),
       waiter: null,
       waiters: @json($waiters),
       selectedWaiter: null,
       pax: null,
       tableNo: null,
       categories: @json($categories),
       selectedCategory: null,
       selectedSubcategory: null,
       expandedCategory: null, // for collapse tracking
       products: @json($products),
       orderDetails: [],
       searchQuery: "",
       isEdit: {{ isset($order) ? 'true' : 'false' }},
       order: @json($order ?? null),
   },
mounted() {
  this.noteModal = new bootstrap.Modal(
    document.getElementById('noteModal')
);

    console.log('edit mounted — raw order from blade/api:', this.order);

    const params = new URLSearchParams(window.location.search);
    const typeFromUrl = params.get('type');
    if (typeFromUrl) {
      this.orderType = typeFromUrl;
    }
    const tableNoFromUrl = params.get('table_no');
    if (tableNoFromUrl) {
      this.tableNo = parseInt(tableNoFromUrl);
    }

    if (this.isEdit && this.order) {
      this.orderNo = this.order.id;
        this.date = new Date(this.order.created_at).toLocaleString();
          // normalize waiter IDs
          this.waiters = (this.waiters || []).map(w => ({ ...w, id: Number(w.id) }));

          const waiterId = Number(this.order.user_id);
          this.$nextTick(() => {
            this.selectedWaiter = waiterId;
            this.pax = this.order.number_pax;
            this.tableNo = this.order.table_no;

            this.orderDetails = this.order.details.map(detail => {
  const item = detail.product ?? detail.component;
  return {
    detail_id: detail.id,
    id: item.id,
    type: detail.product_id ? 'product' : 'component',
    sku: item.code,
    name: item.name,
    price: parseFloat(detail.price),
    qty: detail.quantity,
    status: detail.status,
    notes: detail.notes ?? ''  // ✅ match backend field
  };
});

            console.log("✅ Fields prefilled:", {
              selectedWaiter: this.selectedWaiter,
              pax: this.pax,
              tableNo: this.tableNo,
              orderDetails: this.orderDetails
            });
          });
      }
},

   computed: {
    grandTotal() {
    return this.orderDetails
      .filter(item => item.status !== 'walked')
      .reduce((sum, item) => sum + (item.qty * item.price), 0)
      .toFixed(2);
  },
    sortedCategories() {
    return [...this.categories].sort((a, b) => a.name.localeCompare(b.name));
  },
     filteredProducts() {
    // 🔸 Search filter first
    if (this.searchQuery.trim() !== "") {
      const q = this.searchQuery.toLowerCase();
      return this.products.filter(p =>
        p.name.toLowerCase().includes(q) ||
        p.sku.toLowerCase().includes(q) ||
        p.description.toLowerCase().includes(q)
      ).sort((a, b) => a.name.localeCompare(b.name));
    }

    // 🔸 Filter by selected subcategory or category
    let items = [...this.products];
    if (this.selectedSubcategory) {
      items = items.filter(p => p.subcategory_id === this.selectedSubcategory.id);
    } else if (this.selectedCategory) {
      items = items.filter(p => p.category_id === this.selectedCategory.id);
    }

    // 🔸 Sort A-Z
    return items.sort((a, b) => a.name.localeCompare(b.name));
  },
  isFormValid() {
    return this.selectedWaiter && this.pax && this.tableNo;
  },
        filteredSearch() {
    if (!this.searchQuery) return [];
    let q = this.searchQuery.toLowerCase();
      return this.products.filter(
        p => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
      );
    },
   },
   methods: {
    openNoteModal(item) {
    this.selectedNoteItem = { ...item }; // clone

    // 🟢 If no note yet → just open modal
    if (!this.selectedNoteItem.notes || this.selectedNoteItem.notes.trim() === '') {
        this.selectedNoteItem.notes = '';
        this.noteModal.show();
        return;
    }

    // 🔵 If note exists → fetch latest from backend
    axios.get(`/order-details/${this.selectedNoteItem.detail_id}/note`)
        .then(res => {
            this.$set(this.selectedNoteItem, 'notes', res.data.notes ?? '');
            this.noteModal.show();
        })
        .catch(() => {
            // fallback open modal even if request fails
            this.noteModal.show();
        });
},
saveNote() {

    // 🟢 Case 1: New item (no detail_id yet)
    if (!this.selectedNoteItem.detail_id) {

        const index = this.orderDetails.findIndex(
            item => item.key === this.selectedNoteItem.key
        );

        if (index !== -1) {
            this.$set(this.orderDetails[index], 'notes', this.selectedNoteItem.notes);
        }

        this.noteModal.hide();
        return;
    }

    // 🔵 Case 2: Existing DB item
    axios.post(`/order-details/${this.selectedNoteItem.detail_id}/note`, {
        notes: this.selectedNoteItem.notes
    }).then(() => {

        const index = this.orderDetails.findIndex(
            item => item.detail_id === this.selectedNoteItem.detail_id
        );

        if (index !== -1) {
            this.$set(this.orderDetails[index], 'notes', this.selectedNoteItem.notes);
        }

        this.noteModal.hide();
    });
},

closeNoteModal() {
    this.noteModal.hide();
},

// saveNote() {
//     this.noteModal.hide();
// },

    sortedSubcategories(subs) {
  return [...subs].sort((a, b) => a.name.localeCompare(b.name));
},
selectCategory(category) {
  this.selectedCategory = category;
  this.selectedSubcategory = null; // 🔸 reset subcategory when changing category
},
toggleCategory(category) {
    // Expand/collapse logic
    this.expandedCategory = 
      this.expandedCategory === category.id ? null : category.id;
    this.selectedCategory = category;
    this.selectedSubcategory = null; // reset subcategory
  },
  selectSubcategory(sub) {
    this.selectedSubcategory = sub;
  },
       addToOrder(product) {
    // Reset validation states
    this.invalidWaiter = false;
    this.invalidPax = false;
    this.invalidTable = false;

     // Validate waiter
    if (!this.selectedWaiter) {
      this.invalidWaiter = true;
      this.$nextTick(() => {
        // Auto focus + open dropdown
        const waiterSelect = this.$refs.waiterSelect;
        if (waiterSelect) {
          waiterSelect.$el.querySelector('input')?.focus();
          waiterSelect.toggleDropdown?.(); // open the dropdown
        }
      });
      return;
    }
    if (!this.pax) {
      this.invalidPax = true;
      this.$nextTick(() => {
        document.querySelector('[name="number_pax"]')?.focus();
      });
      return;
    }
    if (!this.tableNo) {
      this.invalidTable = true;
      this.$nextTick(() => {
        document.querySelector('[name="table_no"]')?.focus();
      });
      return;
    }

    const key = product.type + '-' + product.id;

  const existing = this.orderDetails.find(
    item => item.key === key
  );

  if (existing) {
    existing.qty++;
  } else {
    this.orderDetails.push({
      key: key,                  // ✅ internal unique key
      id: product.id,
      type: product.type,        // "product" or "component"
      sku: product.sku,
      name: product.name,
      qty: 1,
      price: parseFloat(product.price),
      status: 'serving',
      notes: ''
    });
  }
  },
  clearSearch() {
    this.searchQuery = "";
  },
   selectCategory(category) {
    this.selectedCategory = category;
  },
  removeItem(index) {
    this.orderDetails.splice(index, 1);
  },
  submitOrder() {
  const now = new Date();
  const timeSubmitted = now.getFullYear() + '-' +
  String(now.getMonth() + 1).padStart(2, '0') + '-' +
  String(now.getDate()).padStart(2, '0') + ' ' +
  now.toLocaleTimeString('en-US', { hour12: false }); // 24-hour with seconds

  // 🧮 Compute Gross Amount
  const grossAmount = this.orderDetails
    .reduce((sum, item) => sum + (item.qty * item.price), 0)
    .toFixed(2);

  const payload = {
  user_id: this.selectedWaiter, // waiter ID
  table_no: parseInt(this.tableNo),
  number_pax: parseInt(this.pax),
  status: "serving",
  time_submitted: timeSubmitted,
  order_type: this.orderType,
  gross_amount: parseFloat(grossAmount),
  order_details: this.orderDetails.map(item => ({
    detail_id: item.detail_id  || null,
    product_id: item.type === "product" ? item.id : null,
    component_id: item.type === "component" ? item.id : null,
    quantity: item.qty,
    price: item.price,
    status: item.status || 'serving',
    notes: item.notes
  }))
};

  console.log("Submitting order payload:", payload);

  // ✅ Dynamic URL — depends on mode
  const url = this.isEdit
    ? `/orders/update/${this.order.id}`
    : '/orders/store';

  axios.post(url, payload)
  .then(res => {
    console.log("✅ Server response:", res.data);

    // Show loading first
    Swal.fire({
      title: 'Saving...',
      text: 'Please wait',
      didOpen: () => {
        Swal.showLoading();
      },
      allowOutsideClick: false
    });

    // After a short delay, show success alert
    setTimeout(() => {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: res.data.message,
        confirmButtonText: 'OK'
      }).then(() => {
        // ⬅️ Redirect here AFTER clicking OK
        window.location.href = res.data.redirect;
      });
    }, 800); // delay to let loading state show
  })
  .catch(err => {
    console.error("❌ Error saving order:", err.response?.data || err);

    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: "Error saving order. Check console for details.",
      confirmButtonText: 'OK'
    });
  });

},
   },
   watch: {
  searchQuery(newVal, oldVal) {
  }
}
   });
   
</script>
@endsection