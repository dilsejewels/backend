  @extends('admin.layouts.master')

  @section('main_section')
      <div class="container-xxl flex-grow-1 container-p-y">
          <div class="row">
              <div class="col-md-12">
                  <div class="nav-align-top">
                      <ul class="nav nav-pills flex-column flex-md-row mb-6">
                          <li class="nav-item">
                              <a class="nav-link" href="{{ route('admin.profile') }}"><i class="bx bx-user bx-sm me-1_5"></i>
                                  Account</a>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link active" href="javascript:void(0);"><i class="bx bx-bell bx-sm me-1_5"></i>
                                  Change Password</a>
                          </li>
                      </ul>
                  </div>
                  <div class="card">

                      <div class="card-body">
                          <h5 class="mb-1">Change Password</h5>
                          <span class="card-subtitle">If you want to change you password</span>
                          <div class="error"></div>
                      </div>
                      <form id="changePasswordForm" method="POST">
                          @csrf

                          <div class="card-body">
                              <div class="mb-3">
                                  <label for="current_password" class="form-label">Current Password</label>
                                  <input type="password" class="form-control" id="current_password" name="current_password"
                                      placeholder="Enter current password">
                                  <span class="text-danger error-text current_password_error"></span>
                              </div>

                              <div class="mb-3">
                                  <label for="new_password" class="form-label">New Password</label>
                                  <input type="password" class="form-control" id="new_password" name="new_password"
                                      placeholder="Enter new password">
                                  <span class="text-danger error-text new_password_error"></span>
                              </div>

                              <div class="mb-3">
                                  <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                  <input type="password" class="form-control" id="new_password_confirmation"
                                      name="new_password_confirmation" placeholder="Confirm new password">
                                  <span class="text-danger error-text new_password_confirmation_error"></span>
                              </div>

                              <button type="submit" class="btn btn-primary">Update Password</button>
                          </div>
                      </form>
                  </div>
              </div>
          </div>
      </div>
      <script>
 $(document).ready(function() {
    $('#changePasswordForm').on('submit', function(e) {
      e.preventDefault();

      let form = $(this);
      let url = "{{ route('admin.reset.password') }}";
      let data = form.serialize();

      // Clear all previous error texts
      form.find('.error-text').html('');

      $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(response) {
          toastr.success(response.message, 'Success');
          form[0].reset();
        },
        error: function(xhr) {
             console.log(xhr.responseText);
          if (xhr.status === 422) {
            let errors = xhr.responseJSON.errors;
            $.each(errors, function(field, messages) {
              $('.' + field + '_error').html(messages.join('<br>'));
            });
          }
        }
      });
    });
  });
</script>
  @endsection



