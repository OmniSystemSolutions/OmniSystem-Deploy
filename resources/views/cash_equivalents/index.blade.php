@extends('layouts.app')
@section('content')
<style>
   #accountNumber:disabled {
   background-color: #f5f5f5;
   cursor: not-allowed;
   }
</style>
<div class="main-content">
   <div>
      <div class="breadcrumb">
         <h1 class="mr-3">Cash Equivalents</h1>
         <ul>
            <li><a href=""> Accounting </a></li>
            <!----> <!---->
         </ul>
         <div class="breadcrumb-action"></div>
      </div>
      <div class="separator-breadcrumb border-top"></div>
   </div>
   <div class="wrapper">
    <div class="card wrapper">
        <div class="card-body">
            <nav class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a href="{{ route('cash_equivalents.index', ['status' => 'active']) }}" class="nav-link {{ $status === 'active' ? 'active' : '' }}">Active</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('cash_equivalents.index', ['status' => 'archived']) }}" class="nav-link {{ $status === 'archived' ? 'active' : '' }}">Archived</a>
                </li>
                </ul>
            </nav>
            <div class="vgt-wrap ">
                <div class="vgt-inner-wrap">
                <div class="vgt-global-search vgt-clearfix">
                    <div class="vgt-global-search__input vgt-pull-left">
                        <form role="search">
                            <label for="vgt-search-ce">
                            <span aria-hidden="true" class="input__icon">
                                <div class="magnifying-glass"></div>
                            </span>
                            <span class="sr-only">Search</span>
                            </label>
                            <input id="vgt-search-ce" type="text" placeholder="Search this table" class="vgt-input vgt-pull-left">
                        </form>
                    </div>
                    <div class="vgt-global-search__actions vgt-pull-right">
                        <div class="mt-2 mb-3">
                            <button id="dropdown-form__BV_toggle_" aria-haspopup="menu" aria-expanded="false" type="button" class="btn dropdown-toggle btn-light dropdown-toggle-no-caret"><i class="i-Gear"></i></button>
                            <button type="button" class="btn btn-outline-info ripple m-1 btn-sm collapsed" aria-expanded="false" aria-controls="sidebar-right" style="overflow-anchor: none;"><i class="i-Filter-2"></i>Filter</button>
                            <button type="button" class="btn btn-outline-success ripple m-1 btn-sm"><i class="i-File-Copy"></i> PDF
                            </button> <button class="btn btn-sm btn-outline-danger ripple m-1"><i class="i-File-Excel"></i> EXCEL
                            </button> <button type="button" class="btn btn-rounded btn-btn btn-primary btn-icon m-1" 
                            data-bs-toggle="modal" data-bs-target="#New_CashEquivalent">
                            <i class="i-Add"></i> Add
                            </button>
                            <!-- Add Cash Equivalent Modal -->
                            <div class="modal fade" id="New_CashEquivalent" tabindex="-1" role="dialog" aria-labelledby="New_CashEquivalentLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="New_CashEquivalentLabel">Add Cash Equivalent</h5>
                                        {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                        </button> --}}
                                    </div>
                                    <form action="{{ route('cash_equivalents.store') }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                        <div class="row">
                                            <div id="CreateModal___BV_modal_body_" class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                    <label for="created_at_ce">Date and Time Created</label>
                                                    <div class="d-flex">
                                                        <input
                                                            type="datetime-local"
                                                            id="created_at_ce"
                                                            name="created_at"
                                                            class="form-control"
                                                            value="{{ old('created_at') }}"
                                                            >
                                                    </div>
                                                    </div>
                                                    <!-- Radio Buttons -->
                                                    <div class="col-md-12 mt-3">
                                                    <div id="radio-group-1" role="radiogroup" tabindex="-1" class="bv-no-focus-ring">
                                                        <div class="custom-control custom-control-inline custom-radio">
                                                            <input id="radio-group-1_BV_option_0" type="radio" name="Mode" class="custom-control-input" value="1">
                                                            <label for="radio-group-1_BV_option_0" class="custom-control-label"><span>Regular Cash Equivalent</span></label>
                                                        </div>
                                                        <div class="custom-control custom-control-inline custom-radio">
                                                            <input id="radio-group-1_BV_option_1" type="radio" name="Mode" class="custom-control-input" value="2">
                                                            <label for="radio-group-1_BV_option_1" class="custom-control-label"><span>Cash On Hand</span></label>
                                                        </div>
                                                        <div class="custom-control custom-control-inline custom-radio">
                                                            <input id="radio-group-1_BV_option_2" type="radio" name="Mode" class="custom-control-input" value="3">
                                                            <label for="radio-group-1_BV_option_2" class="custom-control-label"><span>Petty Cash</span></label>
                                                        </div>
                                                        <div class="custom-control custom-control-inline custom-radio">
                                                            <input id="radio-group-1_BV_option_3" type="radio" name="Mode" class="custom-control-input" value="4">
                                                            <label for="radio-group-1_BV_option_3" class="custom-control-label"><span>Revolving Fund</span></label>
                                                        </div>
                                                    </div>
                                                    </div>
                                                    <div class="col-md-12 mt-3">
                                                    <label>Cash Equivalent Name *</label>
                                                    <input
                                                        type="text"
                                                        name="name"
                                                        id="cashEquivalentName"
                                                        class="form-control"
                                                        required
                                                        >
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                    <label>Account Number *</label>
                                                    <input
                                                        type="text"
                                                        name="account_number"
                                                        id="accountNumber"
                                                        class="form-control"
                                                        >
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                    <label>Accountable Person</label>
                                                    <select
                                                        name="accountable_id"
                                                        id="accountablePerson"
                                                        class="form-control"
                                                        required
                                                        >
                                                        <option value="" disabled selected>Select Accountable Person</option>
                                                        @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    </div>
                                                    <div class="mt-3 col-md-12 d-flex justify-content-start">
                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary mr-2"
                                                        data-bs-dismiss="modal"
                                                        >Close
                                                    </button>
                                                    <div class="b-overlay-wrap position-relative d-inline-block btn-loader">
                                                        <button type="submit" class="btn btn-primary">
                                                        <i class="i-Yes me-2 font-weight-bold"></i> Submit
                                                        </button>
                                                    </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="vgt-fixed-header">
                </div>
                <div class="vgt-responsive">
                    <table id="vgt-table" class="table-hover tableOne vgt-table ">
                        <thead>
                            <tr>
                            <th scope="col" class="vgt-checkbox-col"><input type="checkbox"></th>
                            <th scope="col" class="vgt-left-align text-left"><span>Date and Time Created</span></th>
                            <th scope="col" class="gvt-left-align text-left"><span>Created By</span></th>
                            <th scope="col" class="gvt-left-align text-left"><span>Cash Equivalent Name</span></th>
                            <th scope="col" class="vgt-left-align text-left"><span>Accountable Name</span></th>
                            <th scope="col" class="gvt-left-align text-left"><span>Account Number</span></th>
                            <th scope="col" class="gvt-left-align text-left"><span>Type of Account</span></th>
                            <th scope="col" class="vgt-left-align text-right"><span>Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashEquivalents as $ce)
                            <tr>
                            <td class="vgt-checkbox-col"><input type="checkbox"></td>
                            <td class="vgt-left-align text-left">{{ $ce->created_at->format('Y-m-d H:i') }}</td>
                            <td class="vgt-left-align text-left">{{ $ce->creator?->username ?? 'N/A' }}</td>
                            <td class="vgt-left-align text-left">{{ $ce->name }}</td>
                            <td class="vgt-left-align text-left">{{ $ce->accountable?->username ?? 'N/A' }}</td>
                            <td class="vgt-left-align text-left">{{ $ce->account_number ?? 'N/A' }}</td>
                            <td class="vgt-left-align text-left">{{ $ce->type_of_account ?? '' }}</td>
                            <td class="vgt-left-align text-right">
                                <div class="dropdown b-dropdown btn-group">
                                    <button id="dropdownMenuCE{{ $ce->id }}"
                                        type="button"
                                        class="btn dropdown-toggle btn-link btn-lg text-decoration-none dropdown-toggle-no-caret"
                                        data-bs-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false">
                                    <span class="_dot _r_block-dot bg-dark"></span>
                                    <span class="_dot _r_block-dot bg-dark"></span>
                                    <span class="_dot _r_block-dot bg-dark"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuCE{{ $ce->id }}">
                                        <li role="presentation">
                                        <a class="dropdown-item" href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCEModal{{ $ce->id }}">
                                        <i class="nav-icon i-Edit font-weight-bold mr-2"></i> Edit
                                        </a>
                                        </li>
                                        @if($ce->status === 'active')
                                        <form action="{{ route('cash_equivalents.archive', $ce) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to move this item to the archive?');"
                                        style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="dropdown-item">
                                        <i class="nav-icon i-Letter-Close font-weight-bold mr-2"></i> Move to Archive
                                        </button>
                                        </form>
                                        @endif
                                        @if($ce->status === 'archived')
                                        <form action="{{ route('cash_equivalents.restore', $ce) }}" method="POST"
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
                            <!-- Edit Modal for this Cash Equivalent -->
                            <div class="modal fade" id="editCEModal{{ $ce->id }}" tabindex="-1" aria-labelledby="editCEModalLabel{{ $ce->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('cash_equivalents.update', $ce->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                        <h5 class="modal-title" id="editCEModalLabel{{ $ce->id }}">Edit Cash Equivalent</h5>
                                        </div>
                                        <div class="modal-body">
                                        <!-- Created At -->
                                        <div class="form-group mb-3">
                                            <label for="ce-created-{{ $ce->id }}">Date &amp; Time Created</label>
                                            <input type="datetime-local"
                                                name="created_at"
                                                id="ce-created-{{ $ce->id }}"
                                                class="form-control"
                                                value="{{ $ce->created_at ? $ce->created_at->format('Y-m-d\TH:i') : '' }}">
                                            <small class="form-text text-muted">
                                            Leave blank to keep the original creation date.
                                            </small>
                                        </div>
                                        <!-- Radio Buttons -->
                                        <div class="form-group mb-3">
                                            <div class="custom-control custom-control-inline custom-radio">
                                                <input id="edit-radio-1-{{ $ce->id }}" type="radio" name="Mode" class="custom-control-input" value="1"
                                                {{ !in_array($ce->name, ['Cash On Hand', 'Petty Cash', 'Revolving Fund']) ? 'checked' : '' }}>
                                                <label for="edit-radio-1-{{ $ce->id }}" class="custom-control-label">Regular Cash Equivalent</label>
                                            </div>
                                            <div class="custom-control custom-control-inline custom-radio">
                                                <input id="edit-radio-2-{{ $ce->id }}" type="radio" name="Mode" class="custom-control-input" value="2"
                                                {{ $ce->name === 'Cash On Hand' ? 'checked' : '' }}>
                                                <label for="edit-radio-2-{{ $ce->id }}" class="custom-control-label">Cash On Hand</label>
                                            </div>
                                            <div class="custom-control custom-control-inline custom-radio">
                                                <input id="edit-radio-3-{{ $ce->id }}" type="radio" name="Mode" class="custom-control-input" value="3"
                                                {{ $ce->name === 'Petty Cash' ? 'checked' : '' }}>
                                                <label for="edit-radio-3-{{ $ce->id }}" class="custom-control-label">Petty Cash</label>
                                            </div>
                                            <div class="custom-control custom-control-inline custom-radio">
                                                <input id="edit-radio-4-{{ $ce->id }}" type="radio" name="Mode" class="custom-control-input" value="4"
                                                {{ $ce->name === 'Revolving Fund' ? 'checked' : '' }}>
                                                <label for="edit-radio-4-{{ $ce->id }}" class="custom-control-label">Revolving Fund</label>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="ce-name-{{ $ce->id }}">Cash Equivalent Name  *</label>
                                            <input type="text"
                                                name="name"
                                                id="ce-name-{{ $ce->id }}"
                                                class="form-control"
                                                value="{{ $ce->name }}" required>
                                        </div>
                                        <div class="form-group mb-3" id="accountNumberWrapper-{{ $ce->id }}">
                                            <label for="ce-account-{{ $ce->id }}">Account Number *</label>
                                            <input type="text"
                                                name="account_number"
                                                id="ce-account-{{ $ce->id }}"
                                                class="form-control"
                                                value="{{ $ce->account_number }}">
                                        </div>
                                        <div class="form-group mb-3" id="accountableWrapper-{{ $ce->id }}">
                                            <label>Accountable Person</label>
                                            <select
                                                name="accountable_id"
                                                class="form-control"
                                                required
                                                >
                                            <option value="" disabled {{ !$ce->accountable_id ? 'selected' : '' }}>
                                            Select Accountable Person
                                            </option>
                                            @foreach($users as $user)
                                            <option
                                            value="{{ $user->id }}"
                                            {{ $ce->accountable_id == $user->id ? 'selected' : '' }}
                                            >
                                            {{ $user->name }}
                                            </option>
                                            @endforeach
                                            </select>
                                        </div>
                                        </div>
                                        <div class="modal-footer" style="justify-content: flex-start">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            </div>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="vgt-wrap__footer vgt-clearfix">
                    <div class="footer__row-count vgt-pull-left">
                        <form>
                            <label for="vgt-select-rpp-ce" class="footer__row-count__label">Rows per page:</label> 
                            <select id="vgt-select-rpp-ce" autocomplete="off" name="perPageSelect" aria-controls="vgt-table" class="footer__row-count__select">
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
                        <div data-v-347cbcfa="" class="footer__navigation__page-info">
                            <div data-v-347cbcfa="">
                            1 - 4 of 4
                            </div>
                        </div>
                        <button type="button" aria-controls="vgt-table" class="footer__navigation__page-btn disabled"><span aria-hidden="true" class="chevron left"></span> <span>prev</span></button>
                        <button type="button" aria-controls="vgt-table" class="footer__navigation__page-btn disabled"><span>next</span> <span aria-hidden="true" class="chevron right"></span></button>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<!-- JavaScript to handle auto-fill CREATE -->
