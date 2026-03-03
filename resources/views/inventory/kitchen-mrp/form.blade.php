@extends('layouts.app')

@section('content')
<style>
    .upload-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

#drop-area {
    cursor: pointer;
    width: 100%;
    max-width: 350px;
}

#drop-area p {
    white-space: pre-line; /* preserves \n line breaks */
}

</style>
<div id="app" class="main-content">
    <div class="breadcrumb">
        <h1>@{{ isEdit ? 'Update Mass Production' : 'Create Mass Production' }}</h1>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <form @submit.prevent="submitForm">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="mt-3 col-md-8">
                                <div class="row">

                                    <!-- Reference No -->
                                    <div class="col-md-6 mb-3">
                                        <label>Reference No</label>
                                        <input type="text" class="form-control" v-model="form.reference_no" readonly>
                                    </div>

                                    <!-- Station (Readonly - auto filled) -->
                                    <div class="col-md-6 mb-3">
                                        <label>Station</label>
                                        <input type="text" class="form-control" v-model="form.station_name" readonly>
                                    </div>

                                    <!-- SKU Dropdown -->
                                    <div class="col-md-6 mb-3">
                                        <label>SKU (Product Code)</label>
                                        <v-select
                                            :options="products"
                                            label="code"
                                            :reduce="product => product.id"
                                            v-model="form.product_id"
                                            @input="selectProduct"
                                            placeholder="Select SKU">
                                        </v-select>
                                    </div>

                                    <!-- Product Name Dropdown -->
                                    <div class="col-md-6 mb-3">
                                        <label>Product Name</label>
                                        <select class="form-control" v-model="form.product_id" @change="fetchIngredients">
                                            <option disabled value="">Select Product</option>
                                            <option v-for="product in preloadedProducts" :value="product.id">
                                                @{{ product.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Target Output -->
                                    <div class="col-md-6 mb-3">
                                        <label>Target Output</label>
                                        <input type="number" class="form-control" v-model="form.quantity">
                                    </div>

                                    <!-- Unit (Readonly Auto Filled) -->
                                    <div class="col-md-6 mb-3">
                                        <label>Unit</label>
                                        <input type="text" class="form-control" v-model="form.unit_name" readonly>
                                    </div>

                                    {{-- <!-- Product Image (Readonly Display Only) -->
                                    <div class="col-md-6 mt-3">
                                        <label>Product Image</label>
                                        <div class="border rounded p-3 text-center">
                                            <img v-if="form.image"
                                                :src="form.image"
                                                style="max-height:150px;">
                                            <p v-else class="text-muted">No image available</p>
                                        </div>
                                    </div> --}}

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10 mt-3 mt-md-0">
                                    <fieldset class="form-group" style="width: 300px;">
                                    <legend>Product Image</legend>
                                    <div id="drop-area"
                                    class="upload-box text-center p-3 border rounded position-relative"
                                    @dragover.prevent
                                    @drop.prevent="handleDrop"
                                    @click="triggerFileInput">
                                    <!-- Hidden File Input -->
                                    <input type="file"
                                        ref="fileInput"
                                        class="d-none"
                                        @change="onFileChange"
                                        accept=".png, .jpg, .jpeg">
                                    <!-- If NO image -->
                                    <div v-if="!form.previewImage">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-muted"></i>
                                        <p class="text-muted mb-0">
                                            Drag & Drop an image<br>
                                            <strong>(or) Click to select</strong>
                                        </p>
                                    </div>
                                    <!-- If image EXISTS -->
                                    <div v-else class="preview-wrapper">
                                        <img :src="form.previewImage" class="preview-image">
                                        <div class="overlay-text">
                                            Click to change image
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </fieldset>        
                        </div>
                        </div>
                        <hr>
                        <h5>Ingredients</h5>
                        <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Component</th>
                                <th>Required Quantity</th>
                                <th>Unit</th>
                                <th>Stock on Hand</th>
                                <th>Needed Quantity</th>
                                <th v-if="isLogProcess">Used Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in form.ingredients" :key="item.id">
                                <td>@{{ item.name }}</td>
                                <td>@{{ computeRequired(item) }}</td>
                                <td>@{{ item.unit }}</td>
                                <td>@{{ item.stock_on_hand }}</td>
                                <!-- Needed Quantity -->
                                <td>
                                    @{{ computeNeeded(item)}}
                                </td>
                                <td v-if="isLogProcess">
                                    <input type="number"
                                        class="form-control"
                                        v-model.number="item.used_quantity">
                                </td>
                            </tr>
                        </tbody>
                        </table>
                        <div v-if="isLogProcess" class="row">
                            <div class="col-md-12 mt-4">
                                <h5>Add Additional Ingredients</h5>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Component</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th width="80">
                                                <button type="button"
                                                        class="btn btn-success btn-sm"
                                                        @click="addRecipeRow">
                                                    +
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-for="(row, index) in additionalRecipes"
                                            :key="index">

                                            <!-- Component Dropdown -->
                                            <td>
                                                <select v-model="row.component_id"
                                                        class="form-control"
                                                        @change="updateRow(row)">
                                                    <option value="" disabled>Select component</option>
                                                    <option v-for="component in components"
                                                            :key="component.id"
                                                            :value="component.id">
                                                        @{{ component.name }}
                                                    </option>
                                                </select>
                                            </td>

                                            <!-- Quantity -->
                                            <td>
                                                <input type="number"
                                                    step="0.01"
                                                    class="form-control"
                                                    v-model.number="row.quantity"
                                                    @input="updateRow(row)">
                                            </td>

                                            <!-- Unit -->
                                            <td>
                                                <input type="text"
                                                    class="form-control"
                                                    v-model="row.unit"
                                                    readonly>
                                            </td>

                                            <!-- Remove -->
                                            <td>
                                                <button type="button"
                                                        class="btn btn-danger btn-sm"
                                                        @click="removeRow(index)">
                                                    x
                                                </button>
                                            </td>

                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <<div class="row">
                            <div class="col-md-12 mt-4">
                                <label>Remarks</label>
                                <input type="text" class="form-control" v-model="form.remarks">
                            </div>
                        </div>
                        <button class="btn btn-primary mt-3">
                        @{{ isEdit ? 'Update Mass Production' : 'Create Mass Production' }}
                        </button>
                    </div>
                </div>
            </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
window.preloadedData = {
    products: @json($products),
    components: @json($components),
    massProductionIngredients: {!! $massProduction
        ? json_encode($massProduction->product->recipes->map(function($recipe) {
            return [
                'id'            => $recipe->id,
                'component_id'  => $recipe->component_id,
                'name'          => $recipe->component->name ?? '',
                'unit'          => $recipe->component->unit ?? '',
                'quantity'      => $recipe->quantity,
                'stock_on_hand' => $recipe->component->onhand ?? 0,
            ];
        }))
        : '[]' 
    !!},
    massProductionForm: {!! $massProduction ? json_encode($massProduction) : 'null' !!}
};
</script>
<script>
    Vue.component('v-select', VueSelect.VueSelect);
new Vue({
    el: '#app',

    data: {
        isEdit: {!! $massProduction ? 'true' : 'false' !!},
        isLogProcess: {!! isset($isLogProcess) && $isLogProcess ? 'true' : 'false' !!},
        components: window.preloadedData.components,
        additionalRecipes: [],
        products: @json($products),

        form: {
            id: window.preloadedData.massProductionForm?.id || null,
            reference_no: window.preloadedData.massProductionForm?.reference_no || "{{ $referenceNo }}",
            product_id: window.preloadedData.massProductionForm?.product_id || null,
            quantity: window.preloadedData.massProductionForm?.quantity || 0,
            remarks: window.preloadedData.massProductionForm?.remarks || '',
            unit_name: window.preloadedData.massProductionForm?.product?.unit?.name || '',
            station_name: window.preloadedData.massProductionForm?.product?.station?.name || '',
            image: window.preloadedData.massProductionForm?.product?.image
                ? '/storage/' + window.preloadedData.massProductionForm.product.image
                : '',
            ingredients: window.preloadedData.massProductionIngredients || []
        },

        preloadedProducts: window.preloadedData.products,
        preloadedComponents: window.preloadedData.components,
        errors: {}
    },

    methods: {
        addRecipeRow() {
            this.additionalRecipes.push({
                component_id: '',
                component_name: '',
                quantity: 0,
                unit: '',
            });
        },

        removeRow(index) {
            this.additionalRecipes.splice(index, 1);
        },

        updateRow(row) {
            const selected = this.components.find(c => c.id === row.component_id);
            if (selected) {
                row.component_name = selected.name;
                row.unit = selected.unit;
            }
        },
        // Always multiply recipe quantity by target output
        computeRequired(item) {
            const target = parseFloat(this.form.quantity) || 0;
            const baseQty = parseFloat(item.quantity) || 0;

            return (baseQty * target);
        },

        // Only calculate shortage amount
        computeNeeded(item) {
            const required = this.computeRequired(item);
            const stock = parseFloat(item.stock_on_hand) || 0;

            // If required is more than stock → return shortage
            if (required > stock) {
                return (required - stock).toFixed(2);
            }

            // Otherwise no shortage
            return 0;
        },
        fetchIngredients() {
            // Clear ingredients if no product selected
            if (!this.form.product_id) {
                this.form.ingredients = [];
                return;
            }

            // Find the selected product
            const product = this.preloadedProducts.find(p => p.id == this.form.product_id);
            if (!product || !product.recipes) {
                this.form.ingredients = [];
                return;
            }

            // Map each recipe to include component details
            this.form.ingredients = product.recipes.map(recipeItem => {
                const component = this.preloadedComponents.find(c => c.id == recipeItem.component_id);

                return {
                    id: recipeItem.id,
                    component_id: recipeItem.component_id,
                    product_id: recipeItem.product_id,
                    quantity: recipeItem.quantity,
                    name: component ? component.name : '',
                    unit: component && component.unit ? component.unit : '',
                    stock_on_hand: component ? component.onhand : 0
                };
            });
        },
        selectProduct(productId) {
            const product = this.products.find(p => p.id === productId)
            console.log('seelcted prd', this.form)
            if (!product) return

            this.form.product_id = product.id
            this.form.unit_name = product.unit ? product.unit.name : ''
            this.form.station_name = product.station ? product.station.name : ''
            this.form.image = product.image ? `/storage/${product.image}` : ''

            // ✅ Call fetchIngredients to load ingredients
            this.fetchIngredients()
        },
            triggerFileInput() {
            this.$refs.fileInput.click();
        },

    onFileChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    const validTypes = ['image/png', 'image/jpeg'];
    const maxSize = 2 * 1024 * 1024; // 2MB

    // Validate file type
    if (!validTypes.includes(file.type)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid File Type',
            text: 'Only PNG and JPG files are allowed.',
            confirmButtonColor: '#3085d6'
        });

        event.target.value = '';
        return;
    }

    // Validate file size
    if (file.size > maxSize) {
        Swal.fire({
            icon: 'warning',
            title: 'File Too Large',
            text: 'Image must not exceed 2MB.',
            confirmButtonColor: '#3085d6'
        });

        event.target.value = '';
        return;
    }

    // Save file
    this.form.image = file;

    // Preview
    const reader = new FileReader();
    reader.onload = e => {
        this.form.previewImage = e.target.result;
    };
    reader.readAsDataURL(file);
},

    handleDrop(event) {
    const file = event.dataTransfer.files[0];
    if (!file) return;

    const validTypes = ['image/png', 'image/jpeg'];
    const maxSize = 2 * 1024 * 1024; // 2MB

    if (!validTypes.includes(file.type)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid File Type',
            text: 'Only PNG and JPG files are allowed.'
        });
        return;
    }

    if (file.size > maxSize) {
        Swal.fire({
            icon: 'warning',
            title: 'File Too Large',
            text: 'Image must not exceed 2MB.'
        });
        return;
    }

    this.form.image = file;

    const reader = new FileReader();
    reader.onload = e => {
        this.form.previewImage = e.target.result;
    };
    reader.readAsDataURL(file);
},

       submitForm() {
            // 🔹 Basic Validation
            if (!this.form.product_id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please select a product.'
                });
                return;
            }

            if (!this.form.quantity || this.form.quantity <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Target output must be greater than 0.'
                });
                return;
            }

            // 🔹 Determine URL & Method
            let url = this.isEdit
                ? `/inventory/kitchen-mrp/${this.form.id}`
                : `/inventory/kitchen-mrp`;

            let method = 'POST'; // Always POST; Laravel will spoof PUT for edits
            let formData = new FormData();

            // 🔹 Basic fields
            formData.append('reference_no', this.form.reference_no);
            formData.append('product_id', this.form.product_id);
            formData.append('quantity', this.form.quantity);
            formData.append('remarks', this.form.remarks || '');

            if (this.isEdit) {
                formData.append('_method', 'PUT');
            }

            // 🔹 If Log Process Mode
            if (this.isLogProcess) {

                // Merge used_ingredients + additional_items
                const processedItems = [];

                // Used ingredients
                this.form.ingredients.forEach(item => {
                    processedItems.push({
                        component_id: item.component_id ?? item.id,
                        name: item.name,
                        quantity: item.used_quantity || 0,
                        unit: item.unit
                    });
                });

                // Additional items
                this.additionalRecipes.forEach(item => {
                    processedItems.push({
                        component_id: item.component_id,
                        name: item.name,
                        quantity: item.quantity || 0,
                        unit: item.unit
                    });
                });

                // Append merged array
                formData.append('processed_items', JSON.stringify(processedItems));

                // 🔹 Automatically set status to completed
                formData.append('status', 'completed');
            }

            // 🔹 Debug: Check merged FormData
            console.log('===== FORM DATA =====');
            formData.forEach((value, key) => {
                try {
                    console.log(key, JSON.parse(value));
                } catch {
                    console.log(key, value);
                }
            });
            console.log('=====================');

            // 🔹 Submit
            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();

                if (!res.ok) {
                    // Laravel validation errors
                    if (res.status === 422 && data.errors) {
                        let errorMessages = Object.values(data.errors)
                            .flat()
                            .join('<br>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Failed',
                            html: errorMessages
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Something went wrong.'
                        });
                    }
                    throw new Error('Request failed');
                }

                return data;
            })
            .then(response => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: this.isEdit
                        ? 'Mass Production updated successfully!'
                        : 'Mass Production created successfully!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "/inventory/kitchen-mrp";
                });
            })
            .catch(error => {
                console.error(error);
            });
        }


    },

    mounted() {
    // if (!this.form.items.length) {
    //     this.addItem();
    // }
},

     watch: {
    'form.quantity'(val) {
        if (!this.form.product_id) return; // no product selected
        if (!this.form.ingredients || !this.form.ingredients.length) return;

        this.form.ingredients.forEach(item => {
            item.needed_quantity = this.computeNeeded(item);
        });
    }
}

});
</script>
@endsection
