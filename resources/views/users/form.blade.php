@extends('layouts.app')

@section('content')
<div class="main-content">
    <div class="breadcrumb">
        <h1 class="mr-3">Create Employee</h1>
        <ul>
            <li><a href="{{ route('users.index') }}">People</a></li>
            <li>Employees</li>
        </ul>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    <div class="card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs card-header-tabs" id="userCreateTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab" aria-controls="basic" aria-selected="true">Basic Information</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="access-tab" data-toggle="tab" href="#access" role="tab" aria-controls="access" aria-selected="false">Access Credentials</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="work-tab" data-toggle="tab" href="#work" role="tab" aria-controls="work" aria-selected="false">Work Information</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="educ-tab" data-toggle="tab" href="#educ" role="tab" aria-controls="educ" aria-selected="false">Educational Background</a>
                </li>
            </ul>
        </div>

        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                {{-- show validation / session messages so basic-tab-only submissions surface errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="tab-content" id="userCreateTabContent">
                    <!-- Basic Information -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="biometric_number">Biometric Number</label>
                                            <input type="text" name="biometric_number" id="biometric_number" class="form-control" value="{{ old('biometric_number') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="id_number">ID Number</label>
                                            <input type="text" name="id_number" id="id_number" class="form-control" value="{{ old('id_number') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="middle_name">Middle Name</label>
                                            <input type="text" name="middle_name" id="middle_name" class="form-control" value="{{ old('middle_name') }}">
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="name">User Name</label>
                                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                                        </div>
                                    </div> --}}
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_of_birth">Date of Birth</label>
                                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tin">TIN #</label>
                                            <input type="text" name="tin" id="tin" class="form-control" value="{{ old('tin') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="gender_id">Gender</label>
                                            <select name="gender_id" id="gender_id" class="form-control">
                                                <option value="">-- Select Gender --</option>
                                                <option value="Male" {{ old('gender_id') == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('gender_id') == 'Female' ? 'selected' : '' }}>Female</option>
                                                <option value="Other" {{ old('gender_id') == 'Other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="blood_type_id">Blood Type</label>
                                            <select name="blood_type_id" id="blood_type_id" class="form-control">
                                                <option value="">-- Select Blood Type --</option>
                                                <option value="A+" {{ old('blood_type_id') == 'A+' ? 'selected' : '' }}>A+</option>
                                                <option value="A-" {{ old('blood_type_id') == 'A-' ? 'selected' : '' }}>A-</option>
                                                <option value="B+" {{ old('blood_type_id') == 'B+' ? 'selected' : '' }}>B+</option>
                                                <option value="B-" {{ old('blood_type_id') == 'B-' ? 'selected' : '' }}>B-</option>
                                                <option value="AB+" {{ old('blood_type_id') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                                <option value="AB-" {{ old('blood_type_id') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                                <option value="O+" {{ old('blood_type_id') == 'O+' ? 'selected' : '' }}>O+</option>
                                                <option value="O-" {{ old('blood_type_id') == 'O-' ? 'selected' : '' }}>O-</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="mobile_number">Mobile #</label>
                                            <input type="text" name="mobile_number" id="mobile_number" class="form-control" value="{{ old('mobile_number') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pag_ibig_number">PhilHealth #</label>
                                            <input type="text" name="phil_health_number" id="phil_health_number" class="form-control" value="{{ old('phil_health_number') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="pag_ibig_number">PAG-IBIG #</label>
                                                    <input type="text" name="pag_ibig_number" id="pag_ibig_number" class="form-control" value="{{ old('pag_ibig_number') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="fw-bold">Primary Branch</label>
                                                    {{-- <select name="branches[]" class="form-control">
                                                        <option value="">Select branch</option>
                                                        @foreach($branches as $b)
                                                            <option value="{{ $b->id }}" {{ (old('branches.0') == $b->id) ? 'selected' : '' }}>{{ $b->name }}</option>
                                                        @endforeach
                                                    </select> --}}
                                                    <select name="branch_id" class="form-control">
                                                        <option value="">Select Branch</option>
                                                        @foreach($branches as $b)
                                                            <option value="{{ $b->id }}"
                                                                    {{ old('branch_id', $user?->branch_id ?? '') == $b->id ? 'selected' : '' }}>
                                                                {{ $b->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="civil_status_id">Civil Status</label>
                                            <select name="civil_status_id" id="civil_status_id" class="form-control">
                                                <option value="">-- Select Civil Status --</option>
                                                <option value="Single" {{ old('civil_status_id') == 'Single' ? 'selected' : '' }}>Single</option>
                                                <option value="Married" {{ old('civil_status_id') == 'Married' ? 'selected' : '' }}>Married</option>
                                                <option value="Widowed" {{ old('civil_status_id') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                                <option value="Separated" {{ old('civil_status_id') == 'Separated' ? 'selected' : '' }}>Separated</option>
                                                <option value="Divorced" {{ old('civil_status_id') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="address">Address</label>
                                            <textarea name="address" id="address" class="form-control">{{ old('address') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Spouse details (inline compact form) -->
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h6 class="mb-3">Spouse Details</h6>
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <input type="text" name="spouse[name]" class="form-control" placeholder="Full Name" value="{{ old('spouse.name') }}">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <input type="date" name="spouse[date_of_birth]" class="form-control" value="{{ old('spouse.date_of_birth') }}">
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <input type="number" name="spouse[age]" class="form-control" placeholder="Age" value="{{ old('spouse.age') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dependents table -->
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h6 class="mb-3">Dependents (Optional)</h6>
                                        <div id="dependents-list">
                                            <div class="dependent-row row mb-2">
                                                <div class="col-md-3"><input type="text" name="dependents[0][name]" class="form-control" placeholder="Name"></div>
                                                <div class="col-md-2"><input type="date" name="dependents[0][birthdate]" class="form-control" placeholder="Birthdate"></div>
                                                <div class="col-md-1"><input type="number" name="dependents[0][age]" class="form-control" placeholder="Age"></div>
                                                <div class="col-md-3">
                                                    <select name="dependents[0][gender]" class="form-control">
                                                        <option value="">-- Select Gender --</option>
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <select name="dependents[0][relationship]" class="form-control">
                                                        <option value="">-- Relationship --</option>
                                                        <option value="Son">Son</option>
                                                        <option value="Daughter">Daughter</option>
                                                        <option value="Parent">Parent</option>
                                                        <option value="Spouse">Spouse</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-dependent">-</button></div>
                                            </div>
                                        </div>
                                        <button type="button" id="add-dependent" class="btn btn-sm btn-outline-primary">Add dependent</button>
                                    </div>
                                </div>

                                <!-- Emergency contact -->
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h6 class="mb-3">Contact Information In Case of Emergency</h6>
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <input type="text" name="contact_person[name]" class="form-control" placeholder="Full Name" value="{{ old('contact_person.name') }}">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <input type="text" name="contact_person[contact_number]" class="form-control" placeholder="Contact Number" value="{{ old('contact_person.contact_number') }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="contact_person[address]" class="form-control" placeholder="Address" value="{{ old('contact_person.address') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group text-center">
                                    <label>Photo</label>

                                    <label
                                        for="image"
                                        id="drop-area"
                                        class="upload-box text-center p-3 border rounded d-block"
                                        style="cursor:pointer;"
                                    >
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-muted"></i>
                                        <p class="text-muted">
                                            Drag & Drop an image<br>
                                            <strong>or click to select</strong>
                                        </p>

                                        <input
                                            type="file"
                                            id="image"
                                            name="image"
                                            class="d-none"
                                            accept="image/*"
                                        >

                                        <div id="preview-container" class="mt-3"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Access Credentials -->
                    <div class="tab-pane fade" id="access" role="tabpanel" aria-labelledby="access-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-check">
                                    <input type="checkbox" name="allow_db_user" id="allow_db_user" class="form-check-input" value="1" {{ old('allow_db_user') ? 'checked' : '' }}>
                                    <label for="allow_db_user" class="form-check-label">Allow employee to be Database User</label>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="username">Username</label>
                                        <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="password">Password</label>
                                        <input type="password" name="password" id="password" class="form-control">
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <h6>Branch Roles</h6>
                                <p class="text-muted">Assign one or more roles per branch (select role names).</p>

                                <div id="branch-permissions-list">
                                    <div class="branch-permission-row form-row align-items-center mb-2">
                                        <div class="col-md-5">
                                            <select name="branch_permissions[0][branch_id]" class="form-control">
                                                <option value="">Select branch</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <select
                                                name="branch_permissions[0][permissions][]"
                                                class="form-control"
                                                multiple
                                            >
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-branch">-</button>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="add-branch-permission" class="btn btn-sm btn-outline-primary">
                                    Add branch
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Work Information -->
                    <div class="tab-pane fade" id="work" role="tabpanel" aria-labelledby="work-tab">
                        <div class="card">
                            <div class="card-body">
                                <h6>Work Informations</h6>

                                <!-- Table of work information entries -->
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered" id="workinfo-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Employment Type</th>
                                                <th>Position</th>
                                                <th>Department</th>
                                                <th>Supervisor</th>
                                                <th>Monthly Rate</th>
                                                <th>Daily Rate</th>
                                                <th>Hourly Rate</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- JS will append rows from form inputs -->
                                        </tbody>
                                    </table>
                                </div>

                                <button type="button" id="open-workinfo-form" class="btn btn-sm btn-outline-primary mb-3">Add Work Info</button>

                                <div id="workinfo-form" style="display:none;" class="mb-3">
                                    <div class="row">
                                        {{-- <div class="col-md-3 form-group"><label>Hire Date</label><input type="date" id="wi_hire_date" class="form-control"></div>
                                        <div class="col-md-2 form-group"><label>Status</label><input type="text" id="wi_status" class="form-control" placeholder="Status"></div> --}}

                                        <div class="col-md-3 form-group"><label>Date</label><input type="date" id="wi_hire_date" class="form-control"></div>
                                        <div class="col-md-2 form-group">
                                            <label>Employment Type</label>
                                            <select id="wi_status" class="form-control">
                                                <option value="">Select Employment Type</option>
                                                <option value="probationary">Probationary Period</option>
                                                <option value="regularization">Regularization</option>
                                                <option value="promotion">Promotion</option>
                                                <option value="contractual">Contractual</option>
                                                <option value="resigned">Resigned</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-2 form-group"><label>Position</label>
                                            <select id="wi_designation" class="form-control">
                                                <option value="">Select Position</option>
                                                @foreach($designations as $des)
                                                    <option value="{{ $des->id }}">{{ $des->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 form-group"><label>Department</label>
                                            <select id="wi_department" class="form-control">
                                                <option value="">Select Department</option>
                                                @foreach($departments as $d)
                                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        {{-- <div class="col-md-3 form-group"><label>Supervisor (user id)</label><input type="number" id="wi_supervisor" class="form-control" placeholder="Supervisor id"></div> --}}
                                        <div class="col-md-3 form-group">
                                            <label>Supervisor</label>
                                            <select id="wi_supervisor" name="wi_supervisor" class="form-control">
                                                <option value="">No Supervisor / Not Applicable</option>

                                                @foreach($potentialSupervisors as $supervisor)
                                                    @php
                                                        $latestDesignation = $supervisor->employeeWorkInformations->first()?->designation?->name ?? '';
                                                    @endphp

                                                    <option value="{{ $supervisor->username }}"
                                                            data-designation="{{ strtolower($latestDesignation) }}"
                                                            {{ old('wi_supervisor') === $supervisor->username ? 'selected' : '' }}>
                                                        {{ $supervisor->name ?? $supervisor->username }}
                                                        <small class="text-muted">
                                                            ({{ $supervisor->username }} • {{ $latestDesignation ?: '—' }})
                                                        </small>
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group"><label>Monthly Rate</label><input type="number" step="0.01" id="wi_monthly_rate" class="form-control"></div>
                                        <div class="col-md-3 form-group"><label>Daily Rate</label><input type="number" step="0.01" id="wi_daily_rate" class="form-control"></div>
                                        <div class="col-md-3 form-group"><label>Hourly Rate</label><input type="number" step="0.01" id="wi_hourly_rate" class="form-control"></div>
                                    </div>
                                    <div>
                                        <button type="button" id="save-workinfo" class="btn btn-primary btn-sm">Save</button>
                                        <button type="button" id="cancel-workinfo" class="btn btn-secondary btn-sm">Cancel</button>
                                    </div>
                                </div>

                                <hr>

                                <h6>Salary Method</h6>
                                <div class="row mb-3">
                                    <div class="col-md-2 form-group">
                                        <label>Salary Method</label>
                                        <select name="salary_method[method_id]" class="form-control">
                                            <option value="">Select Method</option>
                                            @foreach($salaryMethods as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label>Salary Period</label>
                                        <select name="salary_method[period_id]" class="form-control">
                                            <option value="bi-monthly">Bi-Monthly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="daily">Daily</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label>Account Name / Number</label>
                                        <input type="text" name="salary_method[account]" class="form-control">
                                    </div>

                                    <!-- Shift Template Selector (remains outside the modal) -->
                                    <div class="col-md-2 form-group">
                                        <label class="fw-bold">Shift Template (Optional)</label>
                                        <select id="shift_select" class="form-control mb-2">
                                            <option value="">No template / Custom only</option>
                                            @foreach($shifts as $shift)
                                                <option value="{{ $shift->id }}"
                                                        data-shift='@json($shift)'
                                                        {{ old('salary_method.shift_id', isset($user) && $user->salaryMethod ? $user->salaryMethod->shift_id : null) == $shift->id ? 'selected' : '' }}>
                                                    {{ $shift->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <!-- Hidden field to submit the selected template -->
                                        <input type="hidden" name="salary_method[shift_id]" id="assigned_shift_id"
                                            value="{{ old('salary_method.shift_id', isset($user) && $user->salaryMethod ? $user->salaryMethod->shift_id : null) }}">

                                        <!-- Small helper text -->
                                        <small class="text-muted">Select a template to customize times and view schedule.</small>
                                    </div>
                                </div>

                                <!-- Custom Shift Modal -->
                                <div class="modal fade" id="shiftModal" tabindex="-1" role="dialog" aria-labelledby="shiftModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold" id="shiftModalLabel">
                                                    <span id="modal-shift-name">Custom Shift Settings</span>
                                                </h5>
                                                <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <!-- NOTE: visible Custom Shift Schedule inputs removed. We store aggregated
                                                     values into hidden inputs below when the modal is saved. -->
                                                <input type="hidden" name="salary_method[custom_time_start]" id="custom_time_start_input" value="{{ old('salary_method.custom_time_start', isset($user) && $user->salaryMethod ? $user->salaryMethod->custom_time_start : '') }}">
                                                <input type="hidden" name="salary_method[custom_time_end]" id="custom_time_end_input" value="{{ old('salary_method.custom_time_end', isset($user) && $user->salaryMethod ? $user->salaryMethod->custom_time_end : '') }}">
                                                <input type="hidden" name="salary_method[custom_break_start]" id="custom_break_start_input" value="{{ old('salary_method.custom_break_start', isset($user) && $user->salaryMethod ? $user->salaryMethod->custom_break_start : '') }}">
                                                <input type="hidden" name="salary_method[custom_break_end]" id="custom_break_end_input" value="{{ old('salary_method.custom_break_end', isset($user) && $user->salaryMethod ? $user->salaryMethod->custom_break_end : '') }}">

                                                <!-- Hidden arrays for per-date selections (populated by JS on Save) -->
                                                <input type="hidden" name="salary_method[custom_work_days]" id="custom_work_days_input" value="{{ old('salary_method.custom_work_days', isset($user) && $user->salaryMethod ? json_encode($user->salaryMethod->custom_work_days ?? []) : '[]') }}">
                                                <input type="hidden" name="salary_method[custom_rest_days]" id="custom_rest_days_input" value="{{ old('salary_method.custom_rest_days', isset($user) && $user->salaryMethod ? json_encode($user->salaryMethod->custom_rest_days ?? []) : '[]') }}">
                                                <input type="hidden" name="salary_method[custom_open_time]" id="custom_open_time_input" value="{{ old('salary_method.custom_open_time', isset($user) && $user->salaryMethod ? json_encode($user->salaryMethod->custom_open_time ?? []) : '[]') }}">

                                                <!-- Which shift id the saved custom blob belongs to (client-side helper + submitted) -->
                                                <input type="hidden" name="salary_method[custom_for_shift_id]" id="custom_for_shift_input" value="{{ old('salary_method.custom_for_shift_id', isset($user) && $user->salaryMethod ? $user->salaryMethod->custom_for_shift_id : '') }}">

                                                <!-- Preview Card -->
                                                <div class="card mt-4">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold mb-4">Current Shift Preview</h6>

                                                        <!-- Time Preview -->
                                                        <div class="row mb-4">
                                                            <div class="col-md-3">
                                                                <strong>Start:</strong> <span id="pv-start" class="text-primary fw-bold">-</span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <strong>End:</strong> <span id="pv-end" class="text-primary fw-bold">-</span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <strong>Break Start:</strong> <span id="pv-break-start" class="text-primary fw-bold">-</span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <strong>Break End:</strong> <span id="pv-break-end" class="text-primary fw-bold">-</span>
                                                            </div>
                                                        </div>

                                                        <!-- Preset date range for template schedule -->
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <label>Preset Schedule Start</label>
                                                                <input type="date" id="preset_start" name="salary_method[preset_start]" class="form-control" value="{{ old('salary_method.preset_start') }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Preset Schedule End</label>
                                                                <input type="date" id="preset_end" name="salary_method[preset_end]" class="form-control" value="{{ old('salary_method.preset_end') }}">
                                                            </div>
                                                        </div>

                                                        <!-- Generated per-date schedule preview -->
                                                        <div id="preset-dates-list" class="mb-3">
                                                            <!-- JS will render date cards here -->
                                                        </div>

                                                        <!-- NOTE: Weekly schedule removed. Per-date options (Work Day / Rest Day / Open Time)
                                                             are rendered under each preset date card below the time inputs. -->

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="button" id="apply-preset-btn" class="btn btn-primary mr-2">Apply</button>
                                                <button type="button" id="save-shift-modal-btn" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mt-3">Allowances</h6>

                                <div class="row mb-2 fw-bold small text-muted">
                                    <div class="col-md-5 ps-3 mt-3">
                                    </div>
                                    <h6>Amount</h6>
                                    <div class="col-md-3 col-lg-2"></div>
                                </div>
                                <div id="allowances-list">
                                    @foreach($allowances as $i => $al)
                                    <div class="form-row align-items-center mb-2 allowance-row">
                                        <!-- Checkbox + Allowance name -->
                                        <div class="col-md-5">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input allowance-checkbox"
                                                    type="checkbox"
                                                    name="allowances[{{ $i }}][allowance_id]"
                                                    value="{{ $al->id }}"
                                                    id="allowance_{{ $al->id }}"
                                                >
                                                <label class="form-check-label" for="allowance_{{ $al->id }}">
                                                    {{ $al->name }}
                                                </label>
                                            </div>
                                        </div>
                                        @push('styles')
                                        <style>
                                        .permission-select {
                                            height: 120px;
                                        }
                                        </style>
                                        @endpush

                                        <!-- Amount with + / - buttons (orange background) -->
                                        <div class="col-md-1">
                                            <div class="input-group">
                                                <input
                                                    type="number"
                                                    name="allowances[{{ $i }}][amount]"
                                                    class="form-control allowance-amount text-center"
                                                    placeholder="Enter Amount Here"
                                                    step="100"
                                                    min="0"
                                                    value="{{ old("allowances.$i.amount") }}"
                                                    disabled
                                                >
                                            </div>
                                        </div>

                                        {{-- <div class="col-md-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-allowance">
                                                Remove
                                            </button>
                                        </div> --}}
                                    </div>
                                    @endforeach
                                </div>

                                <h6 class="mt-5 mb-3">Leaves</h6>

                                <!-- Header -->
                                <div class="row fw-bold small text-secondary mb-2">
                                    <div class="col-md-5 ps-1">Leave Type</div>
                                    <div class="col-md-2 text-center">Leave Credits</div>
                                    <div class="col-md-2 text-center">Used Leaves</div>
                                    <div class="col-md-2 text-center">Leave Balance</div>
                                    <div class="col-md-1 text-center">Action</div>
                                </div>

                                <div id="leaves-list">
                                    @foreach($leaves as $i => $lv)
                                    <div class="leave-row row align-items-center py-2 border-bottom" data-leave-id="{{ $lv->id }}">

                                        <!-- Leave Type -->
                                        <div class="col-md-5">
                                            <div class="form-check">
                                                <input class="form-check-input leave-checkbox" type="checkbox"
                                                        name="leaves[{{ $i }}][leave_id]" value="{{ $lv->id }}" id="leave_{{ $lv->id }}"
                                                        {{ old("leaves.$i.assigned_days") ? 'checked' : '' }}>
                                                <label class="form-check-label" for="leave_{{ $lv->id }}">{{ $lv->name }}</label>
                                            </div>
                                        </div>

                                        <!-- Credits -->
                                        {{-- <div class="col-md-2 text-center text-muted">
                                            <span class="leave-credits">0</span>
                                            <input type="hidden" name="leaves[{{ $i }}][assigned_days]"
                                                class="leave-days leave-credits-input"
                                                value="{{ old("leaves.$i.assigned_days", 0) }}">
                                        </div> --}}

                                        <div class="col-md-2 text-center text-muted">
                                            <span class="leave-credits">0</span>
                                            {{-- No hidden input here at all — JS adds it dynamically only when assigned --}}
                                        </div>

                                        <!-- Used -->
                                        <div class="col-md-2 text-center text-muted">
                                            <span class="leave-used">0</span>
                                        </div>

                                        <!-- Balance -->
                                        <div class="col-md-2 text-center fw-medium">
                                            <span class="leave-balance">0</span>
                                        </div>

                                        <!-- Action -->
                                        <div class="col-md-1 text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-secondary p-0" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v fa-sm"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <a class="dropdown-item assign-leave-inline" href="javascript:void(0)"
                                                           data-leave-id="{{ $lv->id }}"
                                                           data-leave-name="{{ $lv->name }}">
                                                            Assign Leave
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)"
                                                           onclick="openLeaveHistory('{{ $lv->id }}', '{{ addslashes($lv->name) }}')">
                                                            Leave History
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Inline assign form (hidden by default) -->
                                    <div id="assign-form-{{ $lv->id }}"
                                         class="assign-leave-form row bg-white border border-secondary-subtle p-3 rounded-3 shadow-sm d-none"
                                         data-leave-id="{{ $lv->id }}">

                                        <div class="col-12 mb-3">
                                            <h6 class="mb-0 fw-semibold text-dark">
                                                Assign Leave to: {{ $lv->name }}
                                            </h6>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label small fw-medium text-muted mb-1 d-block">
                                                Number of days
                                            </label>
                                            <input type="number"
                                                   class="form-control form-control-sm"
                                                   id="assign-days-{{ $lv->id }}"
                                                   min="1"
                                                   value="1"
                                                   required>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label small fw-medium text-muted mb-1 d-block">
                                                Effective Date (optional)
                                            </label>
                                            <input type="date"
                                                   class="form-control form-control-sm"
                                                   id="assign-date-{{ $lv->id }}">
                                        </div>

                                        <div class="col-md-4 mb-3 d-flex align-items-end gap-2">
                                            <button type="button"
                                                    class="btn btn-sm btn-primary px-4 save-assign-leave"
                                                    data-leave-id="{{ $lv->id }}">
                                                Save
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary px-4 cancel-assign-leave"
                                                    data-leave-id="{{ $lv->id }}">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- ===================== LEAVE HISTORY MODAL ===================== -->
                                <div class="modal fade" id="leaveHistoryModal" tabindex="-1" role="dialog" aria-labelledby="leaveHistoryModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content border-0 shadow">

                                            <div class="modal-header" style="background:#f8f9fa; border-bottom:2px solid #dee2e6;">
                                                <h5 class="modal-title fw-bold text-dark" id="leaveHistoryModalLabel">
                                                    <i class="fas fa-history me-2 text-primary"></i>
                                                    Leave History &mdash; <span id="lh-leave-name" class="text-primary"></span>
                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                                        data-dismiss="modal">&times;</button>
                                            </div>

                                            <div class="modal-body px-4 py-3">

                                                <!-- Loading spinner -->
                                                <div id="lh-loading" class="text-center py-4">
                                                    <div class="spinner-border text-primary" role="status"></div>
                                                    <p class="text-muted mt-2">Loading leave history...</p>
                                                </div>

                                                <!-- Content (shown after load) -->
                                                <div id="lh-content" style="display:none;">

                                                    <!-- ── Leave Credit ── -->
                                                    <h6 class="fw-bold text-uppercase text-dark mb-2" style="letter-spacing:.5px;">
                                                        Leave Credit
                                                    </h6>
                                                    <div class="table-responsive mb-1">
                                                        <table class="table table-sm table-bordered mb-0" id="lh-credit-table">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Date Assigned</th>
                                                                    <th class="text-center">Assigned Leaves</th>
                                                                    <th>Reference #</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="lh-credit-body">
                                                                <!-- JS fills this -->
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="fw-bold">
                                                                    <td>Total</td>
                                                                    <td class="text-center" id="lh-credit-total">0</td>
                                                                    <td></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>

                                                    <!-- ── Leave Usage ── -->
                                                    <h6 class="fw-bold text-uppercase text-dark mb-2 mt-4" style="letter-spacing:.5px;">
                                                        Leave Usage
                                                    </h6>
                                                    <div class="table-responsive mb-1">
                                                        <table class="table table-sm table-bordered mb-0" id="lh-usage-table">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Date of Leave</th>
                                                                    <th class="text-center">No of Days</th>
                                                                    <th>Reference #</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="lh-usage-body">
                                                                <!-- JS fills this -->
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="fw-bold">
                                                                    <td>Total</td>
                                                                    <td class="text-center" id="lh-usage-total">0</td>
                                                                    <td></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>

                                                    <!-- ── Leave Balance ── -->
                                                    <div class="mt-3 ps-1">
                                                        <span class="fw-bold text-danger fs-6">Leave Balance &nbsp;&nbsp;
                                                            <span id="lh-balance" class="fw-bold">0</span>
                                                        </span>
                                                    </div>

                                                </div><!-- /#lh-content -->

                                                <!-- Empty state -->
                                                <div id="lh-empty" style="display:none;" class="text-center text-muted py-4">
                                                    <i class="fas fa-folder-open fa-2x mb-2"></i>
                                                    <p>No leave history found for this employee.</p>
                                                </div>

                                            </div><!-- /.modal-body -->

                                            <div class="modal-footer" style="background:#f8f9fa;">
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                        data-bs-dismiss="modal" data-dismiss="modal">Close</button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- =================== END LEAVE HISTORY MODAL =================== -->


                            </div>
                        </div>
                    </div>

                    <!-- Educational Background -->
                    <div class="tab-pane fade" id="educ" role="tabpanel" aria-labelledby="educ-tab">
                        <div class="card">
                            <div class="card-body">
                                <h6>Educational Background</h6>

                                <!-- Table-like header row -->
                                <div class="row fw-bold mb-2">
                                    <div class="col-md-5">Name of School*</div>
                                    <div class="col-md-2">Level*</div>
                                    <div class="col-md-2">From</div>
                                    <div class="col-md-2">To</div>
                                    <div class="col-md-1"></div>
                                </div>

                                <div id="educ-bg-list">
                                    <div class="educ-row row mb-2">
                                        <div class="col-md-5"><input type="text" name="educational_backgrounds[0][name_of_school]" class="form-control" placeholder="Name of school"></div>
                                        <div class="col-md-2">
                                            <select name="educational_backgrounds[0][level]" class="form-control">
                                                <option value="">Select Level</option>
                                                <option value="Elementary">Elementary</option>
                                                <option value="High School">High School</option>
                                                <option value="Vocational">Vocational</option>
                                                <option value="College">College</option>
                                                <option value="Graduate">Graduate</option>
                                                <option value="Post Graduate">Post Graduate</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2"><input type="date" name="educational_backgrounds[0][tenure_start]" class="form-control" placeholder="From"></div>
                                        <div class="col-md-2"><input type="date" name="educational_backgrounds[0][tenure_end]" class="form-control" placeholder="To"></div>
                                        <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-educ">-</button></div>
                                    </div>
                                </div>
                                <button type="button" id="add-educ" class="btn btn-sm btn-outline-primary mb-4">Add education</button>

                                <!-- Attachments - ONLY IN EDUCATIONAL BACKGROUND TAB -->
                                <div class="card mt-4 border-orange">
                                    <div class="card-body">
                                        <h6 class="mb-4 text-orange">Attachments</h6>
                                        <div id="attachments-list">
                                            @php
                                                $commonAttachments = [
                                                    'Birth Certificate',
                                                    'Valid ID',
                                                    'Marriage Contract',
                                                    'Health Card',
                                                    'NBI',
                                                    'Resume',
                                                    'Location Sketch',
                                                    '2x2',
                                                    'Police Clearance',
                                                    'police clearance',
                                                    'NBI',
                                                    'GSIS',
                                                    'HMO',
                                                ];
                                            @endphp

                                            @foreach($commonAttachments as $index => $name)
                                            <div class="attachment-row row align-items-center mb-3">
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input attachment-checkbox"
                                                            type="checkbox"
                                                            id="attach_{{ $index }}"
                                                            value="{{ $name }}"
                                                        >
                                                        <label class="form-check-label" for="attach_{{ $index }}">
                                                            {{ $name }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input
                                                                type="file"
                                                                class="custom-file-input attachment-file"
                                                                name="attachments[{{ $index }}]"
                                                                id="file_{{ $index }}"
                                                                accept=".pdf,.jpg,.jpeg,.png"
                                                                disabled
                                                            >
                                                            <label class="custom-file-label text-truncate" for="file_{{ $index }}">
                                                                Choose file...
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- Hidden input to store the name -->
                                                    <input type="hidden" name="attachment_names[{{ $index }}]" class="attachment-name" value="{{ $name }}" disabled>
                                                </div>
                                                <div class="col-md-2 text-right">
                                                    <button type="button" class="btn btn-sm btn-danger remove-attachment" disabled>
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <!-- End of Attachments -->
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <div>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Image previews
document.getElementById('image')?.addEventListener('change', function(e){
    const file = e.target.files && e.target.files[0];
    if(!file) return;
    if(!file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = function(ev){
        let img = document.getElementById('create-user-image-preview');
        if(!img){
            img = document.createElement('img');
            img.id = 'create-user-image-preview';
            img.className = 'img-thumbnail mt-2';
            img.style.maxWidth = '190px';
            document.getElementById('image').parentNode.appendChild(img);
        }
        img.src = ev.target.result;
    }
    reader.readAsDataURL(file);
});
document.getElementById('avatar')?.addEventListener('change', function(e){
    const file = e.target.files && e.target.files[0];
    if(!file) return;
    if(!file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = function(ev){
        let img = document.getElementById('create-user-avatar-preview');
        if(!img){
            img = document.createElement('img');
            img.id = 'create-user-avatar-preview';
            img.className = 'img-thumbnail mt-2';
            img.style.maxWidth = '190px';
            document.getElementById('avatar').parentNode.appendChild(img);
        }
        img.src = ev.target.result;
    }
    reader.readAsDataURL(file);
});

// Repeatable educational backgrounds
(() => {
    let educIndex = 1;
    document.getElementById('add-educ')?.addEventListener('click', function(){
        const container = document.getElementById('educ-bg-list');
        const row = document.createElement('div');
        row.className = 'educ-row row mb-2';
        row.innerHTML = `\
            <div class="col-md-5"><input type="text" name="educational_backgrounds[${educIndex}][name_of_school]" class="form-control" placeholder="Name of school"></div>\
            <div class="col-md-2"><input type="number" name="educational_backgrounds[${educIndex}][level]" class="form-control" placeholder="Level id"></div>\
            <div class="col-md-2"><input type="date" name="educational_backgrounds[${educIndex}][tenure_start]" class="form-control"></div>\
            <div class="col-md-2"><input type="date" name="educational_backgrounds[${educIndex}][tenure_end]" class="form-control"></div>\
            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-educ">-</button></div>`;
        container.appendChild(row);
        educIndex++;
    });
    document.getElementById('educ-bg-list')?.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-educ')){
            e.target.closest('.educ-row').remove();
        }
    });
})();

// Allowances add/remove and conditional inputs
(() => {
    let aIndex = 1;
    const addAllowanceBtn = document.getElementById('add-allowance');
    const allowancesContainer = document.getElementById('allowances-list');

    // show/hide amount inputs when select changes
    allowancesContainer?.addEventListener('change', function(e){
        if(e.target && e.target.classList.contains('allowance-select')){
            const sel = e.target;
            const row = sel.closest('.allowance-row');
            if(!row) return;
            const amt = row.querySelector('.allowance-amount');
            const cnt = row.querySelector('.allowance-count');
            if(sel.value){
                if(amt){ amt.style.display=''; amt.disabled = false; }
                if(cnt){ cnt.style.display=''; cnt.disabled = false; }
            } else {
                if(amt){ amt.style.display='none'; amt.disabled = true; amt.value = ''; }
                if(cnt){ cnt.style.display='none'; cnt.disabled = true; cnt.value = ''; }
            }
        }
    });

    addAllowanceBtn?.addEventListener('click', function(){
        const row = document.createElement('div');
        row.className = 'allowance-row d-flex mb-2 align-items-center';
        let options = `\n                                            <option value="">-- Select allowance --</option>`;
        @foreach($allowances as $al)
            options += `\n                                                <option value="{{ $al->id }}">{{ addslashes($al->name) }}</option>`;
        @endforeach
        row.innerHTML = `\n            <select name="allowances[${aIndex}][allowance_id]" class="form-control mr-2 allowance-select" style="width:40%">${options}</select>\n            <input type="number" name="allowances[${aIndex}][amount]" class="form-control mr-2 allowance-amount" placeholder="Amount" style="display:none;" disabled>\n            <input type="number" name="allowances[${aIndex}][monthly_count]" class="form-control mr-2 allowance-count" placeholder="Monthly count" style="display:none;" disabled>\n            <button type="button" class="btn btn-sm btn-outline-danger remove-allowance">Remove</button>`;
        allowancesContainer.appendChild(row);
        aIndex++;
    });

    allowancesContainer?.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-allowance')){
            e.target.closest('.allowance-row').remove();
        }
    });

    // initialize existing rows visibility
    document.querySelectorAll('#allowances-list .allowance-row').forEach(function(row){
        const sel = row.querySelector('.allowance-select');
        if(sel){
            sel.dispatchEvent(new Event('change'));
        }
    });
})();



document.querySelector('form').addEventListener('submit', function () {

    try { aggregateShiftModalAndPopulateHidden(); } catch (e) { /* ignore */ }

    // Allowances: enable checked rows
    document.querySelectorAll('.allowance-checkbox:checked').forEach(cb => {
        const row = cb.closest('.allowance-row');
        row.querySelector('.allowance-amount').disabled = false;
    });

    // Leaves: ONLY enable hidden credits input for checked rows
    // Unchecked rows stay disabled → won't submit → no validation error
    document.querySelectorAll('#leaves-list .leave-row').forEach(row => {
        const checkbox    = row.querySelector('.leave-checkbox');
        const creditInput = row.querySelector('.leave-credits-input');
        if (creditInput) {
            creditInput.disabled = !checkbox?.checked;
        }
    });

});

(function(){
    const container = document.getElementById('allowances-list');

    // enable / disable inputs on checkbox toggle
    container?.addEventListener('change', function(e){
        if(e.target.classList.contains('allowance-checkbox')){
            const row = e.target.closest('.allowance-row');
            if(!row) return;

            const amount = row.querySelector('.allowance-amount');
            const count  = row.querySelector('.allowance-count');

            if(e.target.checked){
                amount.disabled = false;
                count.disabled  = false;
            } else {
                amount.disabled = true;
                count.disabled  = true;
                amount.value = '';
                count.value  = '';
            }
        }
    });

    // remove row
    container?.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-allowance')){
            e.target.closest('.allowance-row')?.remove();
        }
    });
})();

(function(){
    // Leaves toggle enable/disable and remove
    const leavesContainer = document.getElementById('leaves-list');
    leavesContainer?.addEventListener('change', function(e){
        if(e.target && e.target.classList.contains('leave-checkbox')){
            const cb = e.target;
            const row = cb.closest('.leave-row');
            if(!row) return;
            const days = row.querySelector('.leave-days');
            const eff = row.querySelector('.leave-effective');
            if(cb.checked){
                if(days){ days.disabled = false; }
                if(eff){ eff.disabled = false; }
            } else {
                if(days){ days.disabled = true; days.value = ''; }
                if(eff){ eff.disabled = true; eff.value = ''; }
            }
        }
    });
    leavesContainer?.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('remove-leave')){
            const row = e.target.closest('.leave-row');
            if(row) row.remove();
        }
    });
})();

// Work info add/edit (table + hidden inputs)
(function(){
    let wiIndex = 0;
    const openBtn = document.getElementById('open-workinfo-form');
    const formPane = document.getElementById('workinfo-form');
    const cancelBtn = document.getElementById('cancel-workinfo');
    const saveBtn = document.getElementById('save-workinfo');
    const tableBody = document.querySelector('#workinfo-table tbody');

    openBtn?.addEventListener('click', function(){
        formPane.style.display = '';
    });
    cancelBtn?.addEventListener('click', function(){
        formPane.style.display = 'none';
        // clear inputs
        ['wi_hire_date','wi_status','wi_designation','wi_department','wi_supervisor','wi_monthly_rate','wi_daily_rate','wi_hourly_rate'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    });

    // track editing state
    let editingRow = null;
    saveBtn?.addEventListener('click', function(){
        // read values
        const data = {
            hire_date: document.getElementById('wi_hire_date')?.value || '',
            employment_status: document.getElementById('wi_status')?.value || '',
            designation_id: document.getElementById('wi_designation')?.value || '',
            designation_text: document.getElementById('wi_designation')?.selectedOptions?.[0]?.text || '',
            department_id: document.getElementById('wi_department')?.value || '',
            department_text: document.getElementById('wi_department')?.selectedOptions?.[0]?.text || '',
            supervisor: document.getElementById('wi_supervisor')?.value || '',
            monthly_rate: document.getElementById('wi_monthly_rate')?.value || '',
            daily_rate: document.getElementById('wi_daily_rate')?.value || '',
            hourly_rate: document.getElementById('wi_hourly_rate')?.value || '',
        };

        // simple validation: require hire_date
        if(!data.hire_date){ alert('Please enter Date'); return; }

        // create or update table row with visible text and hidden inputs
        const renderRow = (index) => {
            return `
            <td>${data.hire_date}<input type="hidden" name="employee_work_informations[${index}][hire_date]" value="${data.hire_date}"></td>
            <td>${data.employment_status}<input type="hidden" name="employee_work_informations[${index}][employment_status_id]" value="${data.employment_status}"></td>
            <td>${data.designation_text}<input type="hidden" name="employee_work_informations[${index}][designation_id]" value="${data.designation_id}"></td>
            <td>${data.department_text}<input type="hidden" name="employee_work_informations[${index}][department_id]" value="${data.department_id}"></td>
            <td>${data.supervisor}<input type="hidden" name="employee_work_informations[${index}][direct_supervisor]" value="${data.supervisor}"></td>
            <td>${data.monthly_rate}<input type="hidden" name="employee_work_informations[${index}][monthly_rate]" value="${data.monthly_rate}"></td>
            <td>${data.daily_rate}<input type="hidden" name="employee_work_informations[${index}][daily_rate]" value="${data.daily_rate}"></td>
            <td>${data.hourly_rate}<input type="hidden" name="employee_work_informations[${index}][hourly_rate]" value="${data.hourly_rate}"></td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-secondary edit-workinfo-row">Edit</button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-workinfo-row">Remove</button>
            </td>
        `;
        };

        if (editingRow) {
            // replace existing
            const idx = editingRow.getAttribute('data-wi-index');
            editingRow.innerHTML = renderRow(idx);
            editingRow.removeAttribute('data-editing');
            editingRow = null;
        } else {
            const tr = document.createElement('tr');
            tr.innerHTML = renderRow(wiIndex);
            tr.setAttribute('data-wi-index', wiIndex);
            tableBody.appendChild(tr);
            wiIndex++;
        }

        // hide form and clear
        cancelBtn.click();
    });

    // remove row
    document.querySelector('#workinfo-table')?.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-workinfo-row')){
            e.target.closest('tr').remove();
        }
        if(e.target.classList.contains('edit-workinfo-row')){
            // populate form with values from the row for editing
            const tr = e.target.closest('tr');
            if(!tr) return;
            const inputs = tr.querySelectorAll('input[type="hidden"]');
            // helper to find hidden input for a given suffix name
            const getVal = (suffix) => {
                for(const inp of inputs){
                    if(inp.name && inp.name.endsWith(suffix)) return inp.value;
                }
                return '';
            };
            document.getElementById('wi_hire_date').value = getVal('[hire_date]') || '';
            document.getElementById('wi_status').value = getVal('[employment_status_id]') || '';
            const desId = getVal('[designation_id]') || '';
            const depId = getVal('[department_id]') || '';
            if(document.getElementById('wi_designation')) document.getElementById('wi_designation').value = desId;
            if(document.getElementById('wi_department')) document.getElementById('wi_department').value = depId;
            document.getElementById('wi_supervisor').value = getVal('[direct_supervisor]') || '';
            document.getElementById('wi_monthly_rate').value = getVal('[monthly_rate]') || '';
            document.getElementById('wi_daily_rate').value = getVal('[daily_rate]') || '';
            document.getElementById('wi_hourly_rate').value = getVal('[hourly_rate]') || '';
            // set editing marker
            tr.setAttribute('data-editing', '1');
            editingRow = tr;
            // show the form
            document.getElementById('workinfo-form').style.display = '';
        }
    });
})();

// Activate tab from URL hash (e.g. /users/create#access)
(function(){
    function activateTabFromHash(hash){
        if(!hash) return;
        try{
            // prefer selector matching the tab href
            var selector = 'a.nav-link[href="' + hash + '"]';
            var tabLink = document.querySelector(selector);
            if(tabLink){
                // If jQuery + bootstrap tab plugin available (Bootstrap 4)
                if(window.jQuery && typeof window.jQuery.fn.tab === 'function'){
                    window.jQuery(selector).tab('show');
                    return;
                }
                // Bootstrap 5: use the JS API
                if(window.bootstrap && typeof window.bootstrap.Tab === 'function'){
                    var tab = new window.bootstrap.Tab(tabLink);
                    tab.show();
                    return;
                }
                // Fallback: manually activate classes
                // deactivate active links/panes
                document.querySelectorAll('.nav-link').forEach(function(el){ el.classList.remove('active'); el.setAttribute('aria-selected','false'); });
                document.querySelectorAll('.tab-pane').forEach(function(p){ p.classList.remove('show','active'); });
                tabLink.classList.add('active');
                tabLink.setAttribute('aria-selected','true');
                var targetId = tabLink.getAttribute('href');
                var pane = document.querySelector(targetId);
                if(pane){ pane.classList.add('show','active'); }
            }
        }catch(e){ console.error('activateTabFromHash error', e); }
    }

    // on load
    document.addEventListener('DOMContentLoaded', function(){
        activateTabFromHash(window.location.hash);
        // server can request an active tab after validation via session('active_tab')
        try {
            var serverTab = "{{ session('active_tab', '') }}";
            if(serverTab) activateTabFromHash('#' + serverTab);
        } catch(e){}
    });

    // when the hash changes (back/forward or link click)
    window.addEventListener('hashchange', function(){
        activateTabFromHash(window.location.hash);
    });
})();

// Repeatable dependents
(() => {
    let depIndex = 1;
    document.getElementById('add-dependent')?.addEventListener('click', function(){
        const container = document.getElementById('dependents-list');
        const row = document.createElement('div');
        row.className = 'dependent-row row mb-2';
        row.innerHTML = `\
            <div class="col-md-3"><input type="text" name="dependents[${depIndex}][name]" class="form-control" placeholder="Name"></div>\
            <div class="col-md-2"><input type="date" name="dependents[${depIndex}][birthdate]" class="form-control"></div>\
            <div class="col-md-1"><input type="number" name="dependents[${depIndex}][age]" class="form-control" placeholder="Age"></div>\
            <div class="col-md-3">\
                <select name="dependents[${depIndex}][gender]" class="form-control">\
                    <option value="">-- Select Gender --</option>\
                    <option value="Male">Male</option>\
                    <option value="Female">Female</option>\
                    <option value="Other">Other</option>\
                </select>\
            </div>\
            <div class="col-md-2">\
                <select name="dependents[${depIndex}][relationship]" class="form-control">\
                    <option value="">-- Relationship --</option>\
                    <option value="Son">Son</option>\
                    <option value="Daughter">Daughter</option>\
                    <option value="Parent">Parent</option>\
                    <option value="Spouse">Spouse</option>\
                    <option value="Other">Other</option>\
                </select>\
            </div>\
            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-dependent">-</button></div>`;
        container.appendChild(row);
        depIndex++;
    });
    document.getElementById('dependents-list')?.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-dependent')){
            e.target.closest('.dependent-row').remove();
        }
    });
})();

// Repeatable work info
(() => {
    let wIndex = 1;
    document.getElementById('add-workinfo')?.addEventListener('click', function(){
        const container = document.getElementById('workinfo-list');
        const row = document.createElement('div');
        row.className = 'workinfo-row row mb-2';
        row.innerHTML = `\
            <div class="col-md-3"><input type="date" name="employee_work_informations[${wIndex}][hire_date]" class="form-control"></div>\
            <div class="col-md-2"><input type="number" name="employee_work_informations[${wIndex}][employment_status_id]" class="form-control"></div>\
            <div class="col-md-2"><input type="number" name="employee_work_informations[${wIndex}][designation_id]" class="form-control"></div>\
            <div class="col-md-2"><input type="number" name="employee_work_informations[${wIndex}][department_id]" class="form-control"></div>\
            <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger remove-workinfo">-</button></div>\
            <div class="col-12 mt-2">\
                <div class="row">\
                    <div class="col-md-4"><input type="number" step="0.01" name="employee_work_informations[${wIndex}][monthly_rate]" class="form-control" placeholder="Monthly rate"></div>\
                    <div class="col-md-4"><input type="number" step="0.01" name="employee_work_informations[${wIndex}][daily_rate]" class="form-control" placeholder="Daily rate"></div>\
                    <div class="col-md-4"><input type="number" step="0.01" name="employee_work_informations[${wIndex}][hourly_rate]" class="form-control" placeholder="Hourly rate"></div>\
                </div>\
            </div>`;
        container.appendChild(row);
        wIndex++;
    });
    document.getElementById('workinfo-list')?.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-workinfo')){
            e.target.closest('.workinfo-row').remove();
        }
    });
})();

// Repeatable branch-permissions rows
(() => {
    let bpIndex = 1; // starting from 1 since initial row is 0
    const container = document.getElementById('branch-permissions-list');
    const addBtn = document.getElementById('add-branch-permission');
    addBtn?.addEventListener('click', function(){
        const row = document.createElement('div');
        row.className = 'branch-permission-row form-row align-items-center mb-2';
        // build branch select options (serialize from server-side values)
        let branchOptions = `\n                                                <option value="">-- Select branch --</option>`;
        @foreach($branches as $branch)
            branchOptions += `\n                                                <option value="{{ $branch->id }}">{{ addslashes($branch->name) }}</option>`;
        @endforeach

        let permOptions = ``;
        @foreach($roles as $role)
            permOptions += `\n                                                    <option value="{{ $role->id }}">{{ addslashes($role->name) }}</option>`;
        @endforeach

        row.innerHTML = `\n                <div class="col-md-5">\n                    <select name="branch_permissions[${bpIndex}][branch_id]" class="form-control">${branchOptions}\n                    </select>\n                </div>\n                <div class="col-md-6">\n                    <select name="branch_permissions[${bpIndex}][permissions][]" class="form-control" multiple>\n                        ${permOptions}\n                    </select>\n                </div>\n                <div class="col-md-1">\n                    <button type="button" class="btn btn-sm btn-outline-danger remove-branch">-</button>\n                </div>`;

        container.appendChild(row);
        bpIndex++;
    });

    container?.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-branch')){
            const row = e.target.closest('.branch-permission-row');
            if(row) row.remove();
        }
    });
})();
</script>

