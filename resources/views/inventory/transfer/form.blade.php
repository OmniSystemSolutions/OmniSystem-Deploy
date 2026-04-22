@extends('layouts.app')
@section('content')
<style>
   .sortable { user-select: none; }
   .sortable span { display: inline-flex; align-items: center; gap: 4px; }
</style>
<div class="main-content" id="app">
   <div>
      <div class="breadcrumb">
         <h1 class="mr-3">
            @{{ formTitle }}
         </h1>
         <ul>
            <li>
               <a href="">
               @{{ breadcrumbText }}
               </a>
            </li>
         </ul>
         <div class="breadcrumb-action"></div>
      </div>
      <div class="separator-breadcrumb border-top"></div>
   </div>
   <div class="wrapper">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <!-- Entry Date/Time -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Date and Time of Request</legend>
                     <div class="d-flex align-items-center">
                        <input type="datetime-local"
                           class="form-control"
                           v-model="form.requested_datetime"
                           readonly/>
                        {{-- <button type="button" class="btn ml-2 btn-secondary btn-sm" @click="clearDate">Clear</button> --}}
                     </div>
                  </fieldset>
               </div>
               <!-- Destination -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend class="col-form-label pt-0">@{{ branchLabel }}</legend>
                     <v-select
                        v-model="selectedBranch"
                        :options="branchesOptions"
                        :clearable="false"
                        placeholder="Select branch"
                        label="label">
                     </v-select>
                  </fieldset>
               </div>
               <!-- Reference Number -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Reference # *</legend>
                     <input type="text" class="form-control" v-model="referenceNo" readonly>
                  </fieldset>
               </div>
               <!-- Type -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Type</legend>
                     <v-select v-model="selectedType" :options="typeOptions" :clearable="true" placeholder="Select type" label="label"></v-select>
                  </fieldset>
               </div>
               <!-- Products Table -->
               <div class="col-sm-12">
                  <div class="list-group mt-2">
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold">List of Items to Send</h6>
                        <button type="button" class="btn btn-primary btn-sm">Filter</button>
                     </div>
                     <div class="list-group-item">
                        <table class="table-hover tableOne vgt-table">
                           <thead>
                              <tr>
                                 <th><input type="checkbox" v-model="selectAll" @change="toggleAll"></th>
                                 <th @click="sortTable('code')" class="sortable">SKU <i :class="sortIcon('code')"></i></th>
                                 <th @click="sortTable('name')" class="sortable">Name <i :class="sortIcon('name')"></i></th>
                                 <th @click="sortTable('category')" class="sortable">Category <i :class="sortIcon('category')"></i></th>
                                 <th @click="sortTable('onhand')" class="sortable">Quantity on Hand <i :class="sortIcon('onhand')"></i></th>
                                 <th @click="sortTable('unit')" class="sortable">Unit <i :class="sortIcon('unit')"></i></th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="item in sortedData" :key="item.id">
                                 <td><input
                                    type="checkbox"
                                    v-model="selections[selectedType.value]"
                                    :value="item.id"
                                    ></td>
                                 <td>@{{ item.code }}</td>
                                 <td>@{{ item.name }}</td>
                                 <td>@{{ item.category ? item.category.name : 'N/A' }}</td>
                                 <td>@{{ item.onhand }}</td>
                                 <td>@{{ item.unit }}</td>
                              </tr>
                              <tr v-if="!items.length">
                                 <td colspan="6" class="text-center text-muted">No items found</td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               <!-- Selected Products Quantity -->
               <div class="col-sm-12">
                  <div class="list-group mt-4">
                     <div class="list-group-item">
                        <h6 class="mb-0 font-weight-bold">Edit Quantity to Send</h6>
                     </div>
                     <div class="list-group-item">
                        <table class="table-hover tableOne vgt-table">
                           <thead>
                              <tr>
                                 <th>Name</th>
                                 <th>SKU(Product Code)</th>
                                 <th>Category</th>
                                 <th>Quantity on Hand</th>
                                 <th>Unit</th>
                                 <th>Enter Quantity of Item Here</th>
                                 <th class="text-right">Action</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-if="selectedItems.length === 0">
                                 <td colspan="6" class="text-center">No Selected Items</td>
                              </tr>
                              <tr v-for="item in selectedItems" :key="item.id">
                                 <td>@{{ item.name }}</td>
                                 <td>@{{ item.code }}</td>
                                 <td>@{{ item.category ? item.category.name : 'N/A' }}</td>
                                 <td>@{{ item.onhand }}</td>
                                 <td>@{{ item.unit }}</td>
                                 <td>
                                    <div style="width: 200px;">
                                       <div role="group" class="input-group input-group-sm">
                                          <div class="input-group-prepend">
                                             <button type="button" class="btn btn-primary" @click="decrementQuantity(item)">-</button>
                                          </div>
                                          <input
                                             type="number"
                                             class="form-control"
                                             :value="quantities[item._type][item.id] ?? 0"
                                             min="0"
                                             :max="item.onhand"
                                             step="0.01"
                                             @input="onQuantityInput($event, item)"
                                             >
                                          <div class="input-group-append">
                                             <button type="button" class="btn btn-primary" @click="incrementQuantity(item)">+</button>
                                          </div>
                                       </div>
                                    </div>
                                 </td>
                                 <td class="vgt-left-align text-right">
                                    <div role="group" class="btn-group btn-group-sm">
                                       <button type="button" class="btn btn-danger" @click="removeItem(item)">Remove</button>
                                    </div>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               <!-- Buttons -->
               <div class="mt-3 col-md-12">
                  <div class="d-flex mt-4">
                     <button type="button" class="btn btn-primary mr-2" @click="submitForm">
                     <i class="i-Yes me-2 font-weight-bold"></i> Submit
                     </button>
                     <a href="/inventory/transfer" class="btn btn-outline-secondary">Cancel</a>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   Vue.component('v-select', VueSelect.VueSelect);
   
   new Vue({
       el: '#app',
   
       data() {
           return {
               mode: '{{ $mode }}',
               transferType: '{{ $transferType }}',
               referenceNo: @json($referenceNo),
               transfer: @json($transfer ?? null),
               branchesOptions: @json(
                   $branches->map(fn ($b) => [
                       'label' => $b->name,
                       'value' => $b->id
                   ])
               ),
               selectedBranch: null,
               allItems: {
                   products: [],
                   components: []
               },
               quantities: {
                   products: {},
                   components: {}
               },
               selections: {
                   products: [],
                   components: []
               },
               items: [], 
               selectAll: false,
               selectedType: { label: 'Components', value: 'components' },
               typeOptions: [
                   { label: 'Products', value: 'products' },
                   { label: 'Components', value: 'components' },
               ],
               form: {
                   id: @json($transfer->id ?? null),
                   reference_no: '{{ $referenceNo ?? '' }}',
                   requested_datetime: @json(
                        $transfer->requested_datetime
                        ?? now()->timezone('Asia/Manila')->format('Y-m-d\TH:i')
                    ),
                   destination_id: this.transferType === 'send' ? @json($transfer->destination_id ?? null) : null,
                   source_id: this.transferType === 'request' ? @json($transfer->source_id ?? null) : null,
                   attached_file: null,
               },
               currentSort: '',
               currentSortDir: 'asc',
               isSubmitting: false,
           }
       },
       mounted() {
        // Load branches (and maybe other items)
        this.loadItems().then(() => {

            if (this.form.requested_datetime) {
                this.form.requested_datetime = this.formatUtcToDatetimeLocal(this.form.requested_datetime);
            }
            // Pre-select type in edit mode
            if (this.mode === 'edit' && this.transfer) {
                this.selectedType = this.transfer.type === 'components'
                    ? { label: 'Components', value: 'components' }
                    : { label: 'Products', value: 'products' };
            }

            if (this.mode === 'edit' && this.transfer?.items) {
                this.transfer.items.forEach(item => {
                    const type = item.product_id ? 'products' : 'components';
                    const id = item.product_id ?? item.component_id;

                    // Add to selections
                    if (!this.selections[type].includes(id)) {
                        this.selections[type].push(id);
                    }

                    // Set quantity
                    this.$set(this.quantities[type], id, item.quantity);
                });
            }

            // -----------------------
            // Pre-select branch
            // -----------------------
           if (this.mode === 'edit' && this.transfer) {
            if (this.transferType === 'send') {
                this.form.destination_id = this.transfer.destination_id;

                this.selectedBranch = this.branchesOptions.find(
                    b => b.value == this.transfer.destination_id
                );
            }

            if (this.transferType === 'request') {
                this.form.source_id = this.transfer.source_id;

                this.selectedBranch = this.branchesOptions.find(
                    b => b.value == this.transfer.source_id
                );
            }
        }

            // Auto-select if only one option
            if (this.branchesOptions.length === 1) {
                this.selectedBranch = this.branchesOptions[0];
            }
        });
        console.log('Requested datetime:', this.transfer?.requested_datetime ?? null);

    },

       computed: {
           formTitle() {
               return `${this.mode === 'edit' ? 'Edit' : 'Create'} ${this.transferType.charAt(0).toUpperCase() + this.transferType.slice(1)} Transfer`
           },
           breadcrumbText() {
               return `${this.mode === 'edit' ? 'Edit' : 'Create'} Transfer ${this.capitalize(this.transferType)} Form`;
           },
           branchLabel() {
                return this.transferType === 'request' ? 'Source' : 'Destination';
            },
           formattedNow() { 
               return new Date(this.currentDateTime).toLocaleString(); 
           },
           sortedData() {
               if (!this.currentSort) return this.items;
               return [...this.items].sort((a,b)=> {
                   let modifier = this.currentSortDir==='asc'?1:-1;
                   let valA = a[this.currentSort], valB = b[this.currentSort];
                   if(valA && typeof valA==='object') valA = valA.name;
                   if(valB && typeof valB==='object') valB = valB.name;
                   valA = valA ? valA.toString().toLowerCase() : '';
                   valB = valB ? valB.toString().toLowerCase() : '';
                   return valA<valB?-1*modifier: valA>valB?1*modifier:0;
               });
           },
           selectedItems() {
                const merged = [];
                
                ['products', 'components'].forEach(type => {
                    this.selections[type].forEach(id => {
                        const item = this.allItems[type].find(i => i.id === id);
                        if (!item) return;
                
                        merged.push({
                            ...item,
                            _type: type,
                            quantity: this.quantities[type][id] || 0
                        });
                    });
                });
                
                return merged;
            }
       },
   
       methods: {
           capitalize(text) {
               if (!text) return '';
               return text.charAt(0).toUpperCase() + text.slice(1);
           },
   
           handleFile(e) {
               this.form.attached_file = e.target.files[0];
           },
   
           sortTable(col){ 
               if(this.currentSort===col) this.currentSortDir=this.currentSortDir==='asc'?'desc':'asc'; 
               else { this.currentSort=col; this.currentSortDir='asc'; } 
           },
   
           sortIcon(col){ 
               return ['fa', this.currentSort===col?(this.currentSortDir==='asc'?'fa-sort-up':'fa-sort-down'):'fa-sort']; 
           },
   
           // ----------------- Selection -----------------
           toggleAll() { 
               this.selected = this.selectAll ? this.items.map(i => i.id) : []; 
           },
   
           // ----------------- Quantity -----------------
           incrementQuantity(item) {
               const type = item._type;
               const max = Number(item.onhand) || 0;
   
               let value = this.quantities[type][item.id] ?? 0;
   
               if (value >= max) return;
   
               value = +(value + 1).toFixed(2);
   
               this.$set(this.quantities[type], item.id, value);
           },

           formatUtcToDatetimeLocal(utcString) {
                if (!utcString) return null;

                const d = new Date(utcString);

                // Manila is UTC+8
                const offset = 8 * 60; // minutes
                d.setMinutes(d.getMinutes() + d.getTimezoneOffset() + offset);

                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                const hh = String(d.getHours()).padStart(2, '0');
                const min = String(d.getMinutes()).padStart(2, '0');

                return `${yyyy}-${mm}-${dd}T${hh}:${min}`;
            },
   
           decrementQuantity(item) {
               const type = item._type;
   
               let value = this.quantities[type][item.id] ?? 0;
   
               if (value <= 0) return;
   
               value = +(value - 1).toFixed(2);
               if (value < 0) value = 0;
   
               this.$set(this.quantities[type], item.id, value);
           },
   
           removeItem(item) {
               const type = item._type;
   
               this.selections[type] = this.selections[type].filter(
                   id => id !== item.id
               );
   
               delete this.quantities[type][item.id];
           },
   
           clampQuantity(item) {
               const max = Number(item.onhand) || 0;
   
               if (item.quantity < 0) item.quantity = 0;
               if (item.quantity > max) item.quantity = max;
   
               this.quantities[item._type][item.id] = item.quantity;
           },
   
           onQuantityInput(e, item) {
               let value = Number(e.target.value);
   
               if (isNaN(value)) value = 0;
   
               const max = Number(item.onhand) || 0;
   
               if (value > max) value = max;
               if (value < 0) value = 0;
   
               if (Number(e.target.value) > max) {
                   Swal.fire('Limit reached', 'Cannot exceed available stock', 'warning');
               }
   
   
               // update immediately
               item.quantity = value;
               this.quantities[item._type][item.id] = value;
   
               console.log('input', item.quantity)
   
               // force DOM sync (important for fast typing)
               e.target.value = value;
           },
   
          // ----------------- Load Items -----------------
        async loadItems() {
            const type = this.selectedType.value; // ✅ MUST BE FIRST
            
            
            try {
                const response = await axios.get('/inventory/transfer/items/fetch', {
                    params: { type }
                });
            
                console.log('✅ Axios response:', response);
                console.log('📄 Response data:', response.data);
            
                if (!response.data || !response.data.items) {
                    console.warn('⚠️ No items returned from backend');
                    return;
                }
            
                this.items = response.data.items.map(item => ({
                    ...item,
                    _type: type, // ✅ now safe
                    quantity: Math.min(
                        this.quantities[type][item.id] || 0,
                        item.onhand ?? 0
                    )
                }));
            
                // ✅ cache items per type
                this.allItems[type] = this.items;
            
                console.log('📋 Initialized items:', this.items);
            
            } catch (e) {
                console.error('❌ Failed to fetch items', e);
                if (e.response) {
                    console.error('📡 Axios response error:', e.response.data);
                    console.error('📡 Status:', e.response.status);
                }
                Swal.fire('Error', 'Failed to load items', 'error');
            }
        },
        submitForm() {

    // -------------------------
    // FRONTEND GUARDS
    // -------------------------
    if (!this.selectedBranch) {
        return Swal.fire('Branch required', 'Please select a branch.', 'warning');
    }

    if (!this.selectedItems.length) {
        return Swal.fire('No items selected', 'Please select at least one item.', 'warning');
    }

    if (this.transferType === 'send' && !this.form.destination_id) {
        return Swal.fire('Error', 'Please select a destination branch', 'error');
    }

    if (this.transferType === 'request' && !this.form.source_id) {
        return Swal.fire('Error', 'Please select a source branch', 'error');
    }

    // -------------------------
    // BUILD FORM DATA
    // -------------------------
    const formData = new FormData();

    Object.keys(this.form).forEach(key => {
        if (
            this.form[key] !== null &&
            key !== 'source_id' &&
            key !== 'destination_id'
        ) {
            formData.append(key, this.form[key]);
        }
    });

    formData.append('transfer_type', this.transferType);

    // ✅ Branch logic (AFTER validation)
    if (this.transferType === 'send') {
        formData.append('destination_id', this.form.destination_id);
    } else {
        formData.append('source_id', this.form.source_id);
    }

    // -------------------------
    // ITEMS
    // -------------------------
    this.selectedItems.forEach((item, index) => {
        if (item._type === 'products') {
            formData.append(`items[${index}][product_id]`, item.id);
        } else {
            formData.append(`items[${index}][component_id]`, item.id);
        }

        formData.append(`items[${index}][quantity]`, item.quantity);
    });

    console.log('Submitting:', [...formData.entries()]);

    // -------------------------
    // SUBMIT
    // -------------------------
    const url = this.mode === 'edit'
        ? `/inventory/transfer/${this.form.id}`
        : `/inventory/transfer`;

    if (this.mode === 'edit') {
        formData.append('_method', 'PUT');
    }

    Swal.fire({
        title: 'Saving transfer...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    axios.post(url, formData)
        .then(() => {
            Swal.fire('Success', 'Transfer saved successfully', 'success')
                .then(() => window.location.href = '/inventory/transfer');
        })
        .catch(error => {
            Swal.fire(
                'Error',
                error.response?.data?.message ?? 'Something went wrong',
                'error'
            );
        });
}

    },
       watch: {
    selectedType(newVal) {
        if (newVal) this.loadItems();
    },

    branchesOptions: {
        immediate: true,
        handler(newOptions) {
            if (this.mode !== 'edit') return;

            let branchId = null;

            if (this.transferType === 'send') {
                branchId = this.form.destination_id;
            } else if (this.transferType === 'request') {
                branchId = this.form.source_id;
            }

            if (!branchId) return;

            const match = newOptions.find(b => b.value == branchId);
            if (match) {
                this.selectedBranch = match;
            }
        }
    },

    // 🔗 Sync selected branch → form
    selectedBranch(newVal) {
        if (!newVal) {
            this.form.destination_id = null;
            this.form.source_id = null;
            return;
        }

        if (this.transferType === 'send') {
            this.form.destination_id = newVal.value;
            this.form.source_id = null;
        }

        if (this.transferType === 'request') {
            this.form.source_id = newVal.value;
            this.form.destination_id = null; // backend sets this
        }
    }
}

   });
</script>
@endsection