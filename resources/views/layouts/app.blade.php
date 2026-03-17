<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdn.rawgit.com/leongersen/noUiSlider/master/distribute/nouislider.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.7.11/fabric.min.js"></script>
<script src="https://cdn.rawgit.com/leongersen/noUiSlider/master/distribute/nouislider.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- Font Awesome 6 Free (Icons only, CSS-based) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vue2-daterange-picker/dist/vue2-daterange-picker.css">

<!-- CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/perfect-scrollbar/1.5.5/css/perfect-scrollbar.min.css" />

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/perfect-scrollbar/1.5.5/perfect-scrollbar.min.js"></script>
<!-- Vue -->
<script src="https://unpkg.com/vue@2.7.14/dist/vue.js"></script>
<!-- Include Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

<!-- XLSX -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- Date Range Picker CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vue2-daterange-picker@0.6.8/dist/vue2-daterange-picker.css">

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue2-daterange-picker/dist/vue2-daterange-picker.min.js"></script>



<script>
  // Set CSRF token header for all Axios requests
  axios.defaults.headers.common['X-CSRF-TOKEN'] = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content');
</script>


<!-- Vue Select -->
<link rel="stylesheet" href="https://unpkg.com/vue-select@3.20.2/dist/vue-select.css">
<script src="https://unpkg.com/vue-select@3.20.2"></script>

<!-- BootstrapVue CSS -->
<link rel="stylesheet" href="https://unpkg.com/bootstrap-vue@2.23.1/dist/bootstrap-vue.min.css">

<!-- BootstrapVue JS -->
<script src="https://unpkg.com/bootstrap-vue@2.23.1/dist/bootstrap-vue.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>



</head>
<style>
  /* simple styles for dropdown list */
  .vs__dropdown-toggle {
    cursor: pointer;
    position: relative;
  }

  .vs__open-indicator {
    transition: transform 0.2s ease;
  }
  .vs__open-indicator.rotate {
    transform: rotate(180deg);
  }

  .vs__listbox {
    border: 1px solid #ccc;
    margin-top: 4px;
    list-style: none;
    padding: 0;
    display: none;
    max-height: 200px;
    overflow-y: auto;
    background: white;
    position: absolute;
    width: 100%;
    z-index: 1000;
  }

  .vs__listbox li {
    padding: 6px 10px;
    cursor: pointer;
  }
  .vs__listbox li:hover {
    background: #f0f0f0;
  }
  
</style>
<body>
  @php
    $user = Auth::user();
  @endphp
{{-- @if(Auth::user()->roles->contains('name', 'Administrator')) --}}
    @include('layouts.header')
    @include('layouts.sidebar')

    <main>
      <div class="main-content-wrap d-flex flex-column flex-grow-1 sidenav-open">
      @yield('content')
      @include('layouts.footer')
      </div>
    </main>
    @yield('scripts')
  {{-- @else
    <div style="text-align:center; margin-top:100px;">
      <h2>Access Denied</h2>
      <p>You do not have permission to view this page.</p>
    </div>
  @endif --}}

  @stack('scripts')
</body>
</html>