<script>
// Track which shift id originally had the saved custom values (if any).
// This prevents applying a previously-saved custom time blob to a different
// template when the user selects another shift.
window.__initialAssignedShiftId = document.getElementById('assigned_shift_id')?.value || '';

// Weekly customization moved to per-date controls rendered under each preset date card.
// The previous per-week arrays/hidden inputs were removed in favour of per-date inputs.

document.getElementById('shift_select').addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    const modal = $('#shiftModal');
    const shiftId = option.value;

    // set assigned shift hidden input so form submits the chosen template
    document.getElementById('assigned_shift_id').value = shiftId || '';

    // if no template selected, just hide modal and return
    if (!shiftId) {
        modal.modal('hide');
        return;
    }

    const shift = JSON.parse(option.dataset.shift);
    // Update modal title
    document.getElementById('modal-shift-name').textContent = shift.name + ' - Customize';

    // Only prefer existing saved custom values when those custom values belong
    // to the same shift template. This prevents a previous template's custom
    // blob from overriding the selected template's times.
    const initialAssigned = window.__initialAssignedShiftId || '';
    let useCustom = false;
    try { useCustom = initialAssigned && initialAssigned.toString() === shiftId.toString(); } catch (e) { useCustom = false; }

    const customTs = useCustom ? (document.getElementById('custom_time_start_input')?.value || '') : '';
    const customTe = useCustom ? (document.getElementById('custom_time_end_input')?.value || '') : '';
    const customBs = useCustom ? (document.getElementById('custom_break_start_input')?.value || '') : '';
    const customBe = useCustom ? (document.getElementById('custom_break_end_input')?.value || '') : '';
    let customWorkDays = useCustom ? (document.getElementById('custom_work_days_input')?.value || '') : '';
    let customRestDays = useCustom ? (document.getElementById('custom_rest_days_input')?.value || '') : '';
    let customOpenTime = useCustom ? (document.getElementById('custom_open_time_input')?.value || '') : '';

    try { customWorkDays = customWorkDays ? JSON.parse(customWorkDays) : []; } catch(e){ customWorkDays = []; }
    try { customRestDays = customRestDays ? JSON.parse(customRestDays) : []; } catch(e){ customRestDays = []; }
    try { customOpenTime = customOpenTime ? JSON.parse(customOpenTime) : []; } catch(e){ customOpenTime = []; }

    // Build a view-model for the modal that prefers saved custom values only when
    // they belong to the same shift template.
    window.currentShiftInModal = Object.assign({}, shift, {
        time_start: customTs || shift.time_start,
        time_end: customTe || shift.time_end,
        break_start: customBs || shift.break_start,
        break_end: customBe || shift.break_end,
        work_days: (Array.isArray(customWorkDays) && customWorkDays.length) ? customWorkDays : (shift.work_days || []),
        rest_days: (Array.isArray(customRestDays) && customRestDays.length) ? customRestDays : (shift.rest_days || []),
        open_time: (Array.isArray(customOpenTime) && customOpenTime.length) ? customOpenTime : (shift.open_time || []),
    });

    // Reset preview times from the modal view-model (may be custom)
    document.getElementById('pv-start').textContent = (window.currentShiftInModal.time_start || '').slice(0,5) || 'N/A';
    document.getElementById('pv-end').textContent = (window.currentShiftInModal.time_end || '').slice(0,5) || 'N/A';
    document.getElementById('pv-break-start').textContent = (window.currentShiftInModal.break_start || '').slice(0,5) || 'N/A';
    document.getElementById('pv-break-end').textContent = (window.currentShiftInModal.break_end || '').slice(0,5) || 'N/A';

    // render preset-per-date cards if a date range is already set
    if(document.getElementById('preset_start') && document.getElementById('preset_end')){
        const container = document.getElementById('preset-dates-list');
        // If the user already generated/edited per-date cards (they exist in DOM),
        // avoid overwriting them when re-opening the modal — keep the edits and
        // refresh the preview/hidden inputs instead. Otherwise render new cards
        // using the modal view-model (which prefers saved custom values).
        // If the existing generated preset cards belong to a different shift,
        // clear them (and the preset date inputs) so the newly-selected template
        // starts with its own preset range. This prevents a previously-generated
        // range from one template (e.g. Graveyard) being shown for another.
        const generatedFor = container?.dataset?.generatedFor || '';
        if (generatedFor && generatedFor.toString() !== (shiftId || '').toString()) {
            container.innerHTML = '';
            const ps = document.getElementById('preset_start');
            const pe = document.getElementById('preset_end');
            if(ps) ps.value = '';
            if(pe) pe.value = '';
        }

        if (!container || container.children.length === 0) {
            renderPresetDates(window.currentShiftInModal || shift);
        } else {
            // ensure preview and hidden inputs reflect any existing edits
            try { aggregateShiftModalAndPopulateHidden(); } catch(e) { /* ignore */ }
        }
    }

    modal.modal('show');
});

