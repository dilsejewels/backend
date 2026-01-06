<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('api/assets/') }}"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Admin Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />

    <!-- Favicon -->
    {{-- <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.png') }}" /> --}}

    <style>
        .authentication-inner{
            max-width: 460px;
            position: relative;
            margin: auto;
        }
    </style>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"/>

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
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
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Register -->
          <div class="card px-sm-6 px-0">
            <div class="card-body">
              <h4 class="mb-1">Welcome! 👋</h4>
              <p class="mb-6">Please sign-in to your account and start the adventure</p>
              <form id="formAuthentication" class="mb-6" action="{{ route('admin.login.submit') }}" method="POST">
                @csrf     
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            
                <div class="mb-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required value="{{ old('email') }}" autofocus />
                </div>
            
                <div class="mb-6 form-password-toggle">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group input-group-merge">
                        <input type="password" id="password" class="form-control" name="password" placeholder="********" required />
                        <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    </div>
                </div>
            
                <div class="mb-8">
                    <div class="d-flex justify-content-between mt-8">
                        <div class="form-check mb-0 ms-2">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                            <label class="form-check-label" for="remember-me"> Remember Me </label>
                        </div>
                        <a href="{{ route('admin.forget') }}">Forgot Password?</a>
                    </div>
                </div>
                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
            </form>
              <p class="text-center">
                <span>New on our platform?</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

     <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('api/assets/vendor/libs/jquery/jquery.js') }}"></script>
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
      $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

      $(document).ready(function() {
          $('#Datatable').DataTable({
              paging: true,
              pageLength: 10,
              lengthMenu: [10, 25, 50, 100],
              searching: true,
              ordering: true,
              info: true,
              autoWidth: false,
              responsive: false,
              language: {
                  lengthMenu: "Show _MENU_ entries",
                  zeroRecords: "No matching records found",
                  info: "Showing page _PAGE_ of _PAGES_",
                  infoEmpty: "No records available",
                  infoFiltered: "(filtered from _MAX_ total records)"
              }
          });
      });
    </script>
  </body>
</html>
