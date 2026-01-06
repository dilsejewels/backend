<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('api/assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Admin Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
      <style>
        .profile-image-container {
            position: relative;
            display: inline-block;
        }

        .profile-image-container:hover #deleteImageBtn {
            display: block !important;
        }

        #deleteImageBtn {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 5px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10;
        }
        .popup-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 999;
        }

        .popup-outer {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .popup-inner {
            background: white;
            border-radius: 8px;
            padding: 15px;
            max-width: 370px;
            width: 100%;
            text-align: center;
        }

        .popup-card {
            width: 100%;
        }

        .remove-trash-icon {
            margin-bottom: 15px;
        }

        .removeflx-btn {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 10px;
        }

        .popup-modal.remove-modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 9999;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* overlay background */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .popup-modal .popup-outer {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .popup-modal .popup-card {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
        }
    </style>

    <!-- Favicon -->
    {{-- <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.png') }}" /> --}}

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('api/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <link rel="stylesheet" href="{{ asset('api/assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('api/assets/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('api/assets/js/config.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('admin.partials.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                @include('admin.partials.navbar')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Content -->
                    <div class="main-content">
                        <div class="page-content">
                            <div class="container-fluid">
                                @yield('main_section')
                            </div>
                        </div>
                    </div>

                    <!-- / Content -->

                    <!-- Footer -->
                    @include('admin.partials.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
               <!-- Global Delete Confirmation Modal -->
        <div class="popup-modal remove-modal" style="display: none;">
            <div class="popup-outer">
                <div class="popup-inner">
                    <div class="popup-card">
                        <div class="remove-popup-inner">
                            <div class="popbtm-dtl">
                                <div class="remove-trash-icon">
                                    <em>
                                        <em>
                                            <!-- Font Awesome Trash Icon in Blue -->
                                            <i class="fa fa-trash" style="color: #696cff; font-size: 30px;"></i>
                                        </em>
                                    </em>
                                </div>
                                <div class="remove-record-descrp">
                                    <h3>Delete it!</h3>
                                    <p>Are you sure you want to delete this record?</p>
                                </div>
                                <div class="removeflx-btn">
                                    <button type="button" class="btn btn-secondary btn-sm close-pop">No</button>
                                    <button type="button" class="btn btn-primary btn-sm"
                                        id="confirmDelete">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overlay close-pop"></div>
        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->


    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('api/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="{{ asset('api/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('api/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('api/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('api/assets/vendor/js/menu.js') }}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('api/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('api/assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('api/assets/js/dashboards-analytics.js') }}"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        function renderDataTable(tableId, rowsHtml) {

            if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
                $(`#${tableId}`).DataTable().destroy();
            }

            $(`#${tableId} tbody`).html(rowsHtml);

            $(`#${tableId}`).DataTable({
                paging: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                language: {
                    lengthMenu: "Show _MENU_ entries",
                    zeroRecords: "No matching records found",
                    info: "Showing page _PAGE_ of _PAGES_",
                    infoEmpty: "No records available",
                    infoFiltered: "(filtered from _MAX_ total records)"
                }
            });
        }
    </script>
</body>

</html>