/**
 * Render per-date schedule cards for the given preset start/end range.
 * Creates inputs named salary_method[preset_dates][i][date|time_start|time_end|lunch_start|lunch_end]
 */
function renderPresetDates(shift){
    const container = document.getElementById('preset-dates-list');
    if(!container) return;
    container.innerHTML = '';
    const start = document.getElementById('preset_start')?.value;
    const end = document.getElementById('preset_end')?.value;
    if(!start || !end) return;
    const s = new Date(start);
    const e = new Date(end);
    if(s > e) return;

    // format helper
    const fmt = (d) => d.toISOString().slice(0,10);
    const human = (d) => d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric', weekday: 'long' });

    let idx = 0;
    for(let dt = new Date(s); dt <= e; dt.setDate(dt.getDate()+1)){
        const dateStr = fmt(dt);

        // determine weekday name (e.g. 'Thursday') to default the per-date radio
        const weekday = new Date(dt).toLocaleDateString(undefined, { weekday: 'long' });
        let checkedWork = '';
        let checkedRest = '';
        let checkedOpen = '';
        try {
            if (shift && Array.isArray(shift.work_days) && shift.work_days.includes(weekday)) checkedWork = 'checked';
            else if (shift && Array.isArray(shift.rest_days) && shift.rest_days.includes(weekday)) checkedRest = 'checked';
            else if (shift && Array.isArray(shift.open_time) && shift.open_time.includes(weekday)) checkedOpen = 'checked';
        } catch (e) { /* ignore */ }

        const card = document.createElement('div');
        card.className = 'card mb-2';
        card.innerHTML = `
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div><strong>${human(new Date(dt))}</strong></div>
                    <div class="text-muted small">${dateStr}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"><label>Start</label><input type="time" name="salary_method[preset_dates][${idx}][time_start]" class="form-control" value="${shift.time_start ? shift.time_start.slice(0,5) : ''}"></div>
                    <div class="col-md-3"><label>End</label><input type="time" name="salary_method[preset_dates][${idx}][time_end]" class="form-control" value="${shift.time_end ? shift.time_end.slice(0,5) : ''}"></div>
                    <div class="col-md-3"><label>Lunch Start</label><input type="time" name="salary_method[preset_dates][${idx}][lunch_start]" class="form-control" value="${shift.break_start ? shift.break_start.slice(0,5) : ''}"></div>
                    <div class="col-md-3"><label>Lunch End</label><input type="time" name="salary_method[preset_dates][${idx}][lunch_end]" class="form-control" value="${shift.break_end ? shift.break_end.slice(0,5) : ''}"></div>
                </div>
                <div class="mb-2"><strong>Breaks</strong></div>

                <!-- Per-date customizable type: Work Day / Rest Day / Open Time -->
                <div class="mb-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="preset_${idx}_work" name="salary_method[preset_dates][${idx}][day_type]" value="work" ${checkedWork}>
                        <label class="form-check-label" for="preset_${idx}_work">Work Day</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="preset_${idx}_rest" name="salary_method[preset_dates][${idx}][day_type]" value="rest" ${checkedRest}>
                        <label class="form-check-label" for="preset_${idx}_rest">Rest Day</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="preset_${idx}_open" name="salary_method[preset_dates][${idx}][day_type]" value="open" ${checkedOpen}>
                        <label class="form-check-label" for="preset_${idx}_open">Open Time</label>
                    </div>
                </div>

                <input type="hidden" name="salary_method[preset_dates][${idx}][date]" value="${dateStr}">
            </div>
        `;
        container.appendChild(card);
        idx++;
    }

    // mark which shift these generated cards belong to so switching templates can clear them
    try {
        const ownerId = (shift && (shift.id || shift.ID)) ? (shift.id || shift.ID).toString() : (document.getElementById('assigned_shift_id')?.value || '');
        container.dataset.generatedFor = ownerId || '';
    } catch(e) { /* ignore */ }
}

