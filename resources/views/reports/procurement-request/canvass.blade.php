@extends('layouts.app')
@section('content')
<style>
    .canvass-table th, .canvass-table td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .qty-control {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .qty-control input {
        width: 80px;
        text-align: center;
    }
    .qty-btn {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        font-size: 16px;
        line-height: 1;
    }
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 42px;
        height: 22px;
    }
    .toggle-switch input { display: none; }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        border-radius: 22px;
        transition: .3s;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: .3s;
    }
    .toggle-switch input:checked + .toggle-slider { background-color: #28a745; }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }
    .card-header-info {
        display: flex;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #dee2e6;
        font-size: 14px;
    }
</style>

<div class="main-content" id="app">
    <div>
        <div class="breadcrumb">
            <h1 class="mr-3">Inventory</h1>
            <ul>
                <li><a href="{{ route('procurement-request.index') }}">PRF - Procurement Request Form</a></li>
                <li>Submit Canvasses</li>
            </ul>
        </div>
        <div class="separator-breadcrumb border-top"></div>
    </div>

    <div class="wrapper">
        <div class="card">
            <!-- PRF Header Info -->
            <div class="card-header-info">
                <div>
                    <div><strong>Requestors Name:</strong> {{ optional($prf->requestedBy)->name }}</div>
                    <div><strong>Department:</strong> {{ optional(optional($prf->requestedBy->employeeWorkInformations->last())->department)->name ?? 'N/A' }}</div>
                    <div><strong>PRF Reference #:</strong> {{ $prf->reference_no }}</div>
                    <div><strong>Type of Request:</strong> {{ ucfirst($prf->type) }}</div>
                </div>
                <div class="text-right">
                    <div><strong>Date of Request:</strong> {{ \Carbon\Carbon::parse($prf->created_at)->format('F d, Y') }}</div>
                    <div><strong>Time of Request:</strong> {{ \Carbon\Carbon::parse($prf->created_at)->format('g:i A') }}</div>
                </div>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0">Submit Canvasses</h5>
                    <div class="text-muted small">
                        <span v-if="uniqueSupplierCount === 0">Select a supplier and toggle <strong>Selected Supplier</strong> to preview PO #.</span>
                        <span v-else>
                            Upon approval, PO(s) will be created:
                            <strong class="text-primary" v-for="(po, sid) in supplierPoMap" :key="sid">
                                @{{ po }}&nbsp;
                            </strong>
                        </span>
                    </div>
                </div>

                <div v-if="loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-bordered canvass-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Unit</th>
                                <th>Qty</th>
                                <th>Canvassed Price/Unit</th>
                                <th>Total Price</th>
                                <th>Supplier</th>
                                <th>Expected PO #</th>
                                <th>Selected Supplier</th>
                                <th>Attachment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="items.length === 0">
                                <tr>
                                    <td colspan="13" class="text-center text-muted">No items found.</td>
                                </tr>
                            </template>

                            <template v-for="(item, itemIndex) in items">
                                <!-- One row per canvass entry (supplier quote) -->
                                <tr v-for="(entry, entryIndex) in item.entries" :key="`entry-${itemIndex}-${entryIndex}`">
                                    <!-- Item info only on first entry row -->
                                    <td>@{{ entryIndex === 0 ? item.name : '' }}</td>
                                    <td>@{{ entryIndex === 0 ? item.code : '' }}</td>
                                    <td>@{{ entryIndex === 0 ? item.category : '' }}</td>
                                    <td>@{{ entryIndex === 0 ? item.brand : '' }}</td>
                                    <td>@{{ entryIndex === 0 ? item.unit : '' }}</td>
                                    <!-- Qty shown on every entry row -->
                                    <td>@{{ item.quantity }}</td>
                                    <!-- Canvassed Price/Unit -->
                                    <td>
                                        <div class="qty-control">
                                            <button class="btn btn-danger qty-btn" @click="decrement(itemIndex, entryIndex)">−</button>
                                            <input type="number" class="form-control form-control-sm"
                                                v-model.number="entry.price_per_unit"
                                                min="0" step="0.01"
                                                @input="recalculate(itemIndex, entryIndex)" />
                                            <button class="btn btn-success qty-btn" @click="increment(itemIndex, entryIndex)">+</button>
                                        </div>
                                    </td>
                                    <td>₱@{{ formatCurrency(entry.total_price) }}</td>
                                    <!-- Supplier v-select — stores full {id,label} object, no lookup needed -->
                                    <td style="min-width:240px;">
                                        <div class="d-flex align-items-center">
                                            <div style="flex:1;">
                                                <v-select
                                                    v-model="entry.supplierOption"
                                                    :options="supplierOptions"
                                                    label="label"
                                                    placeholder="Select Supplier"
                                                    :clearable="false">
                                                </v-select>
                                            </div>
                                            <button class="btn btn-sm btn-success ml-1" title="Add New Supplier"
                                                @click="openAddSupplier(itemIndex, entryIndex)">
                                                <i class="i-Add"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <!-- Expected PO # -->
                                    <td>
                                        <span v-if="entry.selected_supplier && entry.supplierOption && supplierPoMap[entry.supplierOption.id]"
                                              class="badge badge-info text-white">
                                            @{{ supplierPoMap[entry.supplierOption.id] }}
                                        </span>
                                        <span v-else class="text-muted small">—</span>
                                    </td>
                                    <!-- Selected Supplier toggle -->
                                    <td class="text-center">
                                        <label class="toggle-switch">
                                            <input type="checkbox" v-model="entry.selected_supplier" />
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </td>
                                    <!-- Attachment -->
                                    <td style="min-width:200px;">
                                        <div v-if="entry.attachment_name" class="mb-1">
                                            <small class="text-success">
                                                <i class="i-File"></i> @{{ entry.attachment_name }}
                                            </small>
                                            <button type="button" class="btn btn-link btn-sm p-0 ml-1 text-danger"
                                                @click="clearAttachment(itemIndex, entryIndex)" title="Remove file">
                                                <i class="i-Close-Window"></i>
                                            </button>
                                        </div>
                                        <input type="file"
                                            class="form-control-file"
                                            accept=".csv,.xls,.xlsx,.doc,.docx"
                                            :ref="`file-${itemIndex}-${entryIndex}`"
                                            @change="uploadAttachment(itemIndex, entryIndex, $event)" />
                                        <small class="text-muted" v-if="entry.uploading">Uploading...</small>
                                    </td>
                                    <!-- Remove this entry -->
                                    <td>
                                        <button class="btn btn-sm btn-danger" @click="removeEntry(itemIndex, entryIndex)">Remove</button>
                                    </td>
                                </tr>

                                <!-- "Add" row -->
                                <tr :key="`add-${itemIndex}`">
                                    <td>@{{ item.entries.length === 0 ? item.name : '' }}</td>
                                    <td>@{{ item.entries.length === 0 ? item.code : '' }}</td>
                                    <td>@{{ item.entries.length === 0 ? item.category : '' }}</td>
                                    <td>@{{ item.entries.length === 0 ? item.brand : '' }}</td>
                                    <td>@{{ item.entries.length === 0 ? item.unit : '' }}</td>
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                    <td class="text-right">
                                        <button class="btn btn-sm btn-success" @click="addEntry(itemIndex)">Add</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button class="btn btn-primary mr-2" @click="submitCanvass" :disabled="submitting">
                        <i class="i-Check"></i> @{{ submitting ? 'Submitting...' : 'Submit' }}
                    </button>
                    <a href="{{ route('procurement-request.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Add New Supplier Modal ===== -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalLabel">
                        <i class="i-Add text-success mr-1"></i> Add New Supplier
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" v-model="newSupplier.supplier_name" class="form-control" placeholder="Enter supplier name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Contact Person</label>
                            <input type="text" v-model="newSupplier.contact_person" class="form-control" placeholder="Enter contact person">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Mobile #</label>
                            <input type="text" v-model="newSupplier.mobile_no" class="form-control" placeholder="e.g. 09XX-XXX-XXXX">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Landline #</label>
                            <input type="text" v-model="newSupplier.landline_no" class="form-control" placeholder="e.g. (02) XXXX-XXXX">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" v-model="newSupplier.email" class="form-control" placeholder="supplier@example.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Supplier Since</label>
                            <input type="date" v-model="newSupplier.supplier_since" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>TIN</label>
                            <input type="text" v-model="newSupplier.tin" class="form-control" placeholder="000-000-000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Supplier Type <span class="text-danger">*</span></label>
                            <select v-model="newSupplier.supplier_type" class="form-control">
                                <option value="" disabled>Select Supplier Type</option>
                                <option value="Food and Beverage Supplier">Food and Beverage Supplier</option>
                                <option value="Packaging Supplier">Packaging Supplier</option>
                                <option value="Equipment Supplier">Equipment Supplier</option>
                                <option value="Cleaning Supplies">Cleaning Supplies</option>
                                <option value="Utility Providers">Utility Providers</option>
                                <option value="Service Providers">Service Providers</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Address</label>
                            <textarea v-model="newSupplier.address" class="form-control" rows="2" placeholder="Enter address"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" @click="submitNewSupplier" :disabled="addingSupplier">
                        <span v-if="addingSupplier"><span class="spinner-border spinner-border-sm mr-1"></span>Saving...</span>
                        <span v-else><i class="i-Check mr-1"></i>Save Supplier</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== End Add New Supplier Modal ===== -->

</div>
@endsection

@section('scripts')
<script>
    Vue.component('v-select', VueSelect.VueSelect);

new Vue({
    el: '#app',
    data: {
        loading: true,
        submitting: false,
        items: [],
        suppliers: @json($suppliers),
        prfId: {{ $prf->id }},
        branchId: {{ $prf->requesting_branch_id ?? 'null' }},
        nextPoSeq: {{ (int) last(explode('-', $nextPoNumber)) }},
        addingSupplierTarget: null,
        addingSupplier: false,
        newSupplier: {
            supplier_name: '',
            contact_person: '',
            mobile_no: '',
            landline_no: '',
            email: '',
            supplier_since: '',
            tin: '',
            supplier_type: '',
            address: '',
        },
    },
    computed: {
        supplierOptions() {
            return this.suppliers.map(s => this.toOption(s));
        },
        uniqueSupplierCount() {
            const ids = new Set();
            this.items.forEach(item => {
                item.entries.forEach(e => {
                    if (e.selected_supplier && e.supplierOption) ids.add(e.supplierOption.id);
                });
            });
            return ids.size;
        },
        supplierPoMap() {
            const map = {};
            const base = 'PO-' + this.branchId + '-' + String(this.nextPoSeq).padStart(6, '0');
            let index = 1;
            this.items.forEach(item => {
                item.entries.forEach(e => {
                    if (e.selected_supplier && e.supplierOption && !map[e.supplierOption.id]) {
                        map[e.supplierOption.id] = base + '-' + index++;
                    }
                });
            });
            return map;
        },
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        // Converts a raw supplier record into the {id, label} shape v-select stores directly.
        // Storing the full object means v-select never needs to search supplierOptions for a label.
        toOption(s) {
            return {
                id: s.id,
                label: s.supplier_name + (s.contact_person ? ' — ' + s.contact_person : ''),
            };
        },

        fetchData() {
            axios.get(`/inventory/procurement-request/${this.prfId}/fetchCanvassData`)
                .then(response => {
                    const { items, canvass_items } = response.data;

                    const savedMap = {};
                    (canvass_items || []).forEach(c => {
                        savedMap[`${c.type}_${c.item_id}`] = c.entries || [];
                    });

                    this.items = items.map(item => {
                        const key = `${item.type}_${item.item_id}`;
                        const savedEntries = savedMap[key] || [];
                        return {
                            ...item,
                            entries: savedEntries.map(e => {
                                const matched = this.suppliers.find(s => s.id == e.supplier_id);
                                return {
                                    price_per_unit:    e.price_per_unit || 0,
                                    total_price:       e.total_price || 0,
                                    supplierOption:    matched ? this.toOption(matched) : null,
                                    selected_supplier: e.selected_supplier || false,
                                    attachment_path:   e.attachment_path || null,
                                    attachment_name:   e.attachment_name || null,
                                    uploading:         false,
                                };
                            }),
                        };
                    });
                })
                .catch(err => {
                    console.error('Failed to fetch canvass data:', err);
                    Swal.fire('Error', 'Failed to load items.', 'error');
                })
                .finally(() => { this.loading = false; });
        },

        addEntry(itemIndex) {
            this.items[itemIndex].entries.push({
                price_per_unit:    0,
                total_price:       0,
                supplierOption:    null,
                selected_supplier: false,
                attachment_path:   null,
                attachment_name:   null,
                uploading:         false,
            });
            this.$set(this.items, itemIndex, { ...this.items[itemIndex] });
        },

        removeEntry(itemIndex, entryIndex) {
            this.items[itemIndex].entries.splice(entryIndex, 1);
            this.$set(this.items, itemIndex, { ...this.items[itemIndex] });
        },

        increment(itemIndex, entryIndex) {
            const entry = this.items[itemIndex].entries[entryIndex];
            entry.price_per_unit = parseFloat((entry.price_per_unit + 1).toFixed(2));
            this.recalculate(itemIndex, entryIndex);
        },

        decrement(itemIndex, entryIndex) {
            const entry = this.items[itemIndex].entries[entryIndex];
            if (entry.price_per_unit > 0) {
                entry.price_per_unit = parseFloat((entry.price_per_unit - 1).toFixed(2));
                this.recalculate(itemIndex, entryIndex);
            }
        },

        recalculate(itemIndex, entryIndex) {
            const item  = this.items[itemIndex];
            const entry = item.entries[entryIndex];
            const price    = parseFloat(entry.price_per_unit) || 0;
            const qty      = parseFloat(item.quantity) || 0;
            const subtotal = price * qty;
            entry.total_price = parseFloat(subtotal.toFixed(2));
            this.$set(this.items[itemIndex].entries, entryIndex, { ...entry });
        },

        formatCurrency(val) {
            return parseFloat(val || 0).toFixed(2);
        },

        openAddSupplier(itemIndex, entryIndex) {
            this.addingSupplierTarget = { itemIndex, entryIndex };
            this.newSupplier = {
                supplier_name: '', contact_person: '', mobile_no: '',
                landline_no: '', email: '', supplier_since: '',
                tin: '', supplier_type: '', address: '',
            };
            $('#addSupplierModal').modal('show');
        },

        submitNewSupplier() {
            if (!this.newSupplier.supplier_name.trim()) {
                Swal.fire('Required', 'Supplier Name is required.', 'warning');
                return;
            }
            if (!this.newSupplier.supplier_type) {
                Swal.fire('Required', 'Supplier Type is required.', 'warning');
                return;
            }

            this.addingSupplier = true;

            axios.post('{{ route("suppliers.store") }}', this.newSupplier)
                .then(response => {
                    const supplier = response.data;

                    // Same pattern as products/create station auto-fill:
                    // push to list first, then assign the full option object directly —
                    // no DOM search, no timing issue, works like option.selected = true
                    this.suppliers.push(supplier);

                    if (this.addingSupplierTarget !== null) {
                        const { itemIndex, entryIndex } = this.addingSupplierTarget;
                        const entry = { ...this.items[itemIndex].entries[entryIndex], supplierOption: this.toOption(supplier) };
                        this.$set(this.items[itemIndex].entries, entryIndex, entry);
                    }

                    $('#addSupplierModal').modal('hide');
                    Swal.fire({
                        title: 'Supplier Added!',
                        text: `"${supplier.supplier_name}" has been added and selected.`,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', err.response?.data?.message || 'Failed to create supplier.', 'error');
                })
                .finally(() => { this.addingSupplier = false; });
        },

        uploadAttachment(itemIndex, entryIndex, event) {
            const file = event.target.files[0];
            if (!file) return;

            const allowed = ['csv', 'xls', 'xlsx', 'doc', 'docx'];
            const ext = file.name.split('.').pop().toLowerCase();
            if (!allowed.includes(ext)) {
                Swal.fire('Invalid File', 'Only CSV, Excel, and Word files are accepted.', 'error');
                event.target.value = '';
                return;
            }

            const entry = this.items[itemIndex].entries[entryIndex];
            entry.uploading = true;
            this.$set(this.items[itemIndex].entries, entryIndex, { ...entry });

            const formData = new FormData();
            formData.append('file', file);

            axios.post(`/inventory/procurement-request/${this.prfId}/canvass/upload-attachment`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            .then(response => {
                const e = this.items[itemIndex].entries[entryIndex];
                e.attachment_path = response.data.path;
                e.attachment_name = response.data.name;
                e.uploading = false;
                this.$set(this.items[itemIndex].entries, entryIndex, { ...e });
            })
            .catch(err => {
                console.error(err);
                const e = this.items[itemIndex].entries[entryIndex];
                e.uploading = false;
                this.$set(this.items[itemIndex].entries, entryIndex, { ...e });
                event.target.value = '';
                Swal.fire('Error', 'Failed to upload attachment.', 'error');
            });
        },

        clearAttachment(itemIndex, entryIndex) {
            const entry = this.items[itemIndex].entries[entryIndex];
            entry.attachment_path = null;
            entry.attachment_name = null;
            this.$set(this.items[itemIndex].entries, entryIndex, { ...entry });
            const ref = this.$refs[`file-${itemIndex}-${entryIndex}`];
            if (ref) ref.value = '';
        },

        submitCanvass() {
            const itemsWithEntries = this.items.filter(i => i.entries.length > 0);

            if (itemsWithEntries.length === 0) {
                Swal.fire('No Items', 'Please add at least one canvass entry.', 'warning');
                return;
            }

            for (const item of itemsWithEntries) {
                const missing = item.entries.find(e => !e.supplierOption);
                if (missing) {
                    Swal.fire('Missing Supplier', `Please select a supplier for all entries of "${item.name}".`, 'warning');
                    return;
                }
            }

            Swal.fire({
                title: 'Submit Canvasses?',
                text: 'Are you sure you want to submit the canvass?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Submit',
            }).then(result => {
                if (!result.isConfirmed) return;

                this.submitting = true;

                const payload = itemsWithEntries.map(item => ({
                    type:    item.type,
                    item_id: item.item_id,
                    name:    item.name,
                    entries: item.entries.map(e => ({
                        price_per_unit:    e.price_per_unit,
                        total_price:       e.total_price,
                        supplier_id:       e.supplierOption ? e.supplierOption.id : null,
                        selected_supplier: e.selected_supplier,
                        attachment_path:   e.attachment_path || null,
                        attachment_name:   e.attachment_name || null,
                    })),
                }));

                axios.post(`/inventory/procurement-request/${this.prfId}/canvass`, {
                    canvass_items: payload,
                })
                .then(() => {
                    Swal.fire('Submitted!', 'Canvass submitted successfully.', 'success')
                        .then(() => { window.location = '{{ route('procurement-request.index') }}'; });
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Failed to submit canvass.', 'error');
                })
                .finally(() => { this.submitting = false; });
            });
        },
    },
});
</script>
@endsection