<script>
   document.addEventListener('DOMContentLoaded', function() {
   const radios = document.querySelectorAll('input[name="Mode"]');
   const nameField = document.getElementById('cashEquivalentName');
   
   radios.forEach(radio => {
       radio.addEventListener('change', function() {
       switch (this.value) {
           case '2': // Cash On Hand
           nameField.value = 'Cash On Hand';
           nameField.readOnly = true;
           break;
           case '3': // Petty Cash
           nameField.value = 'Petty Cash';
           nameField.readOnly = true;
           break;
           case '4': // Revolving Fund
           nameField.value = 'Revolving Fund';
           nameField.readOnly = true;
           break;
           default: // Regular Cash Equivalent
           nameField.value = '';
           nameField.readOnly = false;
       }
       });
   });
   });
</script>
<!-- JavaScript to handle auto-fill EDIT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="editCEModal"]').forEach(modal => {

        const radios = modal.querySelectorAll('input[name="Mode"]');
        const accountInput = modal.querySelector('input[name="account_number"]');
        const accountWrapper = modal.querySelector('[id^="accountNumberWrapper"]');
        const accountableWrapper = modal.querySelector('[id^="accountableWrapper"]');
        const accountableSelect = modal.querySelector('select[name="accountable_id"]');
        const nameInput = modal.querySelector('input[name="name"]');

        const specialNames = {
            2: 'Cash On Hand',
            3: 'Petty Cash',
            4: 'Revolving Fund'
        };

        function toggleFields() {
            const selected = modal.querySelector('input[name="Mode"]:checked');

            if (!selected) return;

            if (selected.value === '1') {
                accountWrapper.style.display = 'block';
                accountInput.required = true;

                accountableWrapper.style.display = 'none';
                accountableSelect.required = false;
                accountableSelect.value = '';
            } else {
                accountWrapper.style.display = 'none';
                accountInput.required = false;
                accountInput.value = '';

                accountableWrapper.style.display = 'block';
                accountableSelect.required = true;
            }
        }

        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                const val = parseInt(this.value);

                // ✅ update name
                if (specialNames[val]) {
                    nameInput.value = specialNames[val];
                    nameInput.readOnly = true;
                } else {
                    nameInput.value = '';
                    nameInput.readOnly = false;
                }

                toggleFields();
            });
        });

        // 🔥 INITIAL STATE (based on checked radio)
        toggleFields();
    });
});
</script>
<script>
   document.addEventListener('DOMContentLoaded', function () {
       const input = document.getElementById('created_at_ce');
   
       // only auto-fill if no old value exists
       if (!input.value) {
           const now = new Date();
           now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
   
           input.value = now.toISOString().slice(0, 16);
       }
   });
</script>
<script>
   document.addEventListener('DOMContentLoaded', function () {
       const accountInput = document.getElementById('accountNumber');
       const accountableField = document.getElementById('accountablePerson');
       const radios = document.querySelectorAll('input[name="Mode"]');
   
       function toggleAccountField() {
            const selected = document.querySelector('input[name="Mode"]:checked');

            if (selected && selected.value === '1') { // Regular Cash Equivalent
                // 🔥 SHOW account number field
                accountInput.closest('.col-md-12').style.display = 'block';
                accountInput.disabled = false;
                accountInput.required = true;

                // 🔥 REMOVE accountable person (hide + disable)
                accountableField.closest('.col-md-12').style.display = 'none';
                accountableField.required = false;
                accountableField.value = '';
            } else {
                accountInput.closest('.col-md-12').style.display = 'none';
                accountInput.disabled = true;
                accountInput.required = false;
                accountInput.value = '';

                // 🔥 SHOW accountable person for other 3
                accountableField.closest('.col-md-12').style.display = 'block';
                accountableField.required = true;
            }
        }
   
       radios.forEach(radio => {
           radio.addEventListener('change', toggleAccountField);
       });
   
       // initial state
       toggleAccountField();
   });
</script>