// Call render when the preset date inputs change
document.getElementById('preset_start')?.addEventListener('change', function(){ renderPresetDates(window.currentShiftInModal || {}); });
document.getElementById('preset_end')?.addEventListener('change', function(){ renderPresetDates(window.currentShiftInModal || {}); });

// Apply button: explicitly render per-day preset cards when clicked (keeps modal open)
document.getElementById('apply-preset-btn')?.addEventListener('click', function(){
    renderPresetDates(window.currentShiftInModal || {});
});

// Aggregate per-date preset values into the hidden fields expected by the server
function aggregateShiftModalAndPopulateHidden(){
    const container = document.getElementById('preset-dates-list');
    if(!container) return;
    const cards = container.querySelectorAll('.card');
    const work = [], rest = [], openTimes = {};
    let first_time_start = '';
    let first_time_end = '';
    let first_lunch_start = '';
    let first_lunch_end = '';

    cards.forEach(card => {
        const dateInp = card.querySelector('input[name$="[date]"]');
        const date = dateInp ? dateInp.value : null;
        const tstart = card.querySelector('input[name$="[time_start]"]');
        const tend = card.querySelector('input[name$="[time_end]"]');
        const lstart = card.querySelector('input[name$="[lunch_start]"]');
        const lend = card.querySelector('input[name$="[lunch_end]"]');
        const dayType = card.querySelector('input[type="radio"][name$="[day_type]"]:checked');
        const type = dayType ? dayType.value : 'work';

        if(date){
            // Record the date into the appropriate work/rest arrays
            if(type === 'work') work.push(date);
            else if(type === 'rest') rest.push(date);

            // Always record per-date time details (so server receives the
            // full preset schedule for every date). Include the day_type so
            // consumers can still tell work/rest/open per date.
            openTimes[date] = {
                start: tstart && tstart.value ? tstart.value : null,
                end: tend && tend.value ? tend.value : null,
                lunch_start: lstart && lstart.value ? lstart.value : null,
                lunch_end: lend && lend.value ? lend.value : null,
                day_type: type
            };
        }

        if(!first_time_start && tstart && tstart.value) first_time_start = tstart.value;
        if(!first_time_end && tend && tend.value) first_time_end = tend.value;
        if(!first_lunch_start && lstart && lstart.value) first_lunch_start = lstart.value;
        if(!first_lunch_end && lend && lend.value) first_lunch_end = lend.value;
    });

    const shift = window.currentShiftInModal || {};
    if(!first_time_start && shift.time_start) first_time_start = (shift.time_start||'').slice(0,5);
    if(!first_time_end && shift.time_end) first_time_end = (shift.time_end||'').slice(0,5);
    if(!first_lunch_start && shift.break_start) first_lunch_start = (shift.break_start||'').slice(0,5);
    if(!first_lunch_end && shift.break_end) first_lunch_end = (shift.break_end||'').slice(0,5);

    document.getElementById('custom_time_start_input').value = first_time_start || '';
    document.getElementById('custom_time_end_input').value = first_time_end || '';
    document.getElementById('custom_break_start_input').value = first_lunch_start || '';
    document.getElementById('custom_break_end_input').value = first_lunch_end || '';

    document.getElementById('custom_work_days_input').value = JSON.stringify(work);
    document.getElementById('custom_rest_days_input').value = JSON.stringify(rest);
    // custom_open_time is an object keyed by date → details; controller will json_decode
    document.getElementById('custom_open_time_input').value = JSON.stringify(openTimes);

    // update preview display
    document.getElementById('pv-start').textContent = first_time_start || '-';
    document.getElementById('pv-end').textContent = first_time_end || '-';
    document.getElementById('pv-break-start').textContent = first_lunch_start || '-';
    document.getElementById('pv-break-end').textContent = first_lunch_end || '-';
}

