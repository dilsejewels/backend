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
      <h4 class="mb-3">Product Style Group Management</h4>
      <button class="btn btn-primary btn-sm" id="addBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="dataTable">
        <thead class="bg-light">
          <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Product Name</th>
            <th>Style Category</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="dataForm" class="modal-content">
      @csrf
      <input type="hidden" id="edit_id">
      <div class="modal-header">
        <h5 class="modal-title">Add / Edit Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Product <span class="text-danger">*</span></label>
          <select id="sptsg_products_id" name="sptsg_products_id" class="form-select">
            <option value="">Select Product</option>
            @foreach($products as $product)
              <option value="{{ $product->products_id }}">{{ $product->products_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-sptsg_products_id"></small>
        </div>

        <div class="mb-3">
          <label class="form-label">Style Category <span class="text-danger">*</span></label>
          <select id="sptsg_style_category_id" name="sptsg_style_category_id" class="form-select">
            <option value="">Select Style Category</option>
            @foreach($styleCategories as $style)
              <option value="{{ $style->psc_id }}">{{ $style->psc_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-sptsg_style_category_id"></small>
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
    // Initialize Toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };
    const styleGroupTable = $('#dataTable').DataTable({
        ajax: '{{ route('product-to-style-group.fetch') }}',
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'products_name' },
            { data: 'psc_name' }
        ]
    });

    // Toggle expand row for Style Group Mappings
    $('#dataTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = styleGroupTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editStyleGroupBtn" data-id="${data.sptsg_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteStyleGroupBtn" data-id="${data.sptsg_id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    const styleGroupModal = new bootstrap.Modal(document.getElementById('formModal'));

    function clearStyleGroupForm() {
        $('#dataForm')[0].reset();
        $('.form-control').removeClass('is-invalid');
        $('small.text-danger').text('');
    }

    $('#addBtn').click(() => {
        clearStyleGroupForm();
        $('#edit_id').val('');
        styleGroupModal.show();
    });

    $('#dataForm').submit(function(e){
        e.preventDefault();
        const id = $('#edit_id').val();
        const url = id ? `/admin/product-to-style-group/update/${id}` : `{{ route('product-to-style-group.store') }}`;

        $.post(url, $(this).serialize())
            .done(res => {
                styleGroupModal.hide();
                styleGroupTable.ajax.reload();
                toastr.success(res.success);
            })
            .fail(xhr => {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        $(`#${key}`).addClass('is-invalid');
                        $(`.error-${key}`).text(errors[key][0]);
                    });
                } else {
                    toastr.error('Something went wrong.');
                }
            });
    });

    $(document).on('click', '.editStyleGroupBtn', function(){
        clearStyleGroupForm();
        const id = $(this).data('id');
        $.get(`/admin/product-to-style-group/show/${id}`, data => {
            $('#edit_id').val(id);
            $('#sptsg_products_id').val(data.sptsg_products_id);
            $('#sptsg_style_category_id').val(data.sptsg_style_category_id);
            styleGroupModal.show();
        });
    });

    $(document).on('click', '.deleteStyleGroupBtn', function(){
        if (!confirm('Are you sure to delete this record?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: `/admin/product-to-style-group/delete/${id}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: res => {
                styleGroupTable.ajax.reload();
                toastr.success(res.success);
            },
            error: () => toastr.error('Delete failed.')
        });
    });
});
</script>
@endsection
