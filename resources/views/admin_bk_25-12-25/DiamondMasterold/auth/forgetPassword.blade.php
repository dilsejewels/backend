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

    <!-- Favicon -->
    {{-- <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.png') }}" /> --}}

    <style>
        .authentication-inner {
            max-width: 460px;
            position: relative;
            margin: auto;
        }
    </style>
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
    <style>
           body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
        }
        .authentication-inner {
            max-width: 460px;
            margin: auto;
               width: 100%;
        }
        
        .card-body {
            padding: 30px;
        }
    </style>

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
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <h4 class="mb-2">Forgot your password? 🔒</h4>
                        <p class="mb-4">Enter your email and we’ll send you instructions to reset your password</p>

                        <div id="success-message" style="display: {{ session('status') ? 'block' : 'none' }};">
                            <div class="text-center">
                                <div style="font-size: 48px;">✅</div>
                                <h4 class="mt-2">Success!</h4>
                                <p>{{ session('status') }}</p>
                                <a href="{{ route('admin.login') }}" class="btn btn-primary d-grid w-100 mt-3">Back to Login</a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('sendResetLink') }}" id="reset-form"
                            style="display: {{ session('status') ? 'none' : 'block' }};">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" class="form-control" name="email"
                                    value="{{ old('email') }}" required autofocus />
                            </div>
                            <button class="btn btn-primary d-grid w-100">Send Reset Link</button>
                        </form>
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
  
  $('#reset-form').on('submit', function(e) {
    e.preventDefault();

    const form = $(this);
    const email = $('#email').val();
    const submitBtn = form.find('button');

    // Disable the button and show loading text with spinner
    submitBtn.prop('disabled', true).html(`
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...
    `);

    $('.alert').remove(); // Remove any existing alerts

    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: { email: email },
        success: function(response) {
            form.hide();
            $('#success-message').html(`
                <div class="text-center">
                    <div style="font-size: 48px;">✅</div>
                    <h4 class="mt-2">Success!</h4>
                    <p>${response.message}</p>
                    <a href="{{ route('admin.login') }}" class="btn btn-primary mt-3">Back to Login</a>
                </div>
            `).show();
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                let errorHtml = '<div class="alert alert-danger"><ul>';
                for (let key in errors) {
                    errorHtml += `<li>${errors[key]}</li>`;
                }
                errorHtml += '</ul></div>';
                form.prepend(errorHtml);
            } else {
                form.prepend(`<div class="alert alert-danger">${xhr.responseJSON.message}</div>`);
            }

            // Re-enable the button and reset text
            submitBtn.prop('disabled', false).html('Send Reset Link');
        }
    });
});

</script>

</body>

</html>