// Save changes button: aggregate values then close modal
document.getElementById('save-shift-modal-btn')?.addEventListener('click', function(){
    aggregateShiftModalAndPopulateHidden();
    // remember that the current assigned_shift now has the saved custom blob
    try {
        window.__initialAssignedShiftId = document.getElementById('assigned_shift_id')?.value || window.__initialAssignedShiftId;
    } catch(e) {}

    // also persist which shift the custom blob belongs to so server can receive it
    try {
        const assigned = document.getElementById('assigned_shift_id')?.value || '';
        if(document.getElementById('custom_for_shift_input')){
            document.getElementById('custom_for_shift_input').value = assigned;
        }
    } catch(e) {}

    // close modal (Bootstrap)
    try{ $('#shiftModal').modal('hide'); } catch(e){ /* ignore */ }
});

// live-update preview + hidden fields while editing per-date times
document.getElementById('preset-dates-list')?.addEventListener('input', function(e){
    if(e.target && e.target.matches('input[type="time"]')){
        aggregateShiftModalAndPopulateHidden();
    }
});

// Live update time preview from custom inputs
document.querySelectorAll('#shiftModal input[type="time"]').forEach(input => {
    input.addEventListener('input', function () {
        const map = {
            'salary_method[custom_time_start]': 'pv-start',
            'salary_method[custom_time_end]': 'pv-end',
            'salary_method[custom_break_start]': 'pv-break-start',
            'salary_method[custom_break_end]': 'pv-break-end',
        };
        const targetId = map[this.name];
        if (targetId) {
            document.getElementById(targetId).textContent = this.value || '-';
        }
    });
});

