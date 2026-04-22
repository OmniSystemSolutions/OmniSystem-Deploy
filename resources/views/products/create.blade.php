@extends('layouts.app')

@section('content')

<div class="main-content" id="app">

   {{-- Breadcrumb --}}
   <div>
      <div class="breadcrumb">
         <h1 class="mr-3">Create Product</h1>
         <ul>
            <li><a href="">Inventory</a></li>
         </ul>
         <div class="breadcrumb-action"></div>
      </div>
      <div class="separator-breadcrumb border-top"></div>
   </div>

   <div class="card mt-4">
      <div class="card-body">
         <form id="productForm" enctype="multipart/form-data">
            @csrf

            <div class="row">
               <div class="col-sm-12">
                  <div class="row">

                     {{-- Left column: form fields --}}
                     <div class="mt-3 col-md-8">

                        <div class="row">

                           {{-- Left sub-column --}}
                           <div class="col-md-6">

                              {{-- SKU --}}
                              <div class="form-group">
                                 <label>SKU (Product Code) *</label>
                                 <input type="text"
                                    id="code"
                                    name="code"
                                    class="form-control"
                                    placeholder="Enter SKU"
                                    value="{{ old('CODE') }}">
                                 <div id="SKU-feedback" class="invalid-feedback"></div>
                              </div>

                              {{-- Category --}}
                              <div class="form-group">
                                 <label>Category</label>
                                 <div class="d-flex">
                                    <select name="category_id"
                                       id="category_id"
                                       class="custom-select mr-2"
                                       v-model="category_id">
                                       <option value="" disabled selected></option>
                                       <option v-for="c in categories" :value="c.id" :key="c.id">
                                          @{{ c.name }}
                                       </option>
                                    </select>
                                    <button type="button"
                                       class="btn btn-outline-success btn-sm"
                                       @click="toggleCategoryForm">
                                       <i class="i-Add"></i>
                                    </button>
                                 </div>
                              </div>

                              {{-- New Category Form --}}
                              <div v-if="showCategoryForm"
                                 class="border rounded p-4 mt-3 bg-white shadow-sm"
                                 style="max-width: 600px; margin: auto;">

                                 <h4 class="text-center mb-4">Add Category</h4>

                                 <div class="form-group">
                                    <label class="font-weight-bold">Category Name *</label>
                                    <input type="text"
                                       class="form-control"
                                       v-model="newCategory.name"
                                       :class="{ 'is-invalid': errors.name }"
                                       placeholder="Enter category name">
                                    <div class="invalid-feedback">@{{ errors.name }}</div>
                                 </div>

                                 <div class="form-group mt-3">
                                    <label class="font-weight-bold">Description</label>
                                    <textarea class="form-control"
                                       rows="3"
                                       v-model="newCategory.description"
                                       :class="{ 'is-invalid': errors.description }"
                                       placeholder="Enter category description"></textarea>
                                    <div class="invalid-feedback">@{{ errors.description }}</div>
                                 </div>

                                 <div class="d-flex justify-content-center mt-4">
                                    <button class="btn btn-success px-4 mr-2" @click="saveCategory">Save</button>
                                    <button class="btn btn-danger px-4" @click="toggleCategoryForm">Cancel</button>
                                 </div>

                              </div>

                              {{-- Unit Price --}}
                              <div class="form-group">
                                 <label>Unit Price *</label>
                                 <input class="form-control"
                                    name="price"
                                    placeholder="0"
                                    value="{{ old('PRICE') }}"
                                    inputmode="decimal">
                                 <div id="Price-feedback" class="invalid-feedback"></div>
                              </div>

                              {{-- Quantity --}}
                              <div class="form-group">
                                 <label>Quantity *</label>
                                 <input class="form-control"
                                    name="quantity"
                                    placeholder="0"
                                    value="{{ old('QUANTITY') }}"
                                    inputmode="decimal">
                                 <div id="Quantity-feedback" class="invalid-feedback"></div>
                              </div>

                           </div>{{-- /col-md-6 left --}}

                           {{-- Right sub-column --}}
                           <div class="col-md-6">

                              {{-- Product Name --}}
                              <div class="form-group">
                                 <label>Product Name *</label>
                                 <input type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter Name of Product"
                                    value="{{ old('NAME') }}">
                                 <div id="Name-feedback" class="invalid-feedback"></div>
                              </div>

                              {{-- Subcategory --}}
                              <div class="form-group">
                                 <label>Subcategory</label>
                                 <div class="d-flex">
                                    <select class="custom-select mr-2" v-model="subcategory_id">
                                       <option value="" disabled></option>
                                       <option v-for="sub in subcategories" :value="sub.id" :key="sub.id">
                                          @{{ sub.name }}
                                       </option>
                                    </select>
                                    <button type="button"
                                       class="btn btn-outline-success btn-sm"
                                       @click="toggleSubCategoryForm">
                                       <i class="i-Add"></i>
                                    </button>
                                 </div>
                              </div>

                              {{-- New Subcategory Form --}}
                              <div v-if="showSubCategoryForm"
                                 class="border rounded p-4 mt-3 bg-white shadow-sm"
                                 style="max-width: 600px; margin: auto;">

                                 <h4 class="text-center mb-4">Add Sub Category</h4>

                                 <div class="form-group">
                                    <label class="font-weight-bold">Subcategory Name *</label>
                                    <input type="text"
                                       class="form-control"
                                       v-model="newSubCategory.name"
                                       :class="{ 'is-invalid': subErrors.name }"
                                       placeholder="Enter subcategory name">
                                    <div class="invalid-feedback">@{{ subErrors.name }}</div>
                                 </div>

                                 <div class="form-group mt-3">
                                    <label class="font-weight-bold">Description</label>
                                    <textarea class="form-control"
                                       rows="3"
                                       v-model="newSubCategory.description"
                                       :class="{ 'is-invalid': subErrors.description }"
                                       placeholder="Enter subcategory description"></textarea>
                                    <div class="invalid-feedback">@{{ subErrors.description }}</div>
                                 </div>

                                 <div class="d-flex justify-content-center mt-4">
                                    <button class="btn btn-success px-4 mr-2" @click="saveSubCategory">Save</button>
                                    <button class="btn btn-danger px-4" @click="toggleSubCategoryForm">Cancel</button>
                                 </div>

                              </div>

                              {{-- Station --}}
                              <div class="form-group">
                                 <label for="station_id">Station</label>
                                 <div class="d-flex">
                                    <select name="station_id" id="station_id" class="form-control mr-2">
                                       <option value="" disabled selected></option>
                                       @foreach ($stations as $station)
                                          <option value="{{ $station->id }}"
                                             {{ old('station_id') == $station->id ? 'selected' : '' }}>
                                             {{ $station->name }}
                                          </option>
                                       @endforeach
                                    </select>
                                    <button type="button"
                                       id="toggleStationBtn"
                                       class="btn btn-outline-success btn-sm"
                                       onclick="toggleStationForm()">
                                       <i class="i-Add"></i>
                                    </button>
                                 </div>
                              </div>

                              {{-- New Station Form --}}
                              <div id="newSStationForm"
                                 class="border rounded p-4 mt-3 bg-white shadow-sm"
                                 style="display: none; max-width: 600px; margin: auto;">

                                 <h4 class="text-center mb-4">Add Station</h4>

                                 <div class="form-group">
                                    <label for="new_station_name" class="font-weight-bold">Station Name *</label>
                                    <input type="text"
                                       id="new_station_name"
                                       class="form-control"
                                       placeholder="Enter station name">
                                    <div class="invalid-feedback" id="err_new_station_name"></div>
                                 </div>

                                 <div class="form-group mt-3">
                                    <label for="new_station_description" class="font-weight-bold">Description</label>
                                    <textarea id="new_station_description"
                                       class="form-control"
                                       rows="3"
                                       placeholder="Enter station description"></textarea>
                                    <div class="invalid-feedback" id="err_new_station_description"></div>
                                 </div>

                                 <div class="d-flex justify-content-center mt-4">
                                    <button type="button" onclick="saveStationForm()" class="btn btn-success px-4 mr-2">Save</button>
                                    <button type="button" onclick="toggleStationForm()" class="btn btn-danger px-4">Cancel</button>
                                 </div>

                              </div>

                              {{-- Unit --}}
                              <div class="form-group">
                                 <label for="unit_id">Unit</label>
                                 <select name="unit_id" id="unit_id" class="form-control">
                                    <option value="" disabled selected></option>
                                    @foreach ($units as $unit)
                                       <option value="{{ $unit->id }}"
                                          {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                          {{ $unit->name }}
                                       </option>
                                    @endforeach
                                 </select>
                              </div>

                           </div>{{-- /col-md-6 right --}}

                        </div>{{-- /row two-column fields --}}

                        {{-- Ingredients table --}}
                        <div class="row mt-4">
                           <div class="col-md-12">
                              <h5>Ingredients</h5>

                              <table class="table table-bordered">
                                 <thead>
                                    <tr>
                                       <th>Component</th>
                                       <th>Quantity</th>
                                       <th>Unit</th>
                                       <th>Cost</th>
                                       <th>
                                          <button type="button" class="btn btn-success btn-sm" @click="addRow()">+</button>
                                       </th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <tr v-for="(row, index) in recipes" :key="index">
                                       <td>
                                          <select class="form-control component-select"
                                             :name="'recipes[' + index + '][component_id]'"
                                             v-model="row.component_id"
                                             @change="updateRow(row)">
                                             <option value="">Select Component</option>
                                             <option v-for="c in components" :key="c.id" :value="c.id">
                                                @{{ c.name }}
                                             </option>
                                          </select>
                                       </td>
                                       <td>
                                          <input type="number"
                                             class="form-control"
                                             :name="'recipes[' + index + '][quantity]'"
                                             v-model.number="row.quantity"
                                             step="0.01"
                                             @input="updateRow(row)">
                                       </td>
                                       <td>
                                          <input type="text"
                                             class="form-control"
                                             :name="'recipes[' + index + '][unit]'"
                                             v-model="row.unit"
                                             readonly>
                                       </td>
                                       <td>
                                          <input type="text"
                                             class="form-control"
                                             :name="'recipes[' + index + '][cost]'"
                                             :value="row.cost.toFixed(2)"
                                             readonly>
                                       </td>
                                       <td>
                                          <button type="button"
                                             class="btn btn-danger btn-sm"
                                             @click="removeRow(index)">x</button>
                                       </td>
                                    </tr>
                                 </tbody>
                              </table>

                              <div class="row mt-2">
                                 <div class="col-md-9 text-right">
                                    <label><strong>Total Cost:</strong></label>
                                 </div>
                                 <div class="col-md-2">
                                    <input type="text" class="form-control" :value="totalCost.toFixed(2)" readonly>
                                 </div>
                              </div>

                           </div>
                        </div>{{-- /ingredients row --}}

                        {{-- Save button --}}
                        <div class="mt-3">
                           <button type="button" class="btn btn-primary" @click="saveProduct">
                              Save Product
                           </button>
                        </div>

                     </div>{{-- /col-md-8 --}}

                     {{-- Right column: Product Image --}}
                     <div class="col-md-4 mt-3">
                        <fieldset class="form-group">
                           <legend>Product Image</legend>
                           <div id="drop-area"
                              class="upload-box text-center p-3 border rounded"
                              onclick="document.getElementById('image').click();">
                              <i class="fas fa-hand-pointer fa-2x mb-2 text-muted"></i>
                              <p class="text-muted">
                                 Drag &amp; Drop an image for the product<br>
                                 <strong>(or) Select</strong>
                              </p>
                              <input type="file" id="image" name="image" class="d-none" accept="image/*">
                              <div id="preview-container" class="preview-box mt-3">
                                 @if(isset($product) && $product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                       alt="Product Image"
                                       class="img-thumbnail"
                                       style="max-width: 200px;">
                                 @endif
                              </div>
                           </div>
                        </fieldset>
                     </div>{{-- /col-md-4 --}}

                  </div>{{-- /inner row --}}
               </div>{{-- /col-sm-12 --}}
            </div>{{-- /outer row --}}

         </form>
      </div>{{-- /card-body --}}
   </div>{{-- /card --}}

</div>{{-- /main-content --}}

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput        = document.getElementById('image');
    const dropArea         = document.getElementById('drop-area');
    const previewContainer = document.getElementById('preview-container');

    // Prevent default drag behaviors on drop area and body
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, e => e.preventDefault(), false);
        document.body.addEventListener(eventName, e => e.preventDefault(), false);
    });

    dropArea.addEventListener('dragover',  () => dropArea.classList.add('border-primary'),    false);
    dropArea.addEventListener('dragleave', () => dropArea.classList.remove('border-primary'),  false);

    dropArea.addEventListener('drop', e => {
        dropArea.classList.remove('border-primary');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            previewFile(files[0]);
        }
    }, false);

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) previewFile(fileInput.files[0]);
    });

    function previewFile(file) {
        previewContainer.innerHTML = '';
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('img-thumbnail');
                img.style.maxWidth = '200px';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }
});

