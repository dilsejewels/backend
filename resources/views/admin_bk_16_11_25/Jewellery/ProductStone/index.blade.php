@extends('admin.layouts.master')

@section('main_section')

<style>
    table.dataTable td.dt-control:before {
        background: #317cb1;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4 class="mb-0">Product Stone Types</h4>
      <button class="btn btn-primary" id="addBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="stoneTable" style="width:100%;">
        <thead class="bg-light">
          <tr>
            <th> # </th>
            <th>ID</th>
            <th>Name</th>
            <th>Alias</th>
            <th>Description</th>
            <th>Image</th>
            <th>Status</th>
            <th>Sort Order</th>
            <th>Front Display</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="stoneModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="stoneForm" class="modal-content" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="pst_id" id="pst_id">
      <div class="modal-header">
        <h5 class="modal-title">Product Stone Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Name -->
        <div class="mb-3">
          <label for="pst_name" class="form-label">Name <span class="text-danger">*</span></label>
          <input type="text" name="pst_name" id="pst_name" class="form-control">
          <small class="text-danger error-pst_name"></small>
        </div>

        <!-- Alias -->
        <div class="mb-3">
          <label for="pst_alias" class="form-label">Alias</label>
          <input type="text" name="pst_alias" id="pst_alias" class="form-control">
          <small class="text-danger error-pst_alias"></small>
        </div>

        <!-- Description -->
        <div class="mb-3">
          <label for="pst_description" class="form-label">Description</label>
          <textarea name="pst_description" id="pst_description" class="form-control" rows="3"></textarea>
        </div>

        <!-- Image Upload -->
        <div class="mb-3">
          <label for="pst_image" class="form-label">Image</label>
          <input type="file" name="pst_image" id="pst_image" class="form-control" accept="image/*">
          <small class="text-danger error-pst_image"></small>
          <!-- Old Image Preview -->
          <div id="imgPreviewBox" class="mt-2" style="display: none;">
            <p class="mb-1">Current Image:</p>
            <img id="imgPreview" src="" alt="Current" class="img-fluid rounded" width="100">
          </div>
        </div>

        <!-- Status -->
        <div class="mb-3">
          <label for="pst_status" class="form-label">Status</label>
          <select name="pst_status" id="pst_status" class="form-select">
            <option value="">Select</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <small class="text-danger error-pst_status"></small>
        </div>

        <!-- Sort Order -->
        <div class="mb-3">
          <label for="pst_sort_order" class="form-label">Sort Order</label>
          <input type="number" name="pst_sort_order" id="pst_sort_order" class="form-control">
          <small class="text-danger error-pst_sort_order"></small>
        </div>

        <!-- Display in Front -->
        <div class="mb-3">
          <label for="pst_display_in_front" class="form-label">Display in Front</label>
          <select name="pst_display_in_front" id="pst_display_in_front" class="form-select">
            <option value="">Select</option>
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
          <small class="text-danger error-pst_display_in_front"></small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>

  
$(document).ready(function() {
  const stoneModal = new bootstrap.Modal(document.getElementById('stoneModal'));

  // Initialize DataTable
  let table = $('#stoneTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route("product-stone.index") }}',
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'pst_id',           name: 'pst_id' },
      { data: 'pst_name',         name: 'pst_name' },
      { data: 'pst_alias',        name: 'pst_alias' },
      { data: 'pst_description',  name: 'pst_description', orderable: false, searchable: false },
      { data: 'pst_image',        name: 'pst_image', orderable: false, searchable: false },
      { data: 'pst_status',       name: 'pst_status', orderable: false, searchable: false },
      { data: 'pst_sort_order',   name: 'pst_sort_order' },
      { data: 'pst_display_in_front', name: 'pst_display_in_front', orderable: false, searchable: false },
      { data: 'action',           name: 'action', orderable: false, searchable: false },
    ],
    order: [[1, 'desc']]
  });

  // Show Create Modal
  $('#addBtn').click(function() {
    $('#stoneForm')[0].reset();
    $('#pst_id').val('');
    $('.text-danger').text('');
    $('#imgPreviewBox').hide();
    stoneModal.show();
  });

  // Submit Create / Update
  $('#stoneForm').on('submit', function(e) {
    e.preventDefault();
    $('.text-danger').text('');

    let id = $('#pst_id').val();
    let url = id
      ? `/admin/product-stone/update/${id}`
      : `{{ route('product-stone.store') }}`;
    let formData = new FormData(this);

    $.ajax({
      url: url,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        toastr.success(response.message);
        table.ajax.reload();
        stoneModal.hide();
      },
      error: function(xhr) {
        if (xhr.status === 422) {
          let errors = xhr.responseJSON.errors;
          $.each(errors, function(key, value) {
            $('.error-' + key).text(value[0]);
          });
        } else {
          toastr.error('Something went wrong.');
        }
      }
    });
  });

  // Edit Button
  $(document).on('click', '.editBtn', function() {
    let id = $(this).data('id');
    $.get(`/admin/product-stone/edit/${id}`, function(data) {
      $('#pst_id').val(data.pst_id);
      $('#pst_name').val(data.pst_name);
      $('#pst_alias').val(data.pst_alias);
      $('#pst_description').val(data.pst_description);
      $('#pst_sort_order').val(data.pst_sort_order);
      $('#pst_status').val(data.pst_status);
      $('#pst_display_in_front').val(data.pst_display_in_front);
      $('.text-danger').text('');

      // Show existing image preview if available
      if (data.pst_image) {
        let imgUrl = `{{ asset('storage') }}/${data.pst_image}`;
        $('#imgPreviewBox').show();
        $('#imgPreview').attr('src', imgUrl);
      } else {
        $('#imgPreviewBox').hide();
      }

      stoneModal.show();
    });
  });

  // Delete Button
  $(document).on('click', '.deleteBtn', function() {
    if (!confirm('Are you sure you want to delete this?')) return;
    let id = $(this).data('id');
    $.ajax({
      url: `/admin/product-stone/delete/${id}`,
      type: 'DELETE',
      data: { _token: '{{ csrf_token() }}' },
      success: function(res) {
        toastr.success(res.message);
        table.ajax.reload();
      },
      error: function() {
        toastr.error('Something went wrong.');
      }
    });
  });
});
</script>
@endsection
