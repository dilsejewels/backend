@extends('admin.layouts.master')

@section('main_section')
<style>
  .user-details-table th {
    background: #f8f9fa;
    width: 30%;
  }

  .address-card {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
  }

  .address-card-header {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #dee2e6;
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4 class="mb-3">User Management</h4>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="userTable">
        <thead class="bg-light">
          <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Title</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-4 text-center mb-3">
            <img id="userImage" src="" alt="User Image" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
          </div>
          <div class="col-md-8">
            <table class="table table-bordered user-details-table">
              <tr>
                <th>ID</th>
                <td id="userId"></td>
              </tr>
              <tr>
                <th>Name</th>
                <td id="userName"></td>
              </tr>
              <tr>
                <th>Email</th>
                <td id="userEmail"></td>
              </tr>
              <tr>
                <th>User Type</th>
                <td id="userType"></td>
              </tr>
              <tr>
                <th>Title</th>
                <td id="userTitle"></td>
              </tr>
              <tr>
                <th>Date of Birth</th>
                <td id="userDob"></td>
              </tr>
              <tr>
                <th>Anniversary Date</th>
                <td id="userAnniversary"></td>
              </tr>
              <tr>
                <th>Email Verified</th>
                <td id="userEmailVerified"></td>
              </tr>
              <tr>
                <th>Created At</th>
                <td id="userCreatedAt"></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Addresses Section -->
        <div class="mt-4">
          <h6 class="border-bottom pb-2">Addresses</h6>
          <div id="addressesContainer">
            <!-- Addresses will be loaded here -->
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function () {
  const userModal = new bootstrap.Modal('#userModal');

  const table = $('#userTable').DataTable({
    processing: true,
    serverSide: false,
    ajax: "{{ route('users.fetch') }}",
    columns: [
      { data: 'id' },
      { 
        data: 'image_url',
        orderable: false,
        searchable: false
      },
      { data: 'name' },
      { data: 'email' },
      { data: 'user_type' },
      { data: 'title' },
      {
        data: 'action',
        orderable: false,
        searchable: false
      }
    ],
    order: [[0, 'desc']]
  });

  // Format address from array
  function formatAddress(address) {
    if (!address) return 'N/A';
    
    if (typeof address === 'string') {
      return address;
    }
    
    if (Array.isArray(address)) {
      return address.join(', ');
    }
    
    if (typeof address === 'object') {
      return Object.values(address).filter(val => val).join(', ');
    }
    
    return 'N/A';
  }

  // View user details
  $(document).on('click', '.view-user', function () {
    const userId = $(this).data('id');
    
    $.get(`{{ url('admin/jewellery/users') }}/${userId}`, function(response) {
      if (response.success) {
        const user = response.user;
        
        // Set user image
        if (user.image_url) {
          $('#userImage').attr('src', user.image_url);
        } else {
          $('#userImage').attr('src', '{{ asset("assets/img/default-user.png") }}');
        }
        
        // Set user details
        $('#userId').text(user.id);
        $('#userName').text(user.name || 'N/A');
        $('#userEmail').text(user.email);
        $('#userType').text(user.user_type ? user.user_type.charAt(0).toUpperCase() + user.user_type.slice(1) : 'N/A');
        $('#userTitle').text(user.title || 'N/A');
        $('#userDob').text(user.dob ? new Date(user.dob).toLocaleDateString() : 'N/A');
        $('#userAnniversary').text(user.anniversary_date ? new Date(user.anniversary_date).toLocaleDateString() : 'N/A');
        $('#userEmailVerified').html(user.email_verified_at ? 
          '<span class="badge bg-success">Verified</span>' : 
          '<span class="badge bg-warning">Not Verified</span>');
        $('#userCreatedAt').text(new Date(user.created_at).toLocaleString());
        
        // Set addresses
        let addressesHtml = '';
        if (response.addresses && response.addresses.length > 0) {
          response.addresses.forEach(function(address, index) {
            addressesHtml += `
              <div class="address-card">
                <div class="address-card-header">
                  <strong>Address ${index + 1}</strong>
                </div>
                <div class="card-body">
                  <table class="table table-sm">
                    <tr>
                      <th>First Name:</th>
                      <td>${address.first_name || 'N/A'}</td>
                    </tr>
                    <tr>
                      <th>Country:</th>
                      <td>${address.country || 'N/A'}</td>
                    </tr>
                    <tr>
                      <th>Phone Number:</th>
                      <td>${address.phone_number || 'N/A'}</td>
                    </tr>
                    <tr>
                      <th>Get Offers:</th>
                      <td>${address.is_get_offer ? 'Yes' : 'No'}</td>
                    </tr>
                    <tr>
                      <th>Address Details:</th>
                      <td>${formatAddress(address.address)}</td>
                    </tr>
                  </table>
                </div>
              </div>
            `;
          });
        } else {
          addressesHtml = '<p class="text-muted">No addresses found for this user.</p>';
        }
        
        $('#addressesContainer').html(addressesHtml);
        userModal.show();
      }
    }).fail(function() {
      toastr.error('Error loading user details');
    });
  });
});
</script>
@endsection