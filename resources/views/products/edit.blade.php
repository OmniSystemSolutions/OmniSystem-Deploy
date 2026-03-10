@extends('layouts.app')
@section('content')
<div class="main-content" id="app">
      <div>
         <div class="breadcrumb">
               <h1 class="mr-3">Edit Product</h1>
               <ul>
                  <li><a href=""> Inventory </a></li>
                  <!----> <!---->
               </ul>
               <div class="breadcrumb-action"></div>
         </div>
         <div class="separator-breadcrumb border-top"></div>
      </div>
      <!----> 
      <div class="card mt-4">
         <div class="card-body">
               <form action="{{ route('products.update', $product->id) }}"
                  method="POST"
                  id="productForm"
                  enctype="multipart/form-data"
                  @submit.prevent="submitForm"
               >
                  @csrf
                  @method('PUT')

                  <input type="hidden" name="product_id" value="{{ $product->id }}">
<input type="hidden" name="branch_product_id" value="{{ $branchProduct->id }}">
                  <div class="row">
                  <div class="top-wrapper" style="display: none;">
                  </div>
                  <div class="col-sm-12">
                     <div class="row">
                           <div class="mt-3 col-md-8">

                                 <!----><!---->
                                 <div class="row">
                                       <div class="col-md-6">
                                          <span>
                                          <fieldset class="form-group" id="__BVID__358">
                                             <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0" id="__BVID__358__BV_label_">SKU(Product Code) *</legend>
                                             <div>
                                                   <input type="text" readonly
                                                      name="code" 
                                                      id="code" 
                                                      class="form-control @error('code') is-invalid @enderror" 
                                                      value="{{ old('code', $product->code) }}" 
                                                      placeholder="Enter SKU">
                                                   @error('code')
                                                      <div class="invalid-feedback">{{ $message }}</div>
                                                   @enderror
                                                   <div id="SKU-feedback" class="invalid-feedback"></div>
                                             </div>
                                          </fieldset>
                                          </span>

                                          <!-- Category select + New button -->
                                 <div class="form-group">
                                       <label for="category_id">Category</label>
                                       <div class="d-flex">
                                          <select name="category_id" id="category_id" class="form-control mr-2">
                                          @foreach ($categories as $category)
                                             <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                      {{ $category->name }}
                                             </option>
                                          @endforeach
                                          </select>
                                          <button type="button" id="toggleCategoryBtn" class="btn btn-outline-success btn-sm" onclick="toggleCategoryForm()">
                                          <i class="i-Add"></i>
                                          </button>
                                       </div>
                                 </div>

                                 <!-- Inline new category form (hidden) -->
                                 <div id="newCategoryForm" class="border rounded p-4 mt-3 bg-white shadow-sm" style="display:none; max-width:600px; margin:auto;">
                                       <h4 class="text-center mb-4">Add Category</h4>

                                       <div class="form-group">
                                          <label for="new_category_name" class="font-weight-bold">Category Name *</label>
                                          <input type="text" id="new_category_name" class="form-control" placeholder="Enter category name">
                                          <div class="invalid-feedback" id="err_new_category_name"></div>
                                       </div>

                                       <div class="form-group mt-3">
                                          <label for="new_category_description" class="font-weight-bold">Description</label>
                                          <textarea id="new_category_description" class="form-control" rows="3" placeholder="Enter category description"></textarea>
                                          <div class="invalid-feedback" id="err_new_category_description"></div>
                                       </div>

                                       <div class="d-flex justify-content-center mt-4">
                                          <button type="button" onclick="saveCategory()" class="btn btn-success px-4 mr-2">Save</button>
                                          <button type="button" onclick="toggleCategoryForm()" class="btn btn-danger px-4">Cancel</button>
                                       </div>
                                 </div>

                                 <!-- JS: improved fetch + error handling -->
                                 <script>
                                 function toggleCategoryForm() {
                                       const form = document.getElementById('newCategoryForm');
                                       form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
                                       clearCategoryFormErrors();
                                 }

                                 function clearCategoryFormErrors() {
                                       document.getElementById('new_category_name').classList.remove('is-invalid');
                                       document.getElementById('new_category_description').classList.remove('is-invalid');
                                       document.getElementById('err_new_category_name').innerText = '';
                                       document.getElementById('err_new_category_description').innerText = '';
                                 }

                                 async function saveCategory() {
                                       clearCategoryFormErrors();

                                       const name = document.getElementById('new_category_name').value.trim();
                                       const description = document.getElementById('new_category_description').value.trim();

                                       if (!name) {
                                          document.getElementById('new_category_name').classList.add('is-invalid');
                                          document.getElementById('err_new_category_name').innerText = 'Name is required.';
                                          return;
                                       }

                                       try {
                                          const res = await fetch("{{ route('categories.store') }}", {
                                             method: "POST",
                                             headers: {
                                                   "Content-Type": "application/json",
                                                   "Accept": "application/json",                 // <-- important so Laravel returns JSON
                                                   "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                                   "X-Requested-With": "XMLHttpRequest"         // <-- helps Laravel detect AJAX
                                             },
                                             body: JSON.stringify({ name, description })
                                          });

                                          // attempt to parse JSON if server returns JSON
                                          const contentType = res.headers.get('content-type') || '';
                                          const data = contentType.includes('application/json') ? await res.json() : null;

                                          if (!res.ok) {
                                             // Validation errors (Laravel returns 422 with { errors: { field: [...] } })
                                             if (res.status === 422 && data && data.errors) {
                                                   showValidationErrors(data.errors);
                                                   return;
                                             }
                                             // Other errors: show friendly message
                                             const msg = (data && data.message) ? data.message : res.statusText || 'Server error';
                                             throw new Error(msg);
                                          }

                                          // Success: data should be object with id, name, etc.
                                          const category = (data && data.id) ? data : null;
                                          if (!category) {
                                             // fallback if server returned entire model
                                             Swal.fire('Success', 'Category created', 'success');
                                          } else {
                                             // add new category to select and auto-select it
                                             const select = document.getElementById('category_id');
                                             const option = new Option(category.name, category.id, true, true);
                                             select.add(option);
                                             // hide + reset form
                                             document.getElementById('new_category_name').value = '';
                                             document.getElementById('new_category_description').value = '';
                                             toggleCategoryForm();

                                             Swal.fire({
                                                   icon: 'success',
                                                   title: 'Category created',
                                                   text: category.name,
                                                   timer: 1500,
                                                   showConfirmButton: false
                                             });
                                          }
                                       } catch (err) {
                                          console.error('Category create error:', err);
                                          Swal.fire('Error', 'Something went wrong: ' + err.message, 'error');
                                       }
                                 }

                                 function showValidationErrors(errors) {
                                       // errors is an object keyed by field name, e.g. { name: ["..."], description: ["..."] }
                                       for (const [field, messages] of Object.entries(errors)) {
                                          const input = document.getElementById('new_category_' + field);
                                          const feedback = document.getElementById('err_new_category_' + field);
                                          if (input) input.classList.add('is-invalid');
                                          if (feedback) feedback.innerText = messages.join(' ');
                                       }
                                 }
                                 </script>

                                          <span>
                                          <fieldset class="form-group" id="__BVID__408">
                                             <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0" id="__BVID__408__BV_label_">Unit Price *</legend>
                                             <div>
                                                   <input type="number" step="0.01" 
                                                      name="price" 
                                                      id="price" 
                                                      class="form-control @error('price') is-invalid @enderror" 
                                                      value="{{ old('price', $product->price) }}">
                                                   @error('price')
                                                      <div class="invalid-feedback">{{ $message }}</div>
                                                   @enderror
                                                   <div id="Price-feedback" class="invalid-feedback"></div>
                                                   <!----><!----><!---->
                                             </div>
                                          </fieldset>
                                          </span>

                                           <span>
                                          <fieldset class="form-group" id="__BVID__408">
                                             <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0" id="__BVID__408__BV_label_">Quantity</legend>
                                             <div>
                                                   <input type="number" step="0.01" 
                                                      name="quantity" 
                                                      id="quantity" 
                                                      class="form-control @error('quantity') is-invalid @enderror" 
                                                      value="{{ old('quantity', $product->quantity) }}">
                                                   @error('quantity')
                                                      <div class="invalid-feedback">{{ $message }}</div>
                                                   @enderror
                                                   <div id="Quantity-feedback" class="invalid-feedback"></div>
                                                   <!----><!----><!---->
                                             </div>
                                          </fieldset>
                                          </span>
                                          
                                       </div>
                                       <div class="col-md-6">
                                          <span>
                                          <fieldset class="form-group" id="__BVID__3161">
                                             <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0" id="__BVID__3161__BV_label_">Product Name *</legend>
                                             <div>
                                             <input type="text" 
                                                      name="name" 
                                                      id="name" 
                                                      class="form-control @error('name') is-invalid @enderror" 
                                                      value="{{ old('name', $product->name) }}" 
                                                      placeholder="Enter Name of Product">
                                                   @error('name')
                                                      <div class="invalid-feedback">{{ $message }}</div>
                                                   @enderror
                                                   <div id="Name-feedback" class="invalid-feedback"></div>
                                             </div>
                                          </fieldset>
                                          </span>
                                          
                     <!-- Subcategory select + New button -->
                     <div class="form-group">
                        <label for="subcategory_id">Subcategory</label>
                        <div class="d-flex">
                           <select name="subcategory_id" id="subcategory_id" class="form-control mr-2">
                              @foreach ($subcategories as $subcategory)
                                 <option value="{{ $subcategory->id }}" 
                                    {{ old('subcategory_id', $product->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                    {{ $subcategory->name }}
                                 </option>
                              @endforeach
                           </select>
                           <button type="button" id="toggleSubCategoryBtn" class="btn btn-outline-success btn-sm" onclick="toggleSubCategoryForm()">
                              <i class="i-Add"></i>
                           </button>
                        </div>
                     </div>


                     <script>
                        document.addEventListener('DOMContentLoaded', () => {
                           const categorySelect = document.getElementById('category_id');
                           const subcategorySelect = document.getElementById('subcategory_id');
                           const selectedSubcategoryId = "{{ old('subcategory_id', $product->subcategory_id) }}";

                           // Fetch subcategories when category changes
                           categorySelect.addEventListener('change', async function () {
                              const categoryId = this.value;
                              subcategorySelect.innerHTML = '';

                              if (!categoryId) return;

                              try {
                                 const res = await fetch(`/categories/${categoryId}/subcategories`);
                                 const subcategories = await res.json();

                                 subcategories.forEach(sub => {
                                    const option = new Option(sub.name, sub.id, false, sub.id == selectedSubcategoryId);
                                    subcategorySelect.add(option);
                                 });
                              } catch (error) {
                                 console.error('Failed to load subcategories:', error);
                              }
                           });

                           // ✅ Only auto-trigger if the category changed from original
                           const currentCategory = "{{ $product->category_id }}";
                           if (categorySelect.value && categorySelect.value != currentCategory) {
                              categorySelect.dispatchEvent(new Event('change'));
                           }
                        });
                        </script>


                                 <!-- Inline new subcategory form (hidden) -->
                                 <div id="newSubCategoryForm" class="border rounded p-4 mt-3 bg-white shadow-sm" style="display:none; max-width:600px; margin:auto;">
                                       <h4 class="text-center mb-4">Add Sub Category</h4>

                                       <div class="form-group">
                                          <label for="new_subcategory_name" class="font-weight-bold">Subcategory Name *</label>
                                          <input type="text" id="new_subcategory_name" class="form-control" placeholder="Enter subcategory name">
                                          <div class="invalid-feedback" id="err_new_subcategory_name"></div>
                                       </div>

                                       <div class="form-group mt-3">
                                          <label for="new_subcategory_description" class="font-weight-bold">Description</label>
                                          <textarea id="new_subcategory_description" class="form-control" rows="3" placeholder="Enter subcategory description"></textarea>
                                          <div class="invalid-feedback" id="err_new_subcategory_description"></div>
                                       </div>

                                       <div class="d-flex justify-content-center mt-4">
                                          <button type="button" onclick="saveSubCategory()" class="btn btn-success px-4 mr-2">Save</button>
                                          <button type="button" onclick="toggleSubCategoryForm()" class="btn btn-danger px-4">Cancel</button>
                                       </div>
                                 </div>

                                 <script>
                                 function toggleSubCategoryForm() {
                                       const form = document.getElementById('newSubCategoryForm');
                                       form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
                                       clearSubCategoryFormErrors();
                                 }

                                 function clearSubCategoryFormErrors() {
                                       document.getElementById('new_subcategory_name').classList.remove('is-invalid');
                                       document.getElementById('new_subcategory_description').classList.remove('is-invalid');
                                       document.getElementById('err_new_subcategory_name').innerText = '';
                                       document.getElementById('err_new_subcategory_description').innerText = '';
                                 }

                                 async function saveSubCategory() {
                                       clearSubCategoryFormErrors();

                                       const name = document.getElementById('new_subcategory_name').value.trim();
                                       const description = document.getElementById('new_subcategory_description').value.trim();
                                       const category_id = document.getElementById('category_id').value; // parent category selected

                                       if (!name) {
                                          document.getElementById('new_subcategory_name').classList.add('is-invalid');
                                          document.getElementById('err_new_subcategory_name').innerText = 'Name is required.';
                                          return;
                                       }

                                       if (!category_id) {
                                          Swal.fire('Error', 'Please select a parent category first.', 'error');
                                          return;
                                       }

                                       try {
                                          const res = await fetch("{{ route('subcategories.store') }}", {
                                             method: "POST",
                                             headers: {
                                                   "Content-Type": "application/json",
                                                   "Accept": "application/json",
                                                   "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                                   "X-Requested-With": "XMLHttpRequest"
                                             },
                                             body: JSON.stringify({ name, description, category_id })
                                          });

                                          const contentType = res.headers.get('content-type') || '';
                                          const data = contentType.includes('application/json') ? await res.json() : null;

                                          if (!res.ok) {
                                             if (res.status === 422 && data && data.errors) {
                                                   showSubCategoryValidationErrors(data.errors);
                                                   return;
                                             }
                                             const msg = (data && data.message) ? data.message : res.statusText || 'Server error';
                                             throw new Error(msg);
                                          }

                                          const subcategory = (data && data.id) ? data : null;
                                          if (!subcategory) {
                                             Swal.fire('Success', 'Subcategory created', 'success');
                                          } else {
                                             const select = document.getElementById('subcategory_id');
                                             const option = new Option(subcategory.name, subcategory.id, true, true);
                                             select.add(option);
                                             document.getElementById('new_subcategory_name').value = '';
                                             document.getElementById('new_subcategory_description').value = '';
                                             toggleSubCategoryForm();

                                             Swal.fire({
                                                   icon: 'success',
                                                   title: 'Subcategory created',
                                                   text: subcategory.name,
                                                   timer: 1500,
                                                   showConfirmButton: false
                                             });
                                          }
                                       } catch (err) {
                                          console.error('SubCategory create error:', err);
                                          Swal.fire('Error', 'Something went wrong: ' + err.message, 'error');
                                       }
                                 }

                                 function showSubCategoryValidationErrors(errors) {
                                       for (const [field, messages] of Object.entries(errors)) {
                                          const input = document.getElementById('new_subcategory_' + field);
                                          const feedback = document.getElementById('err_new_subcategory_' + field);
                                          if (input) input.classList.add('is-invalid');
                                          if (feedback) feedback.innerText = messages.join(' ');
                                       }
                                 }
                                 </script>

         <script>
      document.addEventListener('DOMContentLoaded', function () {
         const fileInput = document.getElementById('image');
         const dropArea = document.getElementById('drop-area');
         const previewContainer = document.getElementById('preview-container');

         // Allow click to trigger file input
         dropArea.addEventListener('click', () => fileInput.click());

         // Prevent default drag behaviors
         ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
               dropArea.addEventListener(eventName, (e) => e.preventDefault(), false);
               document.body.addEventListener(eventName, (e) => e.preventDefault(), false);
         });

         // Highlight drop area on dragover
         dropArea.addEventListener('dragover', () => {
               dropArea.classList.add('border-primary');
         }, false);

         dropArea.addEventListener('dragleave', () => {
               dropArea.classList.remove('border-primary');
         }, false);

         // Handle drop
         dropArea.addEventListener('drop', (e) => {
               dropArea.classList.remove('border-primary');
               const files = e.dataTransfer.files;
               if (files.length > 0) {
                  fileInput.files = files; // attach files to hidden input
                  previewFile(files[0]);   // show preview
               }
         }, false);

         // Handle file select (click input)
         fileInput.addEventListener('change', () => {
               if (fileInput.files.length > 0) {
                  previewFile(fileInput.files[0]);
               }
         });

         function previewFile(file) {
               previewContainer.innerHTML = ""; 
               if (file && file.type.startsWith('image/')) {
                  const reader = new FileReader();
                  reader.onload = (e) => {
                     const img = document.createElement('img');
                     img.src = e.target.result;
                     img.classList.add('img-thumbnail');
                     img.style.maxWidth = "200px";
                     previewContainer.appendChild(img);
                  };
                  reader.readAsDataURL(file);
               }
         }
      });
      </script>
         <!-- Station select + New button -->
         <div class="form-group">
            <label for="station_id">Station</label>
            <div class="d-flex">
               <select name="station_id" id="station_id" class="form-control mr-2">
                  @foreach ($stations as $station)
                     <option value="{{ $station->id }}" 
                        {{ old('station_id', $product->station_id) == $station->id ? 'selected' : '' }}>
                        {{ $station->name }}
                     </option>
                  @endforeach
               </select>
               <button type="button" id="toggleStationBtn" class="btn btn-outline-success btn-sm" onclick="toggleStationForm()">
                  <i class="i-Add"></i>
               </button>
            </div>
         </div>

         <!-- Inline new station form (hidden) -->
         <div id="newSStationForm" class="border rounded p-4 mt-3 bg-white shadow-sm" style="display:none; max-width:600px; margin:auto;">
            <h4 class="text-center mb-4">Add Station</h4>

            <div class="form-group">
               <label for="new_station_name" class="font-weight-bold">Station Name *</label>
               <input type="text" id="new_station_name" class="form-control" placeholder="Enter station name">
               <div class="invalid-feedback" id="err_new_station_name"></div>
            </div>

            <div class="form-group mt-3">
               <label for="new_station_description" class="font-weight-bold">Description</label>
               <textarea id="new_station_description" class="form-control" rows="3" placeholder="Enter station description"></textarea>
               <div class="invalid-feedback" id="err_new_station_description"></div>
            </div>

            <div class="d-flex justify-content-center mt-4">
               <button type="button" onclick="saveStationForm()" class="btn btn-success px-4 mr-2">Save</button>
               <button type="button" onclick="toggleStationForm()" class="btn btn-danger px-4">Cancel</button>
            </div>
         </div>
         <script>
            function toggleStationForm() {
                  const form = document.getElementById('newSStationForm');
                  form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
                  clearSStationFormErrors();
            }

            function clearSStationFormErrors() {
                  document.getElementById('new_station_name').classList.remove('is-invalid');
                  document.getElementById('new_station_description').classList.remove('is-invalid');
                  document.getElementById('err_new_station_name').innerText = '';
                  document.getElementById('err_new_station_description').innerText = '';
            }

            function saveStationForm() {
    clearSStationFormErrors();

    const name = document.getElementById('new_station_name').value.trim();
    const description = document.getElementById('new_station_description').value.trim();

    if (!name) {
        document.getElementById('new_station_name').classList.add('is-invalid');
        document.getElementById('err_new_station_name').innerText = 'Name is required.';
        return;
    }

    fetch("{{ route('stations.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({
            name: name,
            description: description
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.errors) {
            // Laravel validation errors
            if (data.errors.name) {
                document.getElementById('new_station_name').classList.add('is-invalid');
                document.getElementById('err_new_station_name').innerText = data.errors.name[0];
            }
            return;
        }

        // ✅ SUCCESS
        // Example: add station to select dropdown and auto-select
        const stationSelect = document.getElementById('station_id');
        if (stationSelect) {
            const option = document.createElement('option');
            option.value = data.station.id;
            option.text = data.station.name;
            option.selected = true;
            stationSelect.appendChild(option);
        }

        // Reset form
        document.getElementById('new_station_name').value = '';
        document.getElementById('new_station_description').value = '';

        // Hide form / modal if needed
        $('#addStationModal').modal('hide');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong while saving the station.');
    });
}

            
            </script>
            <!-- Unit select -->
         <div class="form-group">
            <label for="unit_id">Unit</label>
            <div class="d-flex">
               <select name="unit_id" id="unit_id" class="form-control mr-2">
                  @foreach ($units as $unit)
                     <option value="{{ $unit->id }}" 
                        {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }}
                     </option>
                  @endforeach
               </select>
            </div>
         </div>
               </div>
         </div>

      @php
         // Build $oldRecipes: prefer old() (after validation error), otherwise derive from $product->recipes
         $oldRecipes = old('recipes');
         if (is_null($oldRecipes)) {
               $oldRecipes = $product->recipes->map(function($r) {
                  return [
                     'id' => $r->id,
                     'component_id' => $r->component_id,
                     'quantity' => $r->quantity,
                  ];
               })->toArray();
         }
      @endphp

      <div class="col-md-12 mt-4">
      <h5>Ingredients</h5>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Component</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Cost</th>
                <th>
                    <button type="button"
                            class="btn btn-success btn-sm"
                            @click="addRow()">
                        +
                    </button>
                </th>
            </tr>
        </thead>

        <tbody>
            <tr v-for="(row, index) in recipes" :key="index">

                <input type="hidden"
                       :name="'recipes[' + index + '][id]'"
                       v-model="row.id">

                <td>
                    <select class="form-control"
                            :name="'recipes[' + index + '][component_id]'"
                            v-model="row.component_id"
                            @change="updateRow(row)">

                        <option value="">Select Component</option>

                        <option v-for="c in components"
                                :key="c.id"
                                :value="c.id"
                                :data-cost="c.cost"
                                :data-unit="c.unit">

                            @{{ c.name }}

                        </option>
                    </select>
                </td>

                <td>
                    <input type="number"
                           step="0.01"
                           class="form-control"
                           :name="'recipes[' + index + '][quantity]'"
                           v-model="row.quantity"
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
                           v-model="row.cost"
                           readonly>
                </td>

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

    <div class="row mt-2">
        <div class="col-md-9 text-right">
            <label><strong>Total Cost:</strong></label>
        </div>

        <div class="col-md-2">
            <input type="text"
                   class="form-control"
                   :value="totalCost"
                   readonly>
        </div>
    </div>

         <div class="mt-3 col-md-12">
               <div class="mr-2">
                  <div class="b-overlay-wrap position-relative d-inline-block btn-loader">
                  <button type="submit" class="btn btn-primary mt-3">Update Product</button>
                  </div>
               </div>
         </div>
      </div>
      </div>
               </form>

      
         <div class="row">

               <div class="col-md-10 mt-3 mt-md-0">
         <fieldset class="form-group">
                     <legend>Product Image</legend>
                     <div id="drop-area" class="upload-box text-center p-3 border rounded" onclick="document.getElementById('image').click();">
                     <i class="fas fa-hand-pointer fa-2x mb-2 text-muted"></i>
                     <p class="text-muted">Drag & Drop an image for the product<br><strong>(or) Select</strong></p>
                     
                     <input type="file" id="image" name="image" class="d-none" accept="image/*">

                     <!-- Preview -->
                     <div id="preview-container" class="preview-box mt-3">
                              @if(isset($product) && $product->image)
                                 <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image" 
                                       class="img-thumbnail" style="max-width: 200px;">
                              @endif
                     </div>
                     </div>
               </fieldset>
         </div>