// Auto-open modal + load existing custom data on edit/validation
$(document).ready(function () {
    const hasShift = {{ old('salary_method.shift_id') ? 'true' : (isset($user) && $user->salaryMethod && $user->salaryMethod->shift_id ? 'true' : 'false') }};

    if (hasShift) {
        $('#shift_select').trigger('change');

        // Trigger input events to update preview from existing custom times
        $('#shiftModal input[type="time"]').each(function() {
            if (this.value) $(this).trigger('input');
        });
    }

});

// Enable/disable inputs when checkbox is toggled
document.getElementById('allowances-list').addEventListener('change', function(e) {
    if (e.target.classList.contains('allowance-checkbox')) {
        const row = e.target.closest('.allowance-row');
        const amountInput = row.querySelector('.allowance-amount');
        const countInput = row.querySelector('.allowance-count');
        const buttons = row.querySelectorAll('button.increment-amount, button.decrement-amount, button.increment-count, button.decrement-count');

        if (e.target.checked) {
            amountInput.disabled = false;
            countInput.disabled = false;
            buttons.forEach(btn => btn.disabled = false);
        } else {
            amountInput.disabled = true;
            countInput.disabled = true;
            buttons.forEach(btn => btn.disabled = true);
            amountInput.value = '';
            countInput.value = '';
        }
    }
});

