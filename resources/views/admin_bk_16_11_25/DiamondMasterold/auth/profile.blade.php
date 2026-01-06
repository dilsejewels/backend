  @extends('admin.layouts.master')

  @section('main_section')
      <div class="container-xxl flex-grow-1 container-p-y">
          <div class="row">
              <div class="col-md-12">
                  <div class="nav-align-top">
                      <ul class="nav nav-pills flex-column flex-md-row mb-6">
                          <li class="nav-item">
                              <a class="nav-link active" href="{{ route('admin.profile') }}"><i class="bx bx-sm bx-user me-1_5"></i>
                                  Account</a>
                          </li>
                          <li class="nav-item">
                              <a class="nav-link" href="{{ route('change.password') }}"><i
                                      class="bx bx-sm bx-bell me-1_5"></i>Change Password</a>
                          </li>
                      </ul>
                  </div>
                  <div class="card mb-6">
                      <!-- Account -->
                      <!-- Profile Image Upload -->
                      <div class="card-body">
                          <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">
                              <!-- Profile Image Container with Delete Button -->
                              <div class="profile-image-container" style="position: relative;">
                                  <img src="{{ auth()->user()->image ? asset('storage/profile/' . auth()->user()->image) : asset('api/assets/img/avatars/1.png') }}"
                                      alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" id="uploadedAvatar" />

                                  <!-- Trash bin button (visible only on hover) -->
                                  <div id="deleteImageBtn" class="delete-image-btn"
                                      style="position: absolute; top: 5px; right: 5px; display: none;">
                                      <i class="bx bx-trash"></i>
                                  </div>
                              </div>

                              <div class="button-wrapper">
                                  <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
                                      <span class="d-none d-sm-block">Upload new photo</span>
                                      <i class="bx bx-upload d-block d-sm-none"></i>
                                      <input type="file" id="upload" name="image" class="account-file-input" hidden
                                          accept="image/png, image/jpeg" />
                                  </label>

                                  <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
                                      <i class="bx bx-reset d-block d-sm-none"></i>
                                      <span class="d-none d-sm-block">Reset</span>
                                  </button>

                                  <div>Allowed JPG, GIF or PNG. Max size of 800K</div>
                              </div>
                          </div>

                      </div>

                      <div class="card-body pt-4">
                          <form id="formAccountSettings" method="POST" onsubmit="return false">
                              <div class="row g-6">
                                  <div class="col-md-6">
                                      <label for="firstName" class="form-label">First Name</label>
                                      <input class="form-control" type="text" id="firstName" name="firstName"
                                          value="{{ Auth::user()->name ?? 'John' }}" autofocus />
                                  </div>
                                  <div class="col-md-6">
                                      <label for="lastName" class="form-label">Last Name</label>
                                      <input class="form-control" type="text" name="lastName" id="lastName"
                                          value="Doe" />
                                  </div>
                                  <div class="col-md-6">
                                      <label for="email" class="form-label">E-mail</label>
                                      <input class="form-control" type="text" id="email" name="email"
                                          value="{{ Auth::user()->email}}"  readonly/>
                                  </div>
                                  <div class="col-md-6">
                                      <label for="organization" class="form-label">Organization</label>
                                      <input type="text" class="form-control" id="organization" name="organization"
                                          value="ThemeSelection" />
                                  </div>
                                  <div class="col-md-6">
                                      <label class="form-label" for="phoneNumber">Phone Number</label>
                                      <div class="input-group input-group-merge">
                                          <span class="input-group-text">US (+1)</span>
                                          <input type="text" id="phoneNumber" name="phoneNumber" class="form-control"
                                              placeholder="202 555 0111" />
                                      </div>
                                  </div>
                                  <div class="col-md-6">
                                      <label for="address" class="form-label">Address</label>
                                      <input type="text" class="form-control" id="address" name="address"
                                          placeholder="Address" />
                                  </div>
                                  <div class="col-md-6">
                                      <label for="state" class="form-label">State</label>
                                      <input class="form-control" type="text" id="state" name="state"
                                          placeholder="California" />
                                  </div>
                                  <div class="col-md-6">
                                      <label for="zipCode" class="form-label">Zip Code</label>
                                      <input type="text" class="form-control" id="zipCode" name="zipCode"
                                          placeholder="231465" maxlength="6" />
                                  </div>
                                  <div class="col-md-6">
                                      <label class="form-label" for="country">Country</label>
                                      <select id="country" class="select2 form-select">
                                          <option value="">Select</option>
                                          <option value="Australia">Australia</option>
                                          <option value="Bangladesh">Bangladesh</option>
                                          <option value="Belarus">Belarus</option>
                                          <option value="Brazil">Brazil</option>
                                          <option value="Canada">Canada</option>
                                          <option value="China">China</option>
                                          <option value="France">France</option>
                                          <option value="Germany">Germany</option>
                                          <option value="India">India</option>
                                          <option value="Indonesia">Indonesia</option>
                                          <option value="Israel">Israel</option>
                                          <option value="Italy">Italy</option>
                                          <option value="Japan">Japan</option>
                                          <option value="Korea">Korea, Republic of</option>
                                          <option value="Mexico">Mexico</option>
                                          <option value="Philippines">Philippines</option>
                                          <option value="Russia">Russian Federation</option>
                                          <option value="South Africa">South Africa</option>
                                          <option value="Thailand">Thailand</option>
                                          <option value="Turkey">Turkey</option>
                                          <option value="Ukraine">Ukraine</option>
                                          <option value="United Arab Emirates">United Arab Emirates</option>
                                          <option value="United Kingdom">United Kingdom</option>
                                          <option value="United States">United States</option>
                                      </select>
                                  </div>
                              </div>
                              <div class="mt-6">
                                  <button type="submit" class="btn btn-primary me-3">Save changes</button>
                                  <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                              </div>
                          </form>
                      </div>
                      <!-- /Account -->
                  </div>
                  <div class="card">
                      <h5 class="card-header">Delete Account</h5>
                      <div class="card-body">
                          <div class="mb-6 col-12 mb-0">
                              <div class="alert alert-warning">
                                  <h5 class="alert-heading mb-1">Are you sure you want to delete your account?</h5>
                                  <p class="mb-0">Once you delete your account, there is no going back. Please be
                                      certain.</p>
                              </div>
                          </div>
                          <form id="formAccountDeactivation" onsubmit="return false">
                              <div class="form-check my-8 ms-2">
                                  <input class="form-check-input" type="checkbox" name="accountActivation"
                                      id="accountActivation" />
                                  <label class="form-check-label" for="accountActivation">I confirm my account
                                      deactivation</label>
                              </div>
                              <button type="submit" class="btn btn-danger deactivate-account" disabled>
                                  Deactivate Account
                              </button>
                          </form>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  @endsection
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          const uploadInput = document.getElementById('upload');

          if (uploadInput) {
              uploadInput.addEventListener('change', function() {
                  const fileInput = this;
                  const file = fileInput.files[0];
                  const preview = document.getElementById('uploadedAvatar');

                  if (file) {
                      // Show preview
                      preview.src = URL.createObjectURL(file);

                      // Upload via AJAX
                      const formData = new FormData();
                      formData.append('image', file);

                      fetch("{{ route('profile.upload') }}", {
                              method: 'POST',
                              headers: {
                                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                              },
                              body: formData
                          })
                          .then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  toastr.success('Image uploaded successfully!');
                              } else {
                                  toastr.error(data.message || 'Upload failed');
                              }
                          })
                          .catch(error => {
                              console.error(error);
                              toastr.error(error || 'Something went wrong while uploading.');

                          });
                  }
              });
          }
      });
      document.addEventListener('DOMContentLoaded', function() {
          const deleteImageBtn = document.getElementById('deleteImageBtn');
          const preview = document.getElementById('uploadedAvatar');
          let isImageDelete = false;

          if (deleteImageBtn) {
              deleteImageBtn.addEventListener('click', function() {
                  if (confirm('Are you sure you want to delete your profile image?')) {
                      // Make AJAX request to delete the image
                      fetch("{{ route('profile.deleteImage') }}", {
                              method: 'DELETE',
                              headers: {
                                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                              },
                          })
                          .then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  preview.src =
                                      "{{ asset('assets/img/avatars/1.png') }}";
                                  toastr.success("Profile image deleted successfully!");
                              } else {
                                  toastr.error('Something went wrong while deleting the image.');
                              }
                          })
                          .catch(error => {
                              console.error(error);
                              toastr.error(error || 'An error occurred.');
                          });
                  }
              });
          }
      });
  </script>