function toggleStationForm() {
    const form = document.getElementById('newSStationForm');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    clearStationFormErrors();
}

function clearStationFormErrors() {
    document.getElementById('new_station_name').classList.remove('is-invalid');
    document.getElementById('new_station_description').classList.remove('is-invalid');
    document.getElementById('err_new_station_name').innerText        = '';
    document.getElementById('err_new_station_description').innerText = '';
}

function saveStationForm() {
    clearStationFormErrors();

    const name        = document.getElementById('new_station_name').value.trim();
    const description = document.getElementById('new_station_description').value.trim();

    if (!name) {
        document.getElementById('new_station_name').classList.add('is-invalid');
        document.getElementById('err_new_station_name').innerText = 'Name is required.';
        return;
    }

    fetch("{{ route('stations.store') }}", {
        method:  'POST',
        headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({ name, description }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.errors) {
            if (data.errors.name) {
                document.getElementById('new_station_name').classList.add('is-invalid');
                document.getElementById('err_new_station_name').innerText = data.errors.name[0];
            }
            return;
        }

        const stationSelect = document.getElementById('station_id');
        if (stationSelect) {
            const option    = document.createElement('option');
            option.value    = data.station.id;
            option.text     = data.station.name;
            option.selected = true;
            stationSelect.appendChild(option);
        }

        document.getElementById('new_station_name').value        = '';
        document.getElementById('new_station_description').value = '';
        toggleStationForm();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong while saving the station.');
    });
}
</script>

