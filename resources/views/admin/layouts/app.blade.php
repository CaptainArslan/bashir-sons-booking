<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--favicon-->
    <link rel="icon" href="{{ asset('admin/assets/images/favicon-32x32.png') }}" type="image/png">
    <!--plugins-->
    <link href="{{ asset('admin/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet">
    <!-- loader-->
    <link href="{{ asset('admin/assets/css/pace.min.css') }}" rel="stylesheet">
    <script src="{{ asset('admin/assets/js/pace.min.js') }}"></script>
    <!-- Bootstrap CSS -->
    <link href="{{ asset('admin/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/icons.css') }}" rel="stylesheet">
    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/dark-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/semi-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/header-colors.css') }}">
    <link href="{{ asset('admin/assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <title>@yield('title' ?? config('app.name'))</title>

    <!-- Compact Admin UI Styles -->
    <style>
        /* Global Compact Styling */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .card-body {
            padding: 1rem;
        }

        .btn {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .form-control,
        .form-select {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .table {
            font-size: 0.875rem;
        }

        .table th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 0.5rem;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            padding: 0.75rem 0.5rem;
            vertical-align: middle;
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
        }

        .page-breadcrumb {
            padding: 0.75rem 0;
            margin-bottom: 1rem;
        }

        .breadcrumb {
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            font-size: 0.8rem;
        }

        /* Compact DataTables */
        /* .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        } */

        /* Compact Dropdowns */
        .dropdown-menu {
            font-size: 0.875rem;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e9ecef;
        }

        .dropdown-item {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        /* Compact Alerts */
        .alert {
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
            border-radius: 6px;
        }

        /* Compact Modals */
        .modal-header {
            padding: 1rem 1.25rem;
        }

        .modal-body {
            padding: 1rem 1.25rem;
        }

        .modal-footer {
            padding: 0.75rem 1.25rem;
        }

        /* Compact Navigation */
        .sidebar-wrapper {
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar-menu .sidebar-link {
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
        }

        /* Compact Sidebar Menu */
        .metismenu a {
            font-size: 0.875rem !important;
            padding: 0.75rem 1rem !important;
        }

        .metismenu .parent-icon {
            width: 20px !important;
            height: 20px !important;
            font-size: 1rem !important;
        }

        .metismenu .menu-title {
            font-size: 0.875rem !important;
            font-weight: 500 !important;
        }

        .metismenu .menu-label {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            padding: 0.5rem 1rem !important;
            color: #6c757d !important;
        }

        .metismenu ul li a {
            padding: 0.5rem 1rem 0.5rem 2.5rem !important;
            font-size: 0.8rem !important;
        }

        .metismenu ul li a i {
            font-size: 0.7rem !important;
        }

        /* Compact Buttons */
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
        }

        .btn-success {
            background: linear-gradient(45deg, #28a745, #1e7e34);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(45deg, #ffc107, #e0a800);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
        }

        .btn-info {
            background: linear-gradient(45deg, #17a2b8, #138496);
            border: none;
        }

        /* Hover Effects */
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            transition: all 0.2s ease;
        }

        /* Compact Forms */
        .form-check {
            margin-bottom: 0.5rem;
        }

        .form-check-label {
            font-size: 0.875rem;
            cursor: pointer;
        }

        .form-text {
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        /* Compact Pagination */
        .pagination {
            font-size: 0.875rem;
        }

        .page-link {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>

    @yield('styles')
</head>

<body>
    <!--wrapper-->
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        @include('admin.layouts.header')
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                @include('admin.layouts.alerts')
                @yield('content')
            </div>
        </div>
        <!--end page wrapper -->
        <!--start overlay-->
        <div class="overlay toggle-icon"></div>
        <!--end overlay-->
        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->

        @include('admin.layouts.footer')

    </div>

    {{-- @include('admin.layouts.switcher') --}}
    <!-- Bootstrap JS -->
    <script src="{{ asset('admin/assets/js/bootstrap.bundle.min.js') }}"></script>
    <!--plugins-->
    <script src="{{ asset('admin/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
    {{-- <script src="{{ asset('admin/assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/chartjs/js/chart.js') }}"></script> --}}
    <script src="{{ asset('admin/assets/js/index.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    @include('admin.layouts.select2')
    @include('admin.layouts.datatables')
    @yield('scripts')
    <!--app JS-->
    <script src="{{ asset('admin/assets/js/app.js') }}"></script>
    <script>
        function showLoader(show = true, message = "Please wait...") {
            if (show) {
                Swal.fire({
                    title: message,
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            } else {
                Swal.close();
            }
        }

        // Initialize Input Masking for CNIC and Phone
        $(document).ready(function() {
            function applyCnicMask() {
                // CNIC Mask: Format 34101-1111111-1 (5 digits - 7 digits - 1 digit)
                $('input[name="cnic"], input[id="cnic"], input[name*="cnic"], input.cnic-input').not('[data-inputmask-applied]').each(function() {
                    if ($(this).attr('type') === 'number') {
                        $(this).attr('type', 'text');
                    }
                    if (typeof $.fn.inputmask !== 'undefined') {
                        $(this).inputmask('99999-9999999-9', {
                            placeholder: '_',
                            clearMaskOnLostFocus: false,
                            showMaskOnHover: true,
                            showMaskOnFocus: true
                        }).attr('data-inputmask-applied', 'true');
                    }
                });
            }

            function applyPhoneMask() {
                // Phone Mask: Format 0317-7777777 (4 digits - 7 digits)
                $('input[name="phone"], input[id="phone"], input[name*="phone"], input.phone-input').not('[data-inputmask-applied]').each(function() {
                    if ($(this).attr('type') === 'number' || $(this).attr('type') === 'tel') {
                        $(this).attr('type', 'text');
                    }
                    if (typeof $.fn.inputmask !== 'undefined') {
                        $(this).inputmask('9999-9999999', {
                            placeholder: '_',
                            clearMaskOnLostFocus: false,
                            showMaskOnHover: true,
                            showMaskOnFocus: true
                        }).attr('data-inputmask-applied', 'true');
                    }
                });
            }

            // Apply masks on page load
            applyCnicMask();
            applyPhoneMask();

            // Re-apply masks when new elements are added (for dynamic content)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        applyCnicMask();
                        applyPhoneMask();
                    }
                });
            });

            // Observe the entire document for new inputs
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>
    {{-- <script>
        new PerfectScrollbar(".app-container");
    </script> --}}
</body>

{{-- <script>
    'undefined' === typeof _trfq || (window._trfq = []);
    'undefined' === typeof _trfd && (window._trfd = []), _trfd.push({
        'tccl.baseHost': 'secureserver.net'
    }, {
        'ap': 'cpsh-oh'
    }, {
        'server': 'p3plzcpnl509132'
    }, {
        'dcenter': 'p3'
    }, {
        'cp_id': '10399385'
    }, {
        'cp_cl': '8'
    })
    // Monitoring performance to make your website faster. If you want to opt-out, please contact web hosting support.
</script> --}}
{{-- <script src='../../../signals/js/clients/scc-c2/scc-c2.min.js'></script> --}}

</html>
