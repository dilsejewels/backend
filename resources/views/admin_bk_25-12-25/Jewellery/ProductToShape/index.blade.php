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
      <h4 class="mb-0">Product to Shape Mapping</h4>
      <button class="btn btn-primary" id="addMappingBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="mappingTable" style="width:100%">
        <thead class="bg-light">
          <tr>
            <th>Action</th> <!-- For toggle control -->
            <th>ID</th>
            <th>Product</th>
            <th>Shape</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="mappingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="mappingForm" class="modal-content">
      @csrf
      <input type="hidden" name="pts_id" id="pts_id">
      <div class="modal-header">
        <h5 class="modal-title">Mapping Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="products_id" class="form-label">Product</label>
          <select name="products_id" id="products_id" class="form-select">
            <option value="">-- Select Product --</option>
            @foreach ($products as $product)
              <option value="{{ $product->products_id }}">{{ $product->products_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-products_id"></small>
        </div>
        <div class="mb-3">
          <label for="shape_id" class="form-label">Shape</label>
          <select name="shape_id" id="shape_id" class="form-select">
            <option value="">-- Select Shape --</option>
            @foreach ($shapes as $shape)
              <option value="{{ $shape->id }}">{{ $shape->name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-shape_id"></small>
        </div>
        <div id="formError" class="text-danger mt-2"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Required scripts -->
<script>
  $(document).ready(function() {
    toastr.options = {
      closeButton: true,
      progressBar: true,
      positionClass: 'toast-top-right',
      timeOut: 3000
    };

    // Initialize DataTable with toggle control column
    const shapeTable = $('#mappingTable').DataTable({
      processing: true,
      serverSide: false, // Change to true if you want server-side pagination
      ajax: {
        url: '{{ route("products-to-shape.fetch") }}',
        dataSrc: ''
      },
      columns: [
        {
          className: 'dt-control',
          orderable: false,
          data: null,
          defaultContent: '',
          width: "20px"
        },
        { data: 'pts_id', name: 'pts_id' },
        { data: 'product_name', name: 'product_name' },
        { data: 'shape_name', name: 'shape_name' }
      ],
      order: [[1, 'asc']]
    });

    // Add event listener for opening and closing details
    $('#mappingTable tbody').on('click', 'td.dt-control', function () {
      const tr = $(this).closest('tr');
      const row = shapeTable.row(tr);

      if (row.child.isShown()) {
        // This row is already open - close it
        row.child.hide();
        tr.removeClass('shown');
      } else {
        // Open this row
        const data = row.data();

        // Detail content with edit/delete buttons
        const detailHtml = `
          <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-info editBtn" data-id="${data.pts_id}">
              <i class="fa fa-edit"></i> Edit
            </button>
            <button type="button" class="btn btn-sm btn-danger deleteBtn" data-id="${data.pts_id}">
              <i class="fa fa-trash"></i> Delete
            </button>
          </div>
        `;

        row.child(detailHtml).show();
        tr.addClass('shown');
      }
    });

    const shapeModal = new bootstrap.Modal(document.getElementById('mappingModal'));

    function clearForm() {
      $('#mappingForm')[0].reset();
      $('#pts_id').val('');
      $('.text-danger').text('');
      $('.form-select').removeClass('is-invalid');
      $('#formError').text('');
    }

    // Add New Button click
    $('#addMappingBtn').click(function() {
      clearForm();
      $('#saveBtn').text('Save');
      shapeModal.show();
    });

    // Form submit - Create or Update
    $('#mappingForm').submit(function(e) {
      e.preventDefault();
      $('.text-danger').text('');
      $('.form-select').removeClass('is-invalid');
      $('#formError').text('');

      const id = $('#pts_id').val();
      const url = id 
        ? `{{ url('admin/products-to-shape/update') }}/${id}` 
        : `{{ route('products-to-shape.store') }}`;
      const method = id ? 'PUT' : 'POST';

      $.ajax({
        url,
        type: 'POST',
        data: $(this).serialize() + (id ? '&_method=PUT' : ''),
        success: function(res) {
          toastr.success(res.message);
          shapeModal.hide();
          shapeTable.ajax.reload(null, false); // Reload without resetting pagination
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            $.each(errors, function(field, messages) {
              $(`#${field}`).addClass('is-invalid');
              $(`.error-${field}`).text(messages[0]);
            });
          } else {
            $('#formError').text('Something went wrong.');
            toastr.error('Error saving data');
          }
        }
      });
    });

    // Edit Button click
    $('#mappingTable tbody').on('click', '.editBtn', function() {
      clearForm();
      const id = $(this).data('id');

      $.get(`{{ url('admin/products-to-shape/show') }}/${id}`, function(data) {
        $('#pts_id').val(data.pts_id);
        $('#products_id').val(data.products_id);
        $('#shape_id').val(data.shape_id);
        $('#saveBtn').text('Update');
        shapeModal.show();
      }).fail(function() {
        toastr.error('Failed to fetch data.');
      });
    });

    // Delete Button click
    $('#mappingTable tbody').on('click', '.deleteBtn', function() {
      if (!confirm('Are you sure you want to delete this record?')) return;

      const id = $(this).data('id');

      $.ajax({
        url: `{{ url('admin/products-to-shape/delete') }}/${id}`,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
          toastr.success(res.message);
          shapeTable.ajax.reload(null, false);
        },
        error: function() {
          toastr.error('Failed to delete');
        }
      });
    });

  });
</script>
@endsection
