@extends('layouts.app')
@section('content')
<style>
    .list-group-item .vs__search {
    font-size: 14px;
}
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
                     </div>
                  </fieldset>
               </div>
               <!-- Requested By -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend class="col-form-label pt-0">Requested By</legend>
                     <v-select
                        v-model="form.selectedRequestor"
                        :options="requestorOptions"
                        :clearable="false"
                        placeholder="Select User"
                        label="label">
                     </v-select>
                  </fieldset>
               </div>
               <!-- Type -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Type</legend>
                     <v-select v-model="form.selectedType" :options="typeOptions" :clearable="true" placeholder="Select type" label="label"></v-select>
                  </fieldset>
               </div>
               <!-- Reference Number -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Reference # *</legend>
                     <input type="text" class="form-control" v-model="form.referenceNo" readonly>
                  </fieldset>
               </div>
               <!-- Requesting Branch -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend class="col-form-label pt-0">Requesting Branch</legend>
                     <v-select
                        v-model="form.selectedBranch"
                        :options="branchesOptions"
                        :clearable="false"
                        placeholder="Select Branch"
                        label="label">
                     </v-select>
                  </fieldset>
               </div>
               {{-- Origin --}}
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Origin</legend>
                     <v-select v-model="form.selectedOrigin" :options="originOptions" :clearable="true" label="label"></v-select>
                  </fieldset>
               </div>
               {{-- Proforma Reference # --}}
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Proforma Reference #</legend>
                     <div class="d-flex align-items-center">
                        <input type="text"
                           class="form-control"
                           v-model="form.proformaReferenceNo"
                           />
                     </div>
                  </fieldset>
               </div>
               <!-- Products Table -->
               <div class="col-sm-12">
                  <div class="list-group mt-2">
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold">List of Items to Send</h6>
                        <div class="col-sm-6 col-md-6 col-lg-3">
                            <fieldset class="form-group">
                                <v-select
                                 v-model="form.selectedSubType"
                                 :options="subtypeOptions"
                                 :clearable="true"
                                 label="label"
                                 placeholder="Select Sub Type"
                              >
                                 <template #no-options>
                                    <span>No data found for this type</span>
                                 </template>
                              </v-select>
                            </fieldset>
                        </div>
                     </div>
                     <div class="list-group-item">
                        <table class="table-hover tableOne vgt-table">
                           <thead>
                              <tr>
                                 <th><input type="checkbox" v-model="selectAll" @change="toggleAll"></th>
                                 <th @click="sortTable('code')" class="sortable">SKU <i :class="sortIcon('code')"></i></th>
                                 <th @click="sortTable('name')" class="sortable">Name <i :class="sortIcon('name')"></i></th>
                                 <th @click="sortTable('category')" class="sortable">Category <i :class="sortIcon('category')"></i></th>
                                 <th @click="sortTable('unit')" class="sortable">Unit <i :class="sortIcon('unit')"></i></th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="item in sortedData" :key="item.id">
                                 <td><input
                                    type="checkbox"
                                    v-model="selections[form.selectedSubType ? form.selectedSubType.value : 'products']"
                                    :value="item.id"
                                    ></td>
                                 <td>@{{ item.code }}</td>
                                 <td>@{{ item.name }}</td>
                                 <td>@{{ item.category ? item.category.name : 'N/A' }}</td>
                                 <td>@{{ item.unit ? item.unit.name : 'N/A' }}</td>
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
                           <tbody v-if="form.selectedSubType">
                              <tr v-if="selectedItems.length === 0">
                                 <td colspan="6" class="text-center">No Selected Items</td>
                              </tr>
                              <tr v-for="item in selectedItems" :key="item.id">
                                 <td>@{{ item.name }}</td>
                                 <td>@{{ item.code }}</td>
                                 <td>@{{ item.category ? item.category.name : 'N/A' }}</td>
                                 <td>@{{ item.onhand ?? 0 }}</td>
                                 <td>@{{ item.unit ? item.unit.name : 'N/A' }}</td>
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
                     <button type="button" class="btn btn-primary mr-2" 
                     @click="submitForm"
                     >
                     <i class="i-Yes me-2 font-weight-bold"></i> Submit
                     </button>
                     <a href="/inventory/procurement-request" class="btn btn-outline-secondary">Cancel</a>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
<script>
    Vue.component('v-select', VueSelect.VueSelect);
new Vue({
       el: '#app',
       data() {
           return {
               mode: '{{ $mode }}',
               currentBranchId: @json($currentBranchId ?? null),
               details: @json($details ?? []),
               form: {
                   id: @json($prfs->id ?? null),
                   referenceNo: '{{ $referenceNo ?? '' }}',
                   requested_datetime: @json(
                        $prfs->requested_datetime
                        ?? now()->timezone('Asia/Manila')->format('Y-m-d\TH:i')
                    ),
                   selectedType: '',
                   selectedSubType: '',
                   attached_file: null,
                   proformaReferenceNo: '',
                   selectedRequestor: null,
                   selectedBranch: null,
                   selectedOrigin: 'local',
                   selectedItems: [],
               },
               requestorOptions: @json(
                   $requestors->map(fn ($b) => [
                       'label' => $b->name,
                       'value' => $b->id
                   ])
               ),
               branchesOptions: @json(
                   $branches->map(fn ($b) => [
                       'label' => $b->name,
                       'value' => $b->id
                   ])
               ),
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
               typeOptions: [
                   { label: 'Direct/Indirect Materials', value: 'direct' },
                   { label: 'Consumables/Engineering', value: 'consumables' },
                   { label: 'Assets', value: 'assets' },
                   { label: 'Services', value: 'services' },
               ],
               
               originOptions: [
                    { label: 'Local', value: 'local' },
                    { label: 'International', value: 'international' },
               ],
               currentSort: '',
               currentSortDir: 'asc',
               selectAll: false,

            }
        },
        mounted() {
            if (this.currentBranchId) {
                  this.form.selectedBranch = this.branchesOptions.find(
                        b => b.value === this.currentBranchId
                  );
               }
            if (this.mode === 'edit') {
               this.initEdit();
            }
         },
        methods: {
         initEdit() {
    const prf = @json($prfs);

    // 🔹 Basic fields
    this.form.referenceNo = prf.reference_no;
    this.form.proformaReferenceNo = prf.proforma_reference_no;

    this.form.selectedOrigin = {
        label: prf.origin,
        value: prf.origin.toLowerCase()
    };

    // 🔹 Type
    this.form.selectedType = this.typeOptions.find(
        t => t.value === prf.type.toLowerCase()
    );

    // 🔹 Requestor
    this.form.selectedRequestor = this.requestorOptions.find(
        r => r.value === prf.requested_by
    );

    // 🔹 Branch
    this.form.selectedBranch = this.branchesOptions.find(
        b => b.value === prf.requesting_branch_id
    );

    const details = this.details || {};
    console.log('🔍 Details for edit:', details);

    // ✅ SET DEFAULT SUBTYPE BASED ON DATA
    const hasProducts = details.products && details.products.length > 0;
    const hasComponents = details.components && details.components.length > 0;

    if (hasProducts) {
        this.form.selectedSubType = { label: 'Products', value: 'products' };
    } else if (hasComponents) {
        this.form.selectedSubType = { label: 'Components', value: 'components' };
    } else {
        this.form.selectedSubType = { label: 'Products', value: 'products' };
    }

    // 🔥 LOAD BOTH TYPES
    this.$nextTick(async () => {

        const [productsRes, componentsRes] = await Promise.all([
            axios.get('{{ route("procurement-request.fetchItems") }}', {
                params: { subtype: 'products' }
            }),
            axios.get('{{ route("procurement-request.fetchItems") }}', {
                params: { subtype: 'components' }
            })
        ]);

        this.allItems.products = (productsRes.data.items || []).map(i => ({
            ...i,
            _type: 'products'
        }));

        this.allItems.components = (componentsRes.data.items || []).map(i => ({
            ...i,
            _type: 'components'
        }));

        // 🔥 SHOW ONLY DEFAULT SUBTYPE (not merged)
        const selectedType = this.form.selectedSubType.value;
        this.items = this.allItems[selectedType] || [];

        // 🔥 APPLY SELECTIONS + QUANTITIES
        Object.keys(details).forEach(type => {
            details[type].forEach(i => {
                this.selections[type].push(i.id);
                this.$set(this.quantities[type], i.id, i.quantity);
            });
        });

    });
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
            // ----------------- Selection -----------------
           toggleAll() {
                const type = this.form.selectedSubType?.value;
                if (!type) return;

                this.selections[type] = this.selectAll
                    ? this.items.map(i => i.id)
                    : [];
            },
           sortIcon(col){ 
               return ['fa', this.currentSort===col?(this.currentSortDir==='asc'?'fa-sort-up':'fa-sort-down'):'fa-sort']; 
           },
            async loadItems() {
                const subtype = this.form.selectedSubType?.value;
                
                
                try {
                    const response = await axios.get('{{ route("procurement-request.fetchItems") }}', {
                        params: { subtype }
                    });
                
                    console.log('✅ Axios response:', response);
                    console.log('📄 Response data:', response.data);
                
                    if (!response.data || !response.data.items) {
                        console.warn('⚠️ No items returned from backend');
                        return;
                    }
                
                    this.items = response.data.items.map(item => ({
                        ...item,
                        _type: subtype,
                        quantity: Math.min(
                            this.quantities[subtype][item.id] || 0,
                            item.onhand ?? 0
                        )
                    }));

                    this.allItems[subtype] = this.items;
                
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
            // ----------------- Quantity -----------------
            incrementQuantity(item) {
                const type = item._type;
                const max = Number(item.onhand) || 0;
    
                let value = this.quantities[type][item.id] ?? 0;
    
                if (value >= max) return;
    
                value = +(value + 1).toFixed(2);
    
                this.$set(this.quantities[type], item.id, value);
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
   
   
               // update immediately
               item.quantity = value;
               this.quantities[item._type][item.id] = value;
   
               console.log('input', item.quantity)
   
               // force DOM sync (important for fast typing)
               e.target.value = value;
           },
          async submitForm() {
    try {
        // 🔴 BASIC VALIDATION
        if (!this.form.selectedRequestor) {
            return Swal.fire('Error', 'Please select requestor', 'warning');
        }

        if (!this.form.selectedType) {
            return Swal.fire('Error', 'Please select type', 'warning');
        }

        if (this.selectedItems.length === 0) {
            return Swal.fire('Error', 'Please select at least one item', 'warning');
        }
        if (!this.form.selectedBranch) {
            return Swal.fire('Error', 'Please select requesting branch', 'warning');
        }

        // 🔴 GROUP ITEMS
        const groupedItems = {
            products: [],
            components: []
        };

        this.selectedItems.forEach(i => {
            if (Number(i.quantity) <= 0) return;

            groupedItems[i._type].push({
                id: i.id,
                quantity: i.quantity
            });
        });

        // remove empty groups
        Object.keys(groupedItems).forEach(key => {
            if (groupedItems[key].length === 0) {
                delete groupedItems[key];
            }
        });

        if (Object.keys(groupedItems).length === 0) {
            return Swal.fire('Error', 'Please enter quantity for selected items', 'warning');
        }

        // 🔥 FINAL PAYLOAD
        const payload = {
            reference_no: this.form.referenceNo,
            requested_datetime: this.form.requested_datetime,
            requested_by: this.form.selectedRequestor.value,
            requesting_branch_id: this.form.selectedBranch?.value ?? null,
            type: this.form.selectedType.value,
            subtype: this.form.selectedSubType?.value ?? null,
            origin: this.form.selectedOrigin?.value ?? 'local',
            proforma_reference_no: this.form.proformaReferenceNo,
            items: groupedItems
        };

        console.log('📦 Payload:', payload);

        let response;

        // 🔥 SWITCH CREATE / EDIT
        if (this.mode === 'edit') {
            response = await axios.put(
                `/inventory/procurement-request/${this.form.id}`,
                payload
            );
        } else {
            response = await axios.post(
                '{{ route("procurement-request.store") }}',
                payload
            );
        }

        // ✅ SUCCESS
        Swal.fire(
            'Success',
            this.mode === 'edit'
                ? 'Procurement Request updated!'
                : 'Procurement Request created!',
            'success'
        ).then(() => {
            window.location.href = '/inventory/procurement-request';
        });

    } catch (e) {
        console.error('❌ Submit error:', e);

        if (e.response && e.response.data) {
            Swal.fire('Error', e.response.data.message || 'Failed to submit', 'error');
        } else {
            Swal.fire('Error', 'Something went wrong', 'error');
        }
    }
}
        },
        computed: {
            formTitle() {
                return `${this.mode === 'edit' ? 'Edit' : 'Create'} Procurement Request Form`
            },
            breadcrumbText() {
                return `Procurement Request`;
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
            subtypeOptions() {
               if (!this.form.selectedType) return [];

               if (this.form.selectedType.value === 'direct') {
                  return [
                        { label: 'Products', value: 'products' },
                        { label: 'Components', value: 'components' }
                  ];
               }

               return [];
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
            },
        },
        watch: {
            'form.selectedType'(val) {
               if (this.mode === 'edit') return; // 🔥 IMPORTANT

               this.form.selectedSubType = '';
               this.items = [];
               this.selections.products = [];
               this.selections.components = [];
               this.quantities.products = {};
               this.quantities.components = {};
               this.allItems.products = [];
               this.allItems.components = [];
               this.selectAll = false;
            },

            'form.selectedSubType'(val) {
               if (val) {
                     this.loadItems();
               }
            }
         }
    });
</script>
@endsection