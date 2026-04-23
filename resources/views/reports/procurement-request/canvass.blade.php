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
                                <th>Tax</th>
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
                                    <td colspan="14" class="text-center text-muted">No items found.</td>
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
                                    <td>₱@{{ formatCurrency(entry.tax) }}</td>
                                    <td>₱@{{ formatCurrency(entry.total_price) }}</td>
                                    <!-- Supplier v-select -->
                                    <td style="min-width:220px;">
                                        <div class="d-flex align-items-center">
                                            <div style="flex:1;">
                                                <v-select
                                                    v-model="entry.supplier_id"
                                                    :options="supplierOptions"
                                                    :reduce="s => s.id"
                                                    label="label"
                                                    placeholder="Select Supplier"
                                                    :clearable="false">
                                                </v-select>
                                            </div>
                                            <button class="btn btn-sm btn-warning ml-1" title="Supplier Info"
                                                @click="viewSupplier(entry.supplier_id)">
                                                <i class="i-Information"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <!-- Expected PO # -->
                                    <td>
                                        <span v-if="entry.selected_supplier && entry.supplier_id && supplierPoMap[entry.supplier_id]"
                                              class="badge badge-info text-white">
                                            @{{ supplierPoMap[entry.supplier_id] }}
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

                                <!-- "Add" row — shows item info if no entries yet, otherwise blank -->
                                <tr :key="`add-${itemIndex}`">
                                    <td>@{{ item.entries.length === 0 ? item.name : '' }}</td>
                                    <td>@{{ item.entries.length === 0 ? item.code : '' }}</td>
                                    <td>@{{ item.entries.length === 0 ? item.category : '' }}</td>
                                    <td>@{{ item.entries.length === 0 ? item.brand : '' }}</td>
                                    <td>@{{ item.entries.length === 0 ? item.unit : '' }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
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
    },
    computed: {
        supplierOptions() {
            return this.suppliers.map(s => ({
                id: s.id,
                label: s.company || s.fullname,
            }));
        },
        uniqueSupplierCount() {
            const ids = new Set();
            this.items.forEach(item => {
                item.entries.forEach(e => {
                    if (e.selected_supplier && e.supplier_id) ids.add(e.supplier_id);
                });
            });
            return ids.size;
        },
        // Maps supplier_id → expected PO # using shared base + suffix (only for selected_supplier = true)
        supplierPoMap() {
            const map = {};
            const base = 'PO-' + this.branchId + '-' + String(this.nextPoSeq).padStart(6, '0');
            let index = 1;
            this.items.forEach(item => {
                item.entries.forEach(e => {
                    if (e.selected_supplier && e.supplier_id && !map[e.supplier_id]) {
                        map[e.supplier_id] = base + '-' + index++;
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
        fetchData() {
            axios.get(`/inventory/procurement-request/${this.prfId}/fetchCanvassData`)
                .then(response => {
                    const { items, canvass_items } = response.data;

                    // Build a map of saved entries keyed by type+item_id
                    const savedMap = {};
                    (canvass_items || []).forEach(c => {
                        savedMap[`${c.type}_${c.item_id}`] = c.entries || [];
                    });

                    this.items = items.map(item => {
                        const key = `${item.type}_${item.item_id}`;
                        const savedEntries = savedMap[key] || [];
                        return {
                            ...item,
                            entries: savedEntries.map(e => ({
                                price_per_unit:    e.price_per_unit || 0,
                                tax:               e.tax || 0,
                                total_price:       e.total_price || 0,
                                supplier_id:       e.supplier_id || null,
                                selected_supplier: e.selected_supplier || false,
                                attachment_path:   e.attachment_path || null,
                                attachment_name:   e.attachment_name || null,
                                uploading:         false,
                            })),
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
                tax:               0,
                total_price:       0,
                supplier_id:       null,
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
            const price   = parseFloat(entry.price_per_unit) || 0;
            const qty     = parseFloat(item.quantity) || 0;
            const taxRate = 0.12;
            const subtotal = price * qty;
            entry.tax         = parseFloat((subtotal * taxRate).toFixed(2));
            entry.total_price = parseFloat((subtotal + entry.tax).toFixed(2));
            this.$set(this.items[itemIndex].entries, entryIndex, { ...entry });
        },

        formatCurrency(val) {
            return parseFloat(val || 0).toFixed(2);
        },

        viewSupplier(supplierId) {
            if (!supplierId) {
                Swal.fire('No Supplier', 'Please select a supplier first.', 'info');
                return;
            }
            const supplier = this.suppliers.find(s => s.id == supplierId);
            if (supplier) {
                Swal.fire({
                    title: 'Supplier Info',
                    html: `<strong>Name:</strong> ${supplier.fullname}<br><strong>Company:</strong> ${supplier.company || 'N/A'}`,
                    icon: 'info',
                });
            }
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
                const missing = item.entries.find(e => !e.supplier_id);
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
                        tax:               e.tax,
                        total_price:       e.total_price,
                        supplier_id:       e.supplier_id,
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
                        .then(() => {
                            window.location = '{{ route('procurement-request.index') }}';
                        });
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
