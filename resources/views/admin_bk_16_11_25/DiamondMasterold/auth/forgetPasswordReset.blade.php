<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('api/assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Reset Password | Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Your existing CSS and links -->
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('api/assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('api/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

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
        /* Spinner styles */
        .spinner {
            display: none;
            width: 24px;
            height: 24px;
            border: 4px solid transparent;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Hide spinner after submission */
        .spinner-container {
            position: relative;
            display: inline-block;
        }

        .success-message {
            display: none;
            text-align: center;
            font-size: 24px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <h4 class="mb-2">Reset Your Password 🔐</h4>
                        <p class="mb-4">Enter a new password for your account.</p>

                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ url('/password/reset') }}" id="reset-password-form">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>

                            <div class="spinner-container">
                                <button type="submit" class="btn btn-primary d-grid w-100" id="submit-button">
                                    Reset Password
                                </button>
                                <div class="spinner" id="spinner"></div>
                            </div>
                        </form>

                        <div id="success-message" class="success-message">
                            <p>😊 Password reset successful!</p>
                            <a href="{{ route('admin.login') }}" class="btn btn-primary">Back to login</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('api/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('api/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('api/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('api/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('api/assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('api/assets/js/main.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Handling form submission
        $('#reset-password-form').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const submitButton = $('#submit-button');
            const spinner = $('#spinner');
            const successMessage = $('#success-message');

            // Show spinner and disable the submit button
            submitButton.prop('disabled', true);
            spinner.show();

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function (response) {
                    // Show success message
                    successMessage.show();
                    form.hide();
                },
                error: function (xhr) {
                    // Handle error here
                    submitButton.prop('disabled', false);
                    spinner.hide();
                    toastr.error('There was an error, please try again.');
                }
            });
        });
    </script>

</body>

</html>