</span>
</div>
</div>
<script>
new Vue({
    el: '#app',

    data: {
        components: @json($components),
        recipes: []
    },

    computed: {
        totalCost() {
            return this.recipes.reduce((sum, row) => {
                return sum + (parseFloat(row.cost) || 0);
            }, 0).toFixed(2);
        }
    },

    methods: {
      async submitForm() {

        const form = document.getElementById('productForm');
        const formData = new FormData(form);

        const confirm = await Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to update this product?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel'
        });

        if (!confirm.isConfirmed) return;

        try {

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {

                let errors = [];

                if (data.errors) {
                    Object.entries(data.errors).forEach(([field, msgs]) => {
                        errors.push(`<strong>${field}</strong>: ${msgs.join(', ')}`);
                    });
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errors.join('<br>') || 'Something went wrong.'
                });

                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Product updated successfully.',
                timer: 1800,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "{{ route('products.index') }}";
            });

        } catch (error) {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Unexpected error occurred.'
            });

        }
    },
        addRow() {
            this.recipes.push({
                id: '',
                component_id: '',
                quantity: 0,
                unit: '',
                cost: 0
            });
        },

        removeRow(index) {
            this.recipes.splice(index, 1);
        },

        updateRow(row) {
    const component = this.components.find(
        c => c.id == row.component_id
    );

    if (!component) {
        row.unit = '';
        row.cost = 0;
        return;
    }

    // FIX HERE
    row.unit = component.unit?.name || '';

    const baseCost = parseFloat(component.cost) || 0;
    const qty = parseFloat(row.quantity) || 0;

    row.cost = (baseCost * qty).toFixed(2);
}
    },

    mounted() {
     // load recipes from Laravel
    this.recipes = @json($oldRecipes);

    // compute unit and cost
    this.recipes.forEach(row => {
        this.updateRow(row);
    });

    // if no recipes exist
    if (this.recipes.length === 0) {
        this.addRow();
    }
    }
});
</script>
@endsection