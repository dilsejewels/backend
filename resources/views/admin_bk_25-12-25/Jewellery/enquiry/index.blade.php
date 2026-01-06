@extends('admin.layouts.master')

@section('main_section')
<style>
  td.dt-control {
    text-align: center;
    vertical-align: middle;
  }

  .toggle-icon {
    visibility: hidden;
  }

  table.dataTable td.dt-control:before {
    background: #337ab7;
  }
  
  .comments-box {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    margin-top: 10px;
  }
  
  .enquiry-details {
    display: none;
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  
  <!-- Index View -->
  <div class="card" id="enquiryList">
    <div class="card-header d-flex justify-content-between">
      <h4 class="mb-3">Enquiries Management</h4>
    </div>
    <div class="card-body table-responsive text-nowrap">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <table class="table table-hover" id="enquiryTable">
        <thead class="bg-light">
          <tr>
            <th></th>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Product ID</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($enquiries as $enquiry)
            <tr>
              <td class="dt-control">
                <div class="toggle-icon"><i class="fa fa-plus"></i></div>
              </td>
              <td>{{ $enquiry->id }}</td>
              <td>{{ $enquiry->name }}</td>
              <td>{{ $enquiry->email }}</td>
              <td>{{ $enquiry->phone }}</td>
              <td>{{ $enquiry->product ?? 'N/A' }}</td>
              <td>{{ $enquiry->created_at ? $enquiry->created_at->format('d M Y') : 'N/A' }}</td>


              <td>
                <div class="btn-group">
                  <button class="btn btn-info btn-sm view-details-btn" 
                          data-enquiry-id="{{ $enquiry->id }}"
                          data-enquiry-name="{{ $enquiry->name }}"
                          data-enquiry-email="{{ $enquiry->email }}"
                          data-enquiry-phone="{{ $enquiry->phone }}"
                          data-enquiry-product="{{ $enquiry->product }}"
                          data-enquiry-comments="{{ $enquiry->comments }}"
                          data-enquiry-created="{{ $enquiry->created_at->format('d M Y, h:i A') }}">
                    <i class="fa fa-eye"></i> View
                  </button>
                  <form action="{{ route('enquiries.destroy', $enquiry->id) }}" 
                        method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Are you sure?')">
                      <i class="fa fa-trash"></i> Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Show View (Hidden by default) -->
  <div class="card enquiry-details" id="enquiryDetails">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4>Enquiry Details</h4>
      <button class="btn btn-secondary" id="backToListBtn">
        <i class="fa fa-arrow-left me-1"></i> Back to List
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <table class="table table-bordered">
            <tr>
              <th>ID:</th>
              <td id="detailId"></td>
            </tr>
            <tr>
              <th>Name:</th>
              <td id="detailName"></td>
            </tr>
            <tr>
              <th>Email:</th>
              <td id="detailEmail"></td>
            </tr>
            <tr>
              <th>Phone:</th>
              <td id="detailPhone"></td>
            </tr>
            <tr>
              <th>Product ID:</th>
              <td id="detailProduct"></td>
            </tr>
            <tr>
              <th>Created At:</th>
              <td id="detailCreated"></td>
            </tr>
          </table>
        </div>
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5>Comments</h5>
            </div>
            <div class="card-body">
              <div class="comments-box" id="detailComments"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function () {
  // Initialize DataTable
  const table = $('#enquiryTable').DataTable({
    order: [[1, 'asc']],
    columnDefs: [
      { orderable: false, targets: [0, 7] } // Disable sorting for control and actions columns
    ]
  });

  // Expand row for comments
  $('#enquiryTable tbody').on('click', 'td.dt-control', function () {
    const tr = $(this).closest('tr');
    const row = table.row(tr);
    const icon = $(this).find('i');

    if (row.child.isShown()) {
      row.child.hide();
      tr.removeClass('shown');
      icon.removeClass('fa-minus').addClass('fa-plus');
    } else {
      // Get comments from data attribute
      const comments = tr.find('.view-details-btn').data('enquiry-comments') || 'No comments provided';
      
      row.child(`
        <div class="p-3">
          <h6>Comments:</h6>
          <div class="comments-box">
            ${comments}
          </div>
        </div>
      `).show();
      tr.addClass('shown');
      icon.removeClass('fa-plus').addClass('fa-minus');
    }
  });

  // View details button click
  $(document).on('click', '.view-details-btn', function () {
    const enquiryId = $(this).data('enquiry-id');
    const name = $(this).data('enquiry-name');
    const email = $(this).data('enquiry-email');
    const phone = $(this).data('enquiry-phone');
    const product = $(this).data('enquiry-product');
    const comments = $(this).data('enquiry-comments');
    const created = $(this).data('enquiry-created');

    // Populate details
    $('#detailId').text(enquiryId);
    $('#detailName').text(name);
    $('#detailEmail').text(email);
    $('#detailPhone').text(phone);
    $('#detailProduct').text(product ? product : 'N/A');
    $('#detailCreated').text(created);
    $('#detailComments').text(comments ? comments : 'No comments provided');

    // Update delete form action
    $('#deleteForm').attr('action', `{{ url('enquiries') }}/${enquiryId}`);

    // Switch views
    $('#enquiryList').hide();
    $('#enquiryDetails').show();
  });

  // Back to list button
  $('#backToListBtn').click(function () {
    $('#enquiryDetails').hide();
    $('#enquiryList').show();
  });

  // Delete button click handler
  $(document).on('click', '.btn-danger', function(e) {
    if ($(this).closest('form').length > 0) {
      if (!confirm('Are you sure you want to delete this enquiry?')) {
        e.preventDefault();
      }
    }
  });
});
</script>
@endsection