// + / - buttons for Amount
document.getElementById('allowances-list').addEventListener('click', function(e) {
    if (e.target.classList.contains('increment-amount') || e.target.classList.contains('decrement-amount')) {
        const input = e.target.closest('.input-group').querySelector('.allowance-amount');
        if (input.disabled) return;

        let value = parseFloat(input.value) || 0;
        const step = parseFloat(input.step) || 100;

        if (e.target.classList.contains('increment-amount')) {
            value += step;
        } else if (e.target.classList.contains('decrement-amount') && value > 0) {
            value = Math.max(0, value - step);
        }

        input.value = value;
    }

    // + / - buttons for Monthly Count
    if (e.target.classList.contains('increment-count') || e.target.classList.contains('decrement-count')) {
        const input = e.target.closest('.input-group').querySelector('.allowance-count');
        if (input.disabled) return;

        let value = parseInt(input.value) || 0;
        const step = parseInt(input.step) || 1;

        if (e.target.classList.contains('increment-count')) {
            value += step;
        } else if (e.target.classList.contains('decrement-count') && value > 0) {
            value = Math.max(0, value - step);
        }

        input.value = value;
    }
});

// Initialize state on page load (important for validation errors with old input)
document.querySelectorAll('.allowance-checkbox').forEach(checkbox => {
    if (checkbox.checked) {
        const row = checkbox.closest('.allowance-row');
        row.querySelectorAll('.allowance-amount, .allowance-count').forEach(input => input.disabled = false);
        row.querySelectorAll('button.increment-amount, button.decrement-amount, button.increment-count, button.decrement-count')
            .forEach(btn => btn.disabled = false);
    }
});

// Leaves: Enable/disable + button handling
document.getElementById('leaves-list').addEventListener('change', function(e) {
    if (e.target.classList.contains('leave-checkbox')) {
        const row = e.target.closest('.leave-row');
        const daysInput = row.querySelector('.leave-days');
        const dateInput = row.querySelector('.leave-effective');
        const buttons = row.querySelectorAll('.increment-days, .decrement-days');

        if (e.target.checked) {
            daysInput.disabled = false;
            dateInput.disabled = false;
            buttons.forEach(btn => btn.disabled = false);
        } else {
            daysInput.disabled = true;
            dateInput.disabled = true;
            buttons.forEach(btn => btn.disabled = true);
            daysInput.value = '';
            dateInput.value = '';
        }
    }
});

// + / - buttons for Days
document.getElementById('leaves-list').addEventListener('click', function(e) {
    if (e.target.classList.contains('increment-days') || e.target.classList.contains('decrement-days')) {
        const input = e.target.closest('.input-group').querySelector('.leave-days');
        if (input.disabled) return;

        let value = parseInt(input.value) || 0;
        const step = parseInt(input.step) || 1;

        if (e.target.classList.contains('increment-days')) {
            value += step;
        } else if (e.target.classList.contains('decrement-days') && value > 0) {
            value = Math.max(0, value - step);
        }

        input.value = value;
    }
});

// Initialize on page load (for old input / validation errors)
document.querySelectorAll('.leave-checkbox').forEach(checkbox => {
    if (checkbox.checked) {
        const row = checkbox.closest('.leave-row');
        row.querySelectorAll('.leave-days, .leave-effective').forEach(input => input.disabled = false);
        row.querySelectorAll('.increment-days, .decrement-days').forEach(btn => btn.disabled = false);
    }
});

// Attachments: checkbox → enable file input + remove button
document.getElementById('attachments-list').addEventListener('change', function(e) {
    if (e.target.classList.contains('attachment-checkbox')) {
        const row = e.target.closest('.attachment-row');
        const fileInput = row.querySelector('.attachment-file');
        const fileLabel = row.querySelector('.custom-file-label');
        const removeBtn = row.querySelector('.remove-attachment');
        const nameInput = row.querySelector('.attachment-name');

        if (e.target.checked) {
            fileInput.disabled = false;
            removeBtn.disabled = false;
            nameInput.disabled = false;
        } else {
            fileInput.disabled = true;
            fileInput.value = '';
            fileLabel.textContent = 'Choose file...';
            removeBtn.disabled = true;
            nameInput.disabled = true;
        }
    }
});

// Update file label when file selected
document.getElementById('attachments-list').addEventListener('change', function(e) {
    if (e.target.classList.contains('attachment-file')) {
        const label = e.target.closest('.input-group').querySelector('.custom-file-label');
        if (e.target.files.length > 0) {
            label.textContent = e.target.files[0].name;
        } else {
            label.textContent = 'Choose file...';
        }
    }
});

// Remove button (clears checkbox and file)
document.getElementById('attachments-list').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-attachment')) {
        const row = e.target.closest('.attachment-row');
        const checkbox = row.querySelector('.attachment-checkbox');
        const fileInput = row.querySelector('.attachment-file');
        const label = row.querySelector('.custom-file-label');

        checkbox.checked = false;
        fileInput.value = '';
        label.textContent = 'Choose file...';
        fileInput.disabled = true;
        e.target.disabled = true;
    }
});

// Initialize on load (for edit form with old values)
document.querySelectorAll('.attachment-checkbox').forEach(cb => {
    if (cb.checked) {
        const row = cb.closest('.attachment-row');
        row.querySelectorAll('.attachment-file, .remove-attachment').forEach(el => el.disabled = false);
    }
});
</script>

<script>
// Global counter for new rows
let branchIndex = 1;

// Toggle dropdown visibility
window.togglePermissionsDropdown = function(index) {
    const dropdown = document.getElementById(`permissionsDropdown_${index}`);
    if (dropdown) {
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }
};

// Update displayed text + hidden value when checkboxes change
window.updateSelectedPermissions = function(index) {
    const container = document.getElementById(`permissionsDropdown_${index}`);
    if (!container) return;

    const checkboxes = container.querySelectorAll('.permission-checkbox:checked');
    const names = Array.from(checkboxes).map(cb => cb.dataset.name);
    const ids   = Array.from(checkboxes).map(cb => cb.value);

    // Update visible input
    const displayInput = document.querySelector(`input[name="branch_permissions[${index}][permissions_display]"]`);
    if (displayInput) {
        displayInput.value = names.length > 0 ? names.join(', ') : 'Select permissions...';
    }

    // Update hidden input (the one that submits)
    const hiddenInput = document.querySelector(`input[name="branch_permissions[${index}][permissions][]"]`);
    if (hiddenInput) {
        hiddenInput.value = ids.join(',');
    }
};

