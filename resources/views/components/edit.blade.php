@extends('layouts.app')
@section('content')
<div class="main-content">
    <div>
        <div class="breadcrumb">
            <h1 class="mr-3">Edit Component</h1>
            <ul>
                <li><a href="{{ route('components.index') }}">Inventory</a></li>
                <li>Edit</li>
            </ul>
            <div class="breadcrumb-action"></div>
        </div>
        <div class="separator-breadcrumb border-top"></div>
    </div>

    <div class="wrapper">

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <form id="updateComponentForm" action="{{ route('components.update', $component->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="mt-3 col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">

                                        {{-- ===================== LEFT COLUMN ===================== --}}
                                        <div class="col-md-6">

                                            {{-- SKU (readonly on edit) --}}
                                            <fieldset class="form-group">
                                                <legend class="col-form-label pt-0">SKU (Component Code) *</legend>
                                                <input type="text" readonly class="form-control" id="code" name="code"
                                                    value="{{ old('code', $component->code) }}"
                                                    placeholder="Components SKU">
                                            </fieldset>

                                            {{-- Category --}}
                                            <div class="form-group">
                                                <label for="category_id">Category</label>
                                                <div class="d-flex">
                                                    <select name="category_id" id="category_id" class="form-control mr-2">
                                                        <option value="" disabled selected></option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}"
                                                                {{ old('category_id', $component->category_id) == $category->id ? 'selected' : '' }}>
                                                                {{ $category->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleCategoryForm()">
                                                        <i class="i-Add"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Inline Add Category Form --}}
                                            <div id="newCategoryForm" class="border rounded p-4 mt-3 bg-white shadow-sm" style="display:none;">
                                                <h4 class="text-center mb-4">Add Category</h4>
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Category Name *</label>
                                                    <input type="text" id="new_category_name" class="form-control" placeholder="Enter category name">
                                                    <div class="invalid-feedback" id="err_new_category_name"></div>
                                                </div>
                                                <div class="form-group mt-3">
                                                    <label class="font-weight-bold">Description</label>
                                                    <textarea id="new_category_description" class="form-control" rows="3" placeholder="Enter category description"></textarea>
                                                    <div class="invalid-feedback" id="err_new_category_description"></div>
                                                </div>
                                                <div class="d-flex justify-content-center mt-4">
                                                    <button type="button" onclick="saveCategory()" class="btn btn-success px-4 mr-2">Save</button>
                                                    <button type="button" onclick="toggleCategoryForm()" class="btn btn-danger px-4">Cancel</button>
                                                </div>
                                            </div>

                                            {{-- Purchase Cost & Selling Price --}}
                                            <div class="row mt-2">
                                                <div class="col-md-6">
                                                    <fieldset class="form-group">
                                                        <legend class="col-form-label pt-0">Purchase Cost *</legend>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">₱</span>
                                                            </div>
                                                            <input class="form-control" placeholder="0" id="cost" name="cost"
                                                                value="{{ old('cost', $branchComponent->cost) }}" inputmode="decimal">
                                                        </div>
                                                    </fieldset>
                                                </div>

                                                <div class="col-md-6">
                                                    <fieldset class="form-group">
                                                        <legend class="col-form-label pt-0">Selling Price *</legend>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">₱</span>
                                                            </div>
                                                            <input class="form-control" placeholder="0" id="price" name="price"
                                                                value="{{ old('price', $branchComponent->price) }}" inputmode="decimal">
                                                        </div>
                                                    </fieldset>
                                                </div>

                                                {{-- QTY on Hand (NO peso sign) --}}
                                                <div class="col-md-6">
                                                    <fieldset class="form-group">
                                                        <legend class="col-form-label pt-0">QTY on Hand *</legend>
                                                        <input class="form-control" placeholder="0" id="onhand" name="onhand"
                                                            value="{{ old('onhand', $branchComponent->onhand) }}" inputmode="decimal">
                                                    </fieldset>
                                                </div>

                                                {{-- Unit — SELECT listbox (change 2) --}}
                                                <div class="col-md-6">
                                                    <fieldset class="form-group">
                                                        <legend class="col-form-label pt-0">Unit *</legend>
                                                        <select class="form-control" id="unit_id" name="unit_id">
                                                            <option value="" disabled>Select Unit</option>
                                                            @foreach ($units as $u)
                                                                <option value="{{ $u->id }}"
                                                                    {{ old('unit_id', $component->unit_id) == $u->id ? 'selected' : '' }}>
                                                                    {{ $u->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </fieldset>
                                                </div>
                                            </div>

                                            {{-- Brand Name + Supplier SIDE BY SIDE --}}
                                            <div class="row mt-2">
                                            <div class="col-md-6">
                                                <fieldset class="form-group">
                                                    <legend class="col-form-label pt-0">Brand Name *</legend>
                                                    <input type="text"
                                                        class="form-control"
                                                        id="brand_name"
                                                        name="brand_name"
                                                        placeholder="Enter Brand Here"
                                                        value="{{ old('brand_name', $component->brand_name) }}">
                                                </fieldset>
                                            </div>

                                            {{-- Supplier --}}
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="supplier_id">Supplier *</label>
                                                    <div class="d-flex">
                                                        <select class="form-control mr-2" id="supplier_id" name="supplier_id">
                                                            <option value="">Select Supplier</option>
                                                            @foreach ($suppliers as $supplier)
                                                                <option value="{{ $supplier->id }}"
                                                                    {{ old('supplier_id', $component->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                                    {{ $supplier->fullname }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button"
                                                                class="btn btn-outline-success btn-sm"
                                                                onclick="toggleSupplierForm()">
                                                            <i class="i-Add"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        {{-- Inline Add Supplier Form --}}
                                            <div id="newSupplierForm" class="border rounded p-4 mt-3 bg-white shadow-sm" style="display:none;">
                                                <h4 class="text-center mb-4">Add Supplier</h4>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">First Name *</label>
                                                            <input type="text" id="new_supplier_firstname" class="form-control" placeholder="First name">
                                                            <div class="invalid-feedback" id="err_new_supplier_firstname"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Last Name *</label>
                                                            <input type="text" id="new_supplier_lastname" class="form-control" placeholder="Last name">
                                                            <div class="invalid-feedback" id="err_new_supplier_lastname"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mt-2">
                                                    <label class="font-weight-bold">Company Name</label>
                                                    <input type="text" id="new_supplier_company" class="form-control" placeholder="Company name">
                                                    <div class="invalid-feedback" id="err_new_supplier_company"></div>
                                                </div>
                                                <div class="form-group mt-2">
                                                    <label class="font-weight-bold">Email</label>
                                                    <input type="email" id="new_supplier_email" class="form-control" placeholder="Email address">
                                                    <div class="invalid-feedback" id="err_new_supplier_email"></div>
                                                </div>
                                                <div class="form-group mt-2">
                                                    <label class="font-weight-bold">Phone</label>
                                                    <input type="text" id="new_supplier_phone" class="form-control" placeholder="Phone number">
                                                    <div class="invalid-feedback" id="err_new_supplier_phone"></div>
                                                </div>
                                                <div class="d-flex justify-content-center mt-4">
                                                    <button type="button" onclick="saveSupplier()" class="btn btn-success px-4 mr-2">Save</button>
                                                    <button type="button" onclick="toggleSupplierForm()" class="btn btn-danger px-4">Cancel</button>
                                                </div>
                                            </div>

                                            {{-- For Sale --}}
                                            <fieldset class="form-group">
                                                <legend class="col-form-label pt-0">For Sale</legend>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="for_sale" name="for_sale" value="1"
                                                        {{ old('for_sale', $component->for_sale) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="for_sale">Yes</label>
                                                </div>
                                            </fieldset>

                                        </div>{{-- end left col --}}

                                        {{-- ===================== RIGHT COLUMN ===================== --}}
                                        <div class="col-md-6">

                                            {{-- Component Name --}}
                                            <fieldset class="form-group">
                                                <legend class="col-form-label pt-0">Component Name *</legend>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    value="{{ old('name', $component->name) }}"
                                                    placeholder="Enter Component Name">
                                            </fieldset>

                                            {{-- Subcategory --}}
                                            <div class="form-group">
                                                <label for="subcategory_id">Subcategory</label>
                                                <div class="d-flex">
                                                    <select name="subcategory_id" id="subcategory_id" class="form-control mr-2">
                                                        {{-- filled dynamically --}}
                                                    </select>
                                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleSubCategoryForm()">
                                                        <i class="i-Add"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Inline Add Subcategory Form --}}
                                            <div id="newSubCategoryForm" class="border rounded p-4 mt-3 bg-white shadow-sm" style="display:none;">
                                                <h4 class="text-center mb-4">Add Sub Category</h4>
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Subcategory Name *</label>
                                                    <input type="text" id="new_subcategory_name" class="form-control" placeholder="Enter subcategory name">
                                                    <div class="invalid-feedback" id="err_new_subcategory_name"></div>
                                                </div>
                                                <div class="form-group mt-3">
                                                    <label class="font-weight-bold">Description</label>
                                                    <textarea id="new_subcategory_description" class="form-control" rows="3" placeholder="Enter subcategory description"></textarea>
                                                    <div class="invalid-feedback" id="err_new_subcategory_description"></div>
                                                </div>
                                                <div class="d-flex justify-content-center mt-4">
                                                    <button type="button" onclick="saveSubCategory()" class="btn btn-success px-4 mr-2">Save</button>
                                                    <button type="button" onclick="toggleSubCategoryForm()" class="btn btn-danger px-4">Cancel</button>
                                                </div>
                                            </div>

                                            

                                            {{-- Component Image --}}
                                            <fieldset class="form-group mt-3">
                                                <legend>Component Image</legend>
                                                <div id="drop-area" class="upload-box text-center p-3 border rounded"
                                                    onclick="document.getElementById('image').click();">
                                                    <i class="fas fa-hand-pointer fa-2x mb-2 text-muted"></i>
                                                    <p class="text-muted">Drag & Drop an image for the component<br><strong>(or) Select</strong></p>
                                                    <input type="file" id="image" name="image" class="d-none" accept="image/*">
                                                    <div id="preview-container" class="preview-box mt-3">
                                                        @if($component->image)
                                                            <img src="{{ asset('storage/' . $component->image) }}"
                                                                alt="Component Image" class="img-thumbnail" style="max-width: 200px;">
                                                        @endif
                                                    </div>
                                                </div>
                                            </fieldset>

                                        </div>{{-- end right col --}}
                                    </div>{{-- end row --}}
                                </div>{{-- card-body --}}
                            </div>{{-- card --}}
                        </div>{{-- col-md-8 --}}
                    </div>{{-- row --}}

                    <div class="mt-3 ml-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="i-Yes me-2 font-weight-bold"></i> Update Component
                        </button>
                    </div>
                </div>{{-- col-sm-12 --}}
            </div>{{-- row --}}
        </form>
    </div>
</div>

<script>
// ==================== CATEGORY ====================
function toggleCategoryForm() {
    const form = document.getElementById('newCategoryForm');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    clearErrors(['new_category_name','new_category_description'], 'err_new_category_');
}

async function saveCategory() {
    clearErrors(['new_category_name','new_category_description'], 'err_new_category_');
    const name        = document.getElementById('new_category_name').value.trim();
    const description = document.getElementById('new_category_description').value.trim();

    if (!name) { setError('new_category_name', 'err_new_category_name', 'Name is required.'); return; }

    try {
        const res  = await fetch("{{ route('categories.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ name, description })
        });
        const contentType = res.headers.get('content-type') || '';
        const data = contentType.includes('application/json') ? await res.json() : null;

        if (!res.ok) {
            if (res.status === 422 && data?.errors) { showFieldErrors(data.errors, 'new_category_', 'err_new_category_'); return; }
            throw new Error((data?.message) || res.statusText);
        }
        const category = data?.id ? data : null;
        if (category) {
            document.getElementById('category_id').add(new Option(category.name, category.id, true, true));
            document.getElementById('new_category_name').value = '';
            document.getElementById('new_category_description').value = '';
            toggleCategoryForm();
            Swal.fire({ icon: 'success', title: 'Category created', text: category.name, timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Success', 'Category created', 'success');
        }
    } catch (err) { Swal.fire('Error', 'Something went wrong: ' + err.message, 'error'); }
}

// ==================== SUBCATEGORY ====================
document.getElementById('category_id').addEventListener('change', async function () {
    const categoryId        = this.value;
    const subcategorySelect  = document.getElementById('subcategory_id');
    subcategorySelect.innerHTML = '';
    if (!categoryId) return;

    try {
        const res            = await fetch(`/categories/${categoryId}/subcategories`);
        const subcategories  = await res.json();
        const oldSub         = "{{ old('subcategory_id', $component->subcategory_id) }}";
        subcategories.forEach(sub => {
            const opt = new Option(sub.name, sub.id);
            if (oldSub && oldSub == sub.id) opt.selected = true;
            subcategorySelect.add(opt);
        });
        if (!oldSub && subcategories.length > 0) subcategorySelect.options[0].selected = true;
    } catch (err) { console.error('Failed to load subcategories:', err); }
});

document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('category_id').value;
    if (sel) document.getElementById('category_id').dispatchEvent(new Event('change'));
});

function toggleSubCategoryForm() {
    const form = document.getElementById('newSubCategoryForm');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    clearErrors(['new_subcategory_name','new_subcategory_description'], 'err_new_subcategory_');
}

async function saveSubCategory() {
    clearErrors(['new_subcategory_name','new_subcategory_description'], 'err_new_subcategory_');
    const name        = document.getElementById('new_subcategory_name').value.trim();
    const description = document.getElementById('new_subcategory_description').value.trim();
    const category_id = document.getElementById('category_id').value;

    if (!name) { setError('new_subcategory_name', 'err_new_subcategory_name', 'Name is required.'); return; }
    if (!category_id) { Swal.fire('Error', 'Please select a parent category first.', 'error'); return; }

    try {
        const res  = await fetch("{{ route('subcategories.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ name, description, category_id })
        });
        const contentType = res.headers.get('content-type') || '';
        const data = contentType.includes('application/json') ? await res.json() : null;

        if (!res.ok) {
            if (res.status === 422 && data?.errors) { showFieldErrors(data.errors, 'new_subcategory_', 'err_new_subcategory_'); return; }
            throw new Error((data?.message) || res.statusText);
        }
        const sub = data?.id ? data : null;
        if (sub) {
            document.getElementById('subcategory_id').add(new Option(sub.name, sub.id, true, true));
            document.getElementById('new_subcategory_name').value = '';
            document.getElementById('new_subcategory_description').value = '';
            toggleSubCategoryForm();
            Swal.fire({ icon: 'success', title: 'Subcategory created', text: sub.name, timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Success', 'Subcategory created', 'success');
        }
    } catch (err) { Swal.fire('Error', 'Something went wrong: ' + err.message, 'error'); }
}

// ==================== SUPPLIER ====================
function toggleSupplierForm() {
    const form = document.getElementById('newSupplierForm');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    clearErrors(['new_supplier_firstname','new_supplier_lastname','new_supplier_company','new_supplier_email','new_supplier_phone'], 'err_new_supplier_');
}

async function saveSupplier() {

    clearErrors(
        ['new_supplier_firstname','new_supplier_lastname','new_supplier_company','new_supplier_email','new_supplier_phone'],
        'err_new_supplier_'
    );

    const firstname = document.getElementById('new_supplier_firstname').value.trim();
    const lastname  = document.getElementById('new_supplier_lastname').value.trim();
    const company   = document.getElementById('new_supplier_company').value.trim();
    const email     = document.getElementById('new_supplier_email').value.trim();
    const phone     = document.getElementById('new_supplier_phone').value.trim();

    if (!firstname) {
        setError('new_supplier_firstname','err_new_supplier_firstname','First name is required.');
        return;
    }

    if (!lastname) {
        setError('new_supplier_lastname','err_new_supplier_lastname','Last name is required.');
        return;
    }

    const fullname = firstname + ' ' + lastname;

    try {

        const res = await fetch("{{ route('suppliers.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                fullname: fullname,
                mobile_no: phone,
                email: email,
                company: company
            })
        });

        const data = await res.json();

        if (!res.ok) {
            if (res.status === 422 && data.errors) {
                Object.keys(data.errors).forEach(field => {
                    setError(
                        'new_supplier_' + field,
                        'err_new_supplier_' + field,
                        data.errors[field][0]
                    );
                });
                return;
            }
            throw new Error(data.message || 'Error saving supplier');
        }

        // Add to dropdown and auto select
        document.getElementById('supplier_id')
            .add(new Option(data.fullname, data.id, true, true));

        // Clear fields
        document.getElementById('new_supplier_firstname').value = '';
        document.getElementById('new_supplier_lastname').value  = '';
        document.getElementById('new_supplier_company').value   = '';
        document.getElementById('new_supplier_email').value     = '';
        document.getElementById('new_supplier_phone').value     = '';

        toggleSupplierForm();

        Swal.fire({
            icon: 'success',
            title: 'Supplier Created',
            text: data.fullname,
            timer: 1500,
            showConfirmButton: false
        });

    } catch (err) {
        Swal.fire('Error', err.message, 'error');
    }
}

// ==================== IMAGE DRAG & DROP ====================
document.addEventListener('DOMContentLoaded', function () {
    const fileInput        = document.getElementById('image');
    const dropArea         = document.getElementById('drop-area');
    const previewContainer = document.getElementById('preview-container');

    ['dragenter','dragover','dragleave','drop'].forEach(ev => {
        dropArea.addEventListener(ev, e => e.preventDefault(), false);
        document.body.addEventListener(ev, e => e.preventDefault(), false);
    });
    dropArea.addEventListener('dragover',  () => dropArea.classList.add('border-primary'));
    dropArea.addEventListener('dragleave', () => dropArea.classList.remove('border-primary'));
    dropArea.addEventListener('drop', e => {
        dropArea.classList.remove('border-primary');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            previewFile(e.dataTransfer.files[0]);
        }
    });
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

// ==================== HELPERS ====================
function setError(inputId, feedbackId, message) {
    document.getElementById(inputId).classList.add('is-invalid');
    document.getElementById(feedbackId).innerText = message;
}

function clearErrors(inputIds, feedbackPrefix) {
    inputIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
        const key = id.replace(/^new_[a-z]+_/, '');
        const fb  = document.getElementById(feedbackPrefix + key);
        if (fb) fb.innerText = '';
    });
}

function showFieldErrors(errors, inputPrefix, feedbackPrefix) {
    for (const [field, messages] of Object.entries(errors)) {
        const input    = document.getElementById(inputPrefix + field);
        const feedback = document.getElementById(feedbackPrefix + field);
        if (input)    input.classList.add('is-invalid');
        if (feedback) feedback.innerText = messages.join(' ');
    }
}

// Update Button
document.getElementById('updateComponentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to update this component?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData(form);

            if (!formData.get('supplier_id')) {
                formData.delete('supplier_id');
            }

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    let messages = [];
                    if (data.errors) {
                        for (const [field, msgs] of Object.entries(data.errors)) {
                            messages.push(`<strong>${field}</strong>: ${msgs.join(', ')}`);
                        }
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        html: messages.join('<br>') || 'Something went wrong!',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Updated!',
                    html: 'Component updated successfully!',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    willClose: () => {
                        window.location.href = "{{ route('components.index') }}";
                    }
                });

            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: err.message || 'Something went wrong!',
                });
            }
        }
    });
});
</script>
@endsection