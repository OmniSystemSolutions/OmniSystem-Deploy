@extends('layouts.app')
@section('content')

<div class="main-content">
    <div>
        <div class="breadcrumb">
            <h1 class="mr-3">Suppliers</h1>
            <ul>
                <li><a href=""> Settings </a></li>
            </ul>
            <div class="breadcrumb-action"></div>
        </div>
        <div class="separator-breadcrumb border-top"></div>
    </div>

    <div class="card wrapper">
        <div class="card-body">
            <nav class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a href="{{ route('suppliers.index', ['status' => 'active']) }}" class="nav-link {{ $status === 'active' ? 'active' : '' }}">Active</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('suppliers.index', ['status' => 'archived']) }}" class="nav-link {{ $status === 'archived' ? 'active' : '' }}">Archived</a>
                </li>
            </ul>
        </nav>
            <div class="vgt-wrap">
                <div class="vgt-inner-wrap">
                    <div class="vgt-global-search vgt-clearfix">
                        <div class="vgt-global-search__input vgt-pull-left">
                            <form role="search">
                                <label for="vgt-search-suppliers">
                                    <span aria-hidden="true" class="input__icon">
                                        <div class="magnifying-glass"></div>
                                    </span>
                                    <span class="sr-only">Search</span>
                                </label>
                                <input id="vgt-search-suppliers" type="text" placeholder="Search this table" class="vgt-input vgt-pull-left">
                            </form>
                        </div>

                        <div class="vgt-global-search__actions vgt-pull-right">
                            <div class="mt-2 mb-3">
                                <button type="button" class="btn btn-outline-success ripple m-1 btn-sm">
                                    <i class="i-File-Copy"></i> PDF
                                </button>
                                <button class="btn btn-sm btn-outline-danger ripple m-1">
                                    <i class="i-File-Excel"></i> EXCEL
                                </button>
                                <button type="button" class="btn btn-rounded btn-primary btn-icon m-1"
                                    data-bs-toggle="modal" data-bs-target="#New_Supplier">
                                    <i class="i-Add"></i> Add
                                </button>

                                <!-- Add Supplier Modal -->
                                <div class="modal fade" id="New_Supplier" tabindex="-1" role="dialog" aria-labelledby="New_SupplierLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="New_SupplierLabel">Add Supplier</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                                            </div>

                                            <form action="#" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <input type="hidden" name="id" value="">
                                                        <div class="col-md-6">
                                                            <label>Supplier Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="supplier_name" class="form-control" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Contact Person</label>
                                                            <input type="text" name="contact_person" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Mobile #</label>
                                                            <input type="text" name="mobile_no" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Landline #</label>
                                                            <input type="text" name="landline_no" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Email</label>
                                                            <input type="email" name="email" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Supplier Since</label>
                                                            <input type="date" name="supplier_since" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>TIN</label>
                                                            <input type="text" name="tin" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                        <label for="type">Supplier Type <span class="text-danger">*</span></label>
                                                        <select name="supplier_type" id="supplier_type" class="form-control" required>
                                                            <option value="" disabled selected>Select Supplier Type</option>
                                                            <option value="Food and Beverage Supplier">Food and Beverage Supplier</option>
                                                            <option value="Packaging Supplier">Packaging Supplier</option>
                                                            <option value="Equipment Supplier">Equipment Supplier</option>
                                                            <option value="Cleaning Supplies">Cleaning Supplies</option>
                                                            <option value="Utility Providers">Utility Providers</option>
                                                            <option value="Service Providers">Service Providers</option>
                                                        </select>
                                                        @error('supplier_type')
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>

                                                        <div class="col-md-12">
                                                            <label>Address</label>
                                                            <textarea name="address" class="form-control"></textarea>
                                                        </div>

                                                        <div class="col-md-12 mt-3">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                                <!-- End Add Supplier Modal -->

                            </div>
                        </div>
                    </div>



                    <div class="vgt-responsive mt-3">
                        <table id="vgt-table" class="table-hover tableOne vgt-table">
                            <thead>
                                <tr>
                                    <th>Supplier ID</th>
                                    <th>Supplier Name</th>
                                    <th>Contact Person</th>
                                    <th>Mobile #</th>
                                    <th>Landline #</th>
                                    <th>Email</th>
                                    <th>Supplier Since</th>
                                    <th>TIN</th>
                                    <th>Supplier Type</th>
                                    <th>Address</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $supplier)
                                    <tr>
                                        <td>{{ $supplier->id }}</td>
                                        <td>{{ $supplier->supplier_name }}</td>
                                        <td>{{ $supplier->contact_person }}</td>
                                        <td>{{ $supplier->mobile_no }}</td>
                                        <td>{{ $supplier->landline_no }}</td>
                                        <td>{{ $supplier->email }}</td>
                                        <td>{{ $supplier->supplier_since }}</td>
                                        <td>{{ $supplier->tin }}</td>
                                        <td>{{ $supplier->supplier_type }}</td>
                                        <td>{{ $supplier->address }}</td>
                                        <td class="vgt-left-align text-right">
                                            <div class="dropdown b-dropdown btn-group">
                                                <!-- 3-dot button -->
                                                <button id="dropdownMenu{{ $supplier->id }}"
                                                    type="button"
                                                    class="btn dropdown-toggle btn-link btn-lg text-decoration-none dropdown-toggle-no-caret"
                                                    data-bs-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <span class="_dot _r_block-dot bg-dark"></span>
                                                    <span class="_dot _r_block-dot bg-dark"></span>
                                                    <span class="_dot _r_block-dot bg-dark"></span>
                                                </button>

                                                <!-- Dropdown items -->
                                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu{{ $supplier->id }}">
                                                    <!-- Edit -->
                                                    <li role="presentation">
                                                        <a class="dropdown-item" href="#"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editSupplierModal{{ $supplier->id }}">
                                                            <i class="nav-icon i-Edit font-weight-bold mr-2"></i> Edit
                                                        </a>
                                                    </li>

                                                     <!-- Archive -->
                                                    @if($supplier->status === 'active')
                                                    <form action="{{ route('suppliers.archive', $supplier) }}" method="POST"
                                                        onsubmit="return confirm('Are you sure you want to move this item to the archive?');"
                                                        style="display:inline;">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="nav-icon i-Letter-Close font-weight-bold mr-2"></i> Move to Archive
                                                        </button>
                                                    </form>
                                                    @endif

                                                    <!-- Restore -->
                                                    @if($supplier->status === 'archived')
                                                    <form action="{{ route('suppliers.restore', $supplier) }}" method="POST"
                                                        onsubmit="return confirm('Are you sure you want to restore this item to active?');"
                                                        style="display:inline;">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="nav-icon i-Eye font-weight-bold mr-2font-weight-bold mr-2"></i> Restore as Active
                                                        </button>
                                                    </form>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Supplier Modal -->
            <div class="modal fade" id="editSupplierModal{{ $supplier->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Supplier</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                    <div class="row">
                                    <input type="hidden" name="id" value="">
                                    <div class="col-md-6">
                                        <label>Supplier Name <span class="text-danger">*</span></label>
                                        <input type="text" name="supplier_name" class="form-control" required value="{{ $supplier->supplier_name }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Contact Person</label>
                                        <input type="text" name="contact_person" class="form-control" value="{{ $supplier->contact_person }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Mobile #</label>
                                        <input type="text" name="mobile_no" class="form-control" value="{{ $supplier->mobile_no }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Landline #</label>
                                        <input type="text" name="landline_no" class="form-control" value="{{ $supplier->landline_no }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ $supplier->email }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Supplier Since</label>
                                        <input type="date" name="supplier_since" class="form-control" value="{{ $supplier->supplier_since }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>TIN</label>
                                        <input type="text" name="tin" class="form-control" value="{{ $supplier->tin }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="type">Supplier Type <span class="text-danger">*</span></label>
                                        <select name="supplier_type" id="supplier_type" class="form-control" required>
                                            <option value="" disabled selected>Select Supplier Type</option>
                                            <option value="Food and Beverage Supplier"
                                                {{ old('supplier_type', $supplier->supplier_type ?? '') == 'Food and Beverage Supplier' ? 'selected' : '' }}>
                                                Food and Beverage Supplier
                                            </option>
                                            <option value="Packaging Supplier"
                                                {{ old('supplier_type', $supplier->supplier_type ?? '') == 'Packaging Supplier' ? 'selected' : '' }}>
                                                Packaging Supplier
                                            </option>
                                            <option value="Equipment Supplier"
                                                {{ old('supplier_type', $supplier->supplier_type ?? '') == 'Equipment Supplier' ? 'selected' : '' }}>
                                                Equipment Supplier
                                            </option>
                                            <option value="Cleaning Supplies"
                                                {{ old('supplier_type', $supplier->supplier_type ?? '') == 'Cleaning Supplies' ? 'selected' : '' }}>
                                                Cleaning Supplies
                                            </option>
                                            <option value="Utility Providers"
                                                {{ old('supplier_type', $supplier->supplier_type ?? '') == 'Utility Providers' ? 'selected' : '' }}>
                                                Utility Providers
                                            </option>
                                            <option value="Service Providers"
                                                {{ old('supplier_type', $supplier->supplier_type ?? '') == 'Service Providers' ? 'selected' : '' }}>
                                                Service Providers
                                            </option>
                                        </select>
                                        @error('supplier_type')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control">{{ $supplier->address }}</textarea>
                                    </div>

                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <tr>
                <td colspan="11" class="text-center">No suppliers found.</td>
            </tr>
            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="vgt-wrap__footer vgt-clearfix">
                        <div class="footer__row-count vgt-pull-left">
                            <form>
                                <label for="vgt-select-rpp-suppliers" class="footer__row-count__label">Rows per page:</label>
                                <select id="vgt-select-rpp-suppliers" class="footer__row-count__select">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="30">30</option>
                                    <option value="40">40</option>
                                    <option value="50">50</option>
                                    <option value="-1">All</option>
                                </select>
                            </form>
                        </div>

                        <div class="footer__navigation vgt-pull-right">
                            <div class="footer__navigation__page-info">
                                <div>
                                    {{ $suppliers->count() }} total
                                </div>
                            </div>
                            <button type="button" class="footer__navigation__page-btn disabled">
                                <span class="chevron left"></span><span>prev</span>
                            </button>
                            <button type="button" class="footer__navigation__page-btn disabled">
                                <span>next</span><span class="chevron right"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