// ── Leave History Modal ──────────────────────────────────────────
function openLeaveHistory(leaveId, leaveName) {
    // Update modal title
    document.getElementById('lh-leave-name').textContent = leaveName;

    // Reset state
    document.getElementById('lh-loading').style.display = '';
    document.getElementById('lh-content').style.display = 'none';
    document.getElementById('lh-empty').style.display   = 'none';

    // Open modal — Bootstrap 4 (jQuery)
    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
        window.jQuery('#leaveHistoryModal').modal('show');
    } else if (window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('leaveHistoryModal')
        ).show();
    }

    const userId = document.getElementById('lh-user-id')?.value || null;

    function fmtDate(str) {
        if (!str) return '—';
        try { return new Date(str).toLocaleDateString(); } catch (e) { return str; }
    }
    function makeRef(prefix, id, idx) {
        return `${prefix}-${String(id).padStart(2,'0')}-${String(idx+1).padStart(3,'0')}`;
    }

    // AFTER
    if (!userId) {
        document.getElementById('lh-loading').style.display = 'none';

        const leaveRow   = document.querySelector(`.leave-row[data-leave-id="${leaveId}"]`);
        const creditsSpan = leaveRow?.querySelector('.leave-credits');
        const credits    = parseInt(creditsSpan?.textContent) || 0;

        // ← Read the date stored by save-assign-leave
        const assignedDate = creditsSpan?.dataset.assignedDate || '—';

        if (credits > 0) {
            document.getElementById('lh-credit-body').innerHTML = `
                <tr>
                    <td>${assignedDate}</td>
                    <td class="text-center">${credits}</td>
                    <td>${makeRef('LC', leaveId, 0)}</td>
                </tr>`;
            document.getElementById('lh-credit-total').textContent = credits;
            document.getElementById('lh-usage-body').innerHTML = '<tr><td colspan="3" class="text-center text-muted">No usage yet</td></tr>';
            document.getElementById('lh-usage-total').textContent = 0;
            document.getElementById('lh-balance').textContent = credits;
            document.getElementById('lh-content').style.display = '';
        } else {
            document.getElementById('lh-empty').style.display = '';
        }
        return;
    }

    // ── EDIT form: fetch from server ──
    fetch(`/users/${userId}/leave-history/${leaveId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('lh-loading').style.display = 'none';

        const credits = data.credits || [];
        const usages  = data.usages  || [];

        if (!credits.length && !usages.length) {
            document.getElementById('lh-empty').style.display = '';
            return;
        }

        // Credits
        const creditBody = document.getElementById('lh-credit-body');
        creditBody.innerHTML = '';
        let creditTotal = 0;
        credits.forEach((row, i) => {
            creditTotal += Number(row.assigned_days || 0);
            creditBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${fmtDate(row.effective_date || row.created_at)}</td>
                    <td class="text-center">${row.assigned_days || 0}</td>
                    <td>${row.reference || makeRef('LC', leaveId, i)}</td>
                </tr>`);
        });
        document.getElementById('lh-credit-total').textContent = creditTotal;

        // Usages
        const usageBody = document.getElementById('lh-usage-body');
        usageBody.innerHTML = '';
        let usageTotal = 0;
        usages.forEach((row, i) => {
            usageTotal += Number(row.days_used || 0);
            usageBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${fmtDate(row.date_of_leave || row.created_at)}</td>
                    <td class="text-center">${row.days_used || 0}</td>
                    <td>${row.reference || makeRef('LU', leaveId, i)}</td>
                </tr>`);
        });

        if (!usages.length) {
            usageBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No usage records yet</td></tr>';
        }

        document.getElementById('lh-usage-total').textContent = usageTotal;
        document.getElementById('lh-balance').textContent = data.balance ?? (creditTotal - usageTotal);
        document.getElementById('lh-content').style.display = '';
    })
    .catch(() => {
        document.getElementById('lh-loading').style.display = 'none';
        document.getElementById('lh-empty').style.display = '';
    });
}

// Add new branch row
document.getElementById('add-branch-permission')?.addEventListener('click', function() {
    const list = document.getElementById('branch-permissions-list');
    const newRow = document.createElement('div');
    newRow.className = 'branch-permission-row form-row align-items-start mb-3 position-relative';
    newRow.innerHTML = `
        <div class="col-md-5">
            <select name="branch_permissions[${branchIndex}][branch_id]" class="form-control branch-select">
                <option value="">Select branch</option>
                ${Array.from(document.querySelectorAll('.branch-select option:not([value=""])'))
                    .map(opt => `<option value="${opt.value}">${opt.textContent}</option>`).join('')}
            </select>
        </div>

        <div class="col-md-6 position-relative">
            <input type="text"
                   name="branch_permissions[${branchIndex}][permissions_display]"
                   class="form-control permissions-display"
                   placeholder="Select permissions..."
                   readonly
                   onclick="togglePermissionsDropdown(${branchIndex})">

            <input type="hidden"
                   name="branch_permissions[${branchIndex}][permissions][]"
                   class="permissions-hidden"
                   value="">

            <div class="permissions-dropdown border rounded p-2 position-absolute bg-white w-100 shadow-sm"
                 id="permissionsDropdown_${branchIndex}"
                 style="display:none; max-height:240px; overflow-y:auto; z-index:100; top:100%;">
                ${Array.from(document.querySelectorAll('#permissionsDropdown_0 .form-check'))
                    .map(item => item.outerHTML.replace(/permCheck_0_/g, `permCheck_${branchIndex}_`))
                    .join('')}
            </div>
        </div>

        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger remove-branch mt-4">-</button>
        </div>
    `;

    list.appendChild(newRow);
    branchIndex++;
});

// Remove row
document.getElementById('branch-permissions-list')?.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-branch')) {
        e.target.closest('.branch-permission-row')?.remove();
    }
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.permissions-display') && !e.target.closest('.permissions-dropdown')) {
        document.querySelectorAll('.permissions-dropdown').forEach(dd => {
            dd.style.display = 'none';
        });
    }
});

// Save Assign Leave
document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('save-assign-leave')) return;

    const leaveId   = e.target.dataset.leaveId;
    const daysInput = document.getElementById(`assign-days-${leaveId}`);
    const dateInput = document.getElementById(`assign-date-${leaveId}`);
    const days      = parseInt(daysInput.value) || 0;

    if (days < 1) {
        alert('Please enter at least 1 day.');
        return;
    }

    const leaveRow      = document.querySelector(`.leave-row[data-leave-id="${leaveId}"]`);
    const creditsSpan   = leaveRow?.querySelector('.leave-credits');
    const creditsHidden = leaveRow?.querySelector('.leave-credits-input');
    const balanceEl     = leaveRow?.querySelector('.leave-balance');
    const usedEl        = leaveRow?.querySelector('.leave-used');

    // Read current credits from the <span> textContent
    const currentCredits = parseInt(creditsSpan?.textContent) || 0;
    const newCredits     = currentCredits + days;

    const userId = document.getElementById('lh-user-id')?.value || null;

    if (!userId) {
        // CREATE form: store everything in the DOM for Leave History to read back
        const assignedDate = dateInput.value || '—';

        if (creditsSpan) {
            creditsSpan.textContent = newCredits;
            creditsSpan.dataset.assignedDate = assignedDate; // ← key fix: store date
        }
        if (creditsHidden) creditsHidden.value = newCredits;

        // Update balance
        const used = parseInt(usedEl?.textContent) || 0;
        if (balanceEl) balanceEl.textContent = newCredits - used;

        document.getElementById(`assign-form-${leaveId}`).classList.add('d-none');
        daysInput.value = '1';
        dateInput.value = '';
        return;
    }

    // EDIT form: AJAX
    const btn = e.target;
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append(`leaves[0][leave_id]`, leaveId);
    formData.append(`leaves[0][assigned_days]`, newCredits);
    formData.append(`leaves[0][effective_date]`, dateInput.value || '');

    fetch(`/users/${userId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            || document.querySelector('input[name="_token"]')?.value || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
    })
    .then(r => { if (!r.ok) throw new Error('Server error: ' + r.status); return r; })
    .then(() => {
        if (creditsSpan) creditsSpan.textContent = newCredits;
        if (creditsHidden) creditsHidden.value = newCredits;
        const used = parseInt(usedEl?.textContent) || 0;
        if (balanceEl) balanceEl.textContent = newCredits - used;
        alert(`Successfully assigned ${days} day(s).`);
        document.getElementById(`assign-form-${leaveId}`).classList.add('d-none');
        daysInput.value = '1';
        dateInput.value = '';
    })
    .catch(err => alert('Failed to save. Please try again.\n' + err.message))
    .finally(() => { btn.disabled = false; btn.textContent = 'Save'; });
});

// Cancel button
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('cancel-assign-leave')) {
        const leaveId = e.target.dataset.leaveId;
        const form = document.getElementById(`assign-form-${leaveId}`);
        if (form) {
            form.classList.add('d-none');
            // Reset
            form.querySelector(`#assign-days-${leaveId}`).value = '1';
            form.querySelector(`#assign-date-${leaveId}`).value = '';
        }
    }
});
</script>

<script>
    const assignLeaveButtons = document.querySelectorAll('.assign-leave-inline');
    const assignForms = document.querySelectorAll('.assign-leave-form');

    assignLeaveButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const leaveId = this.dataset.leaveId;
            const form = document.getElementById(`assign-form-${leaveId}`);

            // Hide all assign forms
            assignForms.forEach(form => {
                form.classList.add('d-none');
            });

            // Show the specific assign form
            if (form) {
                form.classList.remove('d-none');
            }
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const positionSelect = document.getElementById('wi_designation');
    const supervisorSelect = document.getElementById('wi_supervisor');

    if (!positionSelect || !supervisorSelect) return;

    // Store all options once
    const allSupervisorOptions = Array.from(supervisorSelect.options);

    function filterSupervisors() {
        const selectedPosition = positionSelect.value;
        
        // Clear current options except the first one ("No Supervisor")
        supervisorSelect.innerHTML = '';
        supervisorSelect.appendChild(allSupervisorOptions[0].cloneNode(true));

        let allowedDesignations = [];

        // Define rules: who can supervise whom
        if (selectedPosition === '') {
            // No position selected → show all
            allowedDesignations = ['manager', 'supervisor', 'director', 'ceo'];
        } else if (['staff', 'regular', 'employee'].includes(selectedPosition.toLowerCase())) {
            // Normal employees can have almost anyone as supervisor
            allowedDesignations = ['manager', 'supervisor', 'director', 'ceo'];
        } else if (selectedPosition.toLowerCase() === 'supervisor') {
            // Supervisors should only have higher-ups
            allowedDesignations = ['manager', 'director', 'ceo'];
        } else if (selectedPosition.toLowerCase() === 'manager') {
            allowedDesignations = ['director', 'ceo'];
        } else if (['director', 'ceo'].includes(selectedPosition.toLowerCase())) {
            // Directors/CEOs usually have no supervisor
            allowedDesignations = [];
        } else {
            // Fallback: show all
            allowedDesignations = ['manager', 'supervisor', 'director', 'ceo'];
        }

        // Add back matching options
        allSupervisorOptions.forEach(opt => {
            if (opt.value === '') return; // skip placeholder

            const optDesignation = opt.getAttribute('data-designation') || '';
            if (allowedDesignations.includes(optDesignation)) {
                supervisorSelect.appendChild(opt.cloneNode(true));
            }
        });

        // If nothing matches, keep "No Supervisor" as default
    }

    // Run on page load (for old() values)
    filterSupervisors();

    // Run when position changes
    positionSelect.addEventListener('change', filterSupervisors);
});
</script>
@endsection

