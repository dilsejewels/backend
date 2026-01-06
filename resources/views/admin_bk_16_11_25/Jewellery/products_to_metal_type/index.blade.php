@extends('admin.layouts.master')

@section('main_section')
<div class="container-xxl flex-grow-1 container-p-y">
  {{-- Success Message Container --}}
  <div id="successMessage"></div>

  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4 class="mb-3">Products To Metal Type</h4>
      <button class="btn btn-primary btn-sm" id="addBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      {{-- DataTable --}}
      <table class="table table-hover" id="relationTable">
        <thead class="bg-light">
          <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Metal Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

{{-- Bootstrap Modal for Add/Edit --}}
<div class="modal fade" id="relationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="relationForm" class="modal-content">
      @csrf
      {{-- Hidden field for primary key --}}
      <input type="hidden" id="sptmt_id" name="sptmt_id">

      <div class="modal-header">
        <h5 class="modal-title">Relation Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {{-- Product Dropdown --}}
        <div class="mb-3">
          <label for="sptmt_products_id" class="form-label">Product</label>
          <select id="sptmt_products_id" name="sptmt_products_id" class="form-control">
            <option value="">Select Product</option>
            @foreach($products as $product)
              <option value="{{ $product->id }}">{{ $product->products_name }}</option>
            @endforeach
          </select>
          <div id="error_sptmt_products_id" class="text-danger"></div>
        </div>

        {{-- Metal Type Dropdown --}}
        <div class="mb-3">
          <label for="sptmt_metal_type_id" class="form-label">Metal Type</label>
          <select id="sptmt_metal_type_id" name="sptmt_metal_type_id" class="form-control">
            <option value="">Select Metal Type</option>
            @foreach($metalTypes as $metal)
              <option value="{{ $metal->id }}">{{ $metal->dmt_name }}</option>
            @endforeach
          </select>
          <div id="error_sptmt_metal_type_id" class="text-danger"></div>
        </div>

        {{-- Generic form error (optional) --}}
        <div id="formError" class="text-danger mt-2"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- Page-specific JavaScript --}}
<script>
$(function(){
  // Bootstrap Modal instance
  const modal = new bootstrap.Modal(document.getElementById('relationModal'));

  // Initialize DataTable with server-side processing
  let table = $('#relationTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route("ProductsToMetalType.fetch") }}',
    columns: [
      { data: 'sptmt_id', name: 'sptmt_id' },
      {
        // अब server-side alias जिसका नाम fetch() में addColumn में दिया गया था
        data: 'product_name',
        name: 'product_name',
        defaultContent: 'N/A'
      },
      {
        data: 'metal_type_name',
        name: 'metal_type_name',
        defaultContent: 'N/A'
      },
      {
        data: 'action',
        name: 'action',
        orderable: false,
        searchable: false
      },
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    language: {
      paginate: {
        previous: '←',
        next: '→'
      }
    }
  });

  // Clear validation errors/messages
  function clearValidation(){
    $('#relationForm .form-control').removeClass('is-invalid');
    $('[id^="error_"]').text('');
    $('#formError').text('');
  }

  // "Add New" button
  $('#addBtn').click(function(){
    clearValidation();
    $('#relationForm')[0].reset();
    $('#sptmt_id').val('');
    $('#saveBtn').text('Save');
    $('#successMessage').html('');
    modal.show();
  });

  // Submit form for Create/Update
  $('#relationForm').submit(function(e){
    e.preventDefault();
    clearValidation();

    const id = $('#sptmt_id').val();
    const url = id
      ? `{{ url('admin/ProductsToMetalType/update') }}/${id}`
      : `{{ route('ProductsToMetalType.store') }}`;

    $.ajax({
      url: url,
      type: 'POST',
      data: $(this).serialize() + (id ? '&_method=PUT' : ''),
      success: res => {
        modal.hide();
        table.ajax.reload(); // DataTable री-लोड
        toastr.success(res.success);
      },
      error: xhr => {
        if(xhr.status === 422){
          // Validation errors
          const errors = xhr.responseJSON.errors;
          Object.keys(errors).forEach(field => {
            $(`[name="${field}"]`).addClass('is-invalid');
            $(`#error_${field}`).text(errors[field][0]);
          });
        } else {
          $('#formError').text('Something went wrong.');
          toastr.error('Error saving data');
        }
      }
    });
  });

  // Edit button click
  $(document).on('click', '.editBtn', function(){
    clearValidation();
    const id = $(this).data('id');

    $.get(`{{ url('admin/ProductsToMetalType/show') }}/${id}`, data => {
      $('#sptmt_id').val(data.sptmt_id);
      $('#sptmt_products_id').val(data.sptmt_products_id);
      $('#sptmt_metal_type_id').val(data.sptmt_metal_type_id);
      $('#saveBtn').text('Update');
      $('#successMessage').html('');
      modal.show();
    });
  });

  // Delete button click
  $(document).on('click', '.deleteBtn', function(){
    if (!confirm('Are you sure you want to delete this record?')) return;

    const id = $(this).data('id');
    $.ajax({
      url: `{{ url('admin/ProductsToMetalType/delete') }}/${id}`,
      type: 'DELETE',
      data: { _token: '{{ csrf_token() }}' },
      success: res => {
        table.ajax.reload();
        toastr.success(res.success);
      },
      error: () => toastr.error('Failed to delete')
    });
  });

});
</script>
@endsection
