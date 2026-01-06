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
      <h4 class="mb-3">Product-style category mapping</h4>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#styleMappingModal" id="addMappingBtn">
        New Add
      </button>
    </div>
    <div class="table-responsive text-nowrap card-body">
      <table class="table table-hover" id="styleMappingTable">
        <thead>
          <tr class="bg-light">
            <th>Action</th>
            <th>Product Name</th>
            <th>Style Category</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="styleMappingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="styleMappingForm">
      @csrf
      <input type="hidden" name="sptsc_id" id="sptsc_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Mapping Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="sptsc_products_id" class="form-label">Select Product</label>
            <select name="sptsc_products_id" id="sptsc_products_id" class="form-select">
              <option value="">-- Select Product --</option>
              @foreach($products as $product)
                <option value="{{ $product->products_id }}">{{ $product->products_name }}</option>
              @endforeach
            </select>
            <div class="text-danger" id="error_sptsc_products_id"></div>
          </div>
          <div class="mb-3">
            <label for="sptsc_style_category_id" class="form-label">Select Style Category</label>
            <select name="sptsc_style_category_id" id="sptsc_style_category_id" class="form-select">
              <option value="">-- Select Style Category --</option>
              @foreach($styleCategories as $category)
                <option value="{{ $category->psc_id }}">{{ $category->psc_name }}</option>
              @endforeach
            </select>
            <div class="text-danger" id="error_sptsc_style_category_id"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header p-2 text-center">
        <h5 class="modal-title w-100">Are you sure you want to delete it?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-footer p-2 justify-content-center">
        <button type="button" class="btn btn-danger btn-sm me-2" id="confirmDeleteBtn">Yes</button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>

<script>

  $(document).ready(function() {
    // Initialize Toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };
const styleCatTable = $('#styleMappingTable').DataTable({
        ajax: '{{ route("ptsc.index") }}',
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'product_name' },
            { data: 'style_category_name' }
        ]
    });

    // Toggle expand row for Style Category Mappings
    $('#styleMappingTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = styleCatTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editStyleCatBtn" data-id="${data.sptsc_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteStyleCatBtn" data-id="${data.sptsc_id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    const styleCatModal = new bootstrap.Modal(document.getElementById('styleMappingModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));

    $('#addMappingBtn').click(function () {
        $('#styleMappingForm')[0].reset();
        $('#sptsc_id').val('');
        $('.text-danger').text('');
        $('#saveBtn').text('Save');
        styleCatModal.show();
    });

    $('#styleMappingForm').submit(function (e) {
        e.preventDefault();
        let id = $('#sptsc_id').val();
        let method = id ? 'PUT' : 'POST';
        let url = id ? `/admin/product-to-style-category/${id}` : `{{ route('ptsc.store') }}`;

        $('.text-danger').text('');

        $.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize() + `&_method=${method}`,
            success: function (res) {
                styleCatModal.hide();
                toastr.success(res.message);
                styleCatTable.ajax.reload();
            },
            error: function (xhr) {
                let errors = xhr.responseJSON?.errors || {};
                if (errors.sptsc_products_id) {
                    $('#error_sptsc_products_id').text(errors.sptsc_products_id[0]);
                }
                if (errors.sptsc_style_category_id) {
                    $('#error_sptsc_style_category_id').text(errors.sptsc_style_category_id[0]);
                }
                if (errors.duplicate) {
                    toastr.error(errors.duplicate[0]);
                }
            }
        });
    });

    $(document).on('click', '.editStyleCatBtn', function () {
        let id = $(this).data('id');
        $.get(`/admin/product-to-style-category/${id}/edit`, function (data) {
            $('#sptsc_id').val(data.sptsc_id);
            $('#sptsc_products_id').val(data.sptsc_products_id);
            $('#sptsc_style_category_id').val(data.sptsc_style_category_id);
            $('#saveBtn').text('Update');
            styleCatModal.show();
        });
    });

    $(document).on('click', '.deleteStyleCatBtn', function () {
        const id = $(this).data('id');
        $('#deleteId').val(id);
        deleteModal.show();
    });

    $('#confirmDeleteBtn').click(function () {
        const id = $('#deleteId').val();
        if (!id) return;

        $.ajax({
            url: `/admin/product-to-style-category/${id}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: res => {
                deleteModal.hide();
                styleCatTable.ajax.reload();
                toastr.success(res.message);
            },
            error: () => {
                deleteModal.hide();
                toastr.error("Failed to delete the mapping.");
            }
        });
    });

});
</script>
@endsection