<script>
new Vue({
    el: '#app',

    data: {
        components:          @json($components),
        categories:          @json($categories),
        category_id:         "{{ old('category_id') }}",
        subcategory_id:      "{{ old('subcategory_id') }}",
        subcategories:       [],
        showSubCategoryForm: false,
        showCategoryForm:    false,
        newCategory: {
            name:        '',
            description: '',
        },
        newSubCategory: {
            name:        '',
            description: '',
        },
        recipes:   [],
        errors:    {},
        subErrors: {},
    },

    computed: {
        totalCost() {
            return this.recipes.reduce((sum, row) => sum + (parseFloat(row.cost) || 0), 0);
        },
    },

    methods: {

        toggleCategoryForm() {
            this.showCategoryForm = !this.showCategoryForm;
            this.errors = {};
            if (!this.showCategoryForm) {
                this.newCategory.name        = '';
                this.newCategory.description = '';
            }
        },

        toggleSubCategoryForm() {
            this.showSubCategoryForm = !this.showSubCategoryForm;
            this.subErrors = {};
        },

        async loadSubcategories(categoryId) {
            try {
                const res  = await fetch(`/categories/${categoryId}/subcategories`);
                const data = await res.json();
                this.subcategories = data;
                if (!this.subcategory_id && data.length) {
                    this.subcategory_id = data[0].id;
                }
            } catch (err) {
                console.error('Failed to load subcategories', err);
            }
        },

        async saveCategory() {
            this.errors = {};

            if (!this.newCategory.name) {
                this.errors.name = 'Name is required.';
                return;
            }

            try {
                const res = await fetch("{{ route('categories.store') }}", {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(this.newCategory),
                });

                const data = await res.json();

                if (res.status === 422) {
                    Object.keys(data.errors).forEach(field => {
                        this.errors[field] = data.errors[field][0];
                    });
                    return;
                }

                if (!res.ok) throw new Error(data.message || 'Server error');

                this.categories.push(data);
                this.category_id             = data.id;
                this.newCategory.name        = '';
                this.newCategory.description = '';
                this.showCategoryForm        = false;

                Swal.fire({
                    icon:               'success',
                    title:              'Category created',
                    text:               data.name,
                    timer:              1500,
                    showConfirmButton:  false,
                });

            } catch (err) {
                Swal.fire('Error', err.message, 'error');
            }
        },

        async saveSubCategory() {
            this.subErrors = {};

            if (!this.newSubCategory.name) {
                this.subErrors.name = 'Name is required';
                return;
            }

            if (!this.category_id) {
                Swal.fire('Error', 'Select category first', 'error');
                return;
            }

            try {
                const res = await fetch("{{ route('subcategories.store') }}", {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ ...this.newSubCategory, category_id: this.category_id }),
                });

                const data = await res.json();

                if (res.status === 422) {
                    Object.keys(data.errors).forEach(field => {
                        this.subErrors[field] = data.errors[field][0];
                    });
                    return;
                }

                if (!res.ok) throw new Error(data.message || 'Server error');

                this.subcategories.push(data);
                this.subcategory_id             = data.id;
                this.newSubCategory.name        = '';
                this.newSubCategory.description = '';
                this.showSubCategoryForm        = false;

                Swal.fire({
                    icon:              'success',
                    title:             'Subcategory created',
                    text:              data.name,
                    timer:             1500,
                    showConfirmButton: false,
                });

            } catch (err) {
                Swal.fire('Error', err.message, 'error');
            }
        },

        addRow() {
            this.recipes.push({ component_id: '', quantity: 0, unit: '', cost: 0 });

            this.$nextTick(() => {
                const selects    = document.querySelectorAll('.component-select');
                const lastSelect = selects[selects.length - 1];

                $(lastSelect)
                    .select2({ width: '100%' })
                    .on('change', e => {
                        const index = selects.length - 1;
                        this.recipes[index].component_id = e.target.value;
                        this.updateRow(this.recipes[index]);
                    });
            });
        },

        removeRow(index) {
            this.recipes.splice(index, 1);
        },

        updateRow(row) {
            const component = this.components.find(c => c.id == row.component_id);
            if (!component) {
                row.unit = '';
                row.cost = 0;
                return;
            }
            row.unit = component.unit;
            row.cost = (parseFloat(component.cost) || 0) * (parseFloat(row.quantity) || 0);
        },

        async saveProduct() {
            if (this.recipes.length < 1) {
                Swal.fire({
                    icon:  'warning',
                    title: 'Validation Error',
                    text:  'Please add at least 1 recipe before saving.',
                });
                return;
            }

            const form     = document.getElementById('productForm');
            const formData = new FormData(form);

            try {
                const res = await fetch("{{ route('products.store') }}", {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept':       'application/json',
                    },
                    body: formData,
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        const messages = Object.values(data.errors).flat();
                        Swal.fire({ icon: 'error', title: 'Validation Error', html: messages.join('<br>') });
                    } else {
                        throw new Error(data.message || 'Server error');
                    }
                    return;
                }

                Swal.fire({
                    icon:              'success',
                    title:             'Product Created',
                    text:              data.message || 'Your product was successfully created!',
                    timer:             2000,
                    showConfirmButton: false,
                });

                form.reset();
                this.recipes        = [];
                this.category_id    = '';
                this.subcategory_id = '';

            } catch (err) {
                console.error('Error saving product:', err);
                Swal.fire('Error', err.message, 'error');
            }
        },

    },

    watch: {
        category_id: {
            immediate: true,
            handler(val) {
                if (!val) {
                    this.subcategories  = [];
                    this.subcategory_id = '';
                    return;
                }
                this.loadSubcategories(val);
            },
        },
    },

    mounted() {
        this.addRow();
    },
});
</script>

@endsection
