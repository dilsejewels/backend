@extends('admin.layouts.master')

@section('main_section')
<style>
    table.dataTable td.dt-control:before {
        background: #317cb1;
    }
</style>
<div class="container-xxl container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4>Product Stone Type Assignment</h4>
      <button class="btn btn-primary btn-sm" id="addBtn">Add New</button>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover" id="dataTable">
        <thead class="bg-light">
          <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Product</th>
            <th>Stone Type</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="formModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="dataForm" class="modal-content">
      @csrf
      <input type="hidden" id="edit_mode" value="0">
      <input type="hidden" id="original_product_id" name="original_product_id">
      <input type="hidden" id="original_stone_type_id" name="original_stone_type_id">

      <div class="modal-header">
        <h5 class="modal-title">Assign Stone Type</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Product</label>
          <select class="form-select" id="sptst_products_id" name="sptst_products_id">
            <option value="">Select Product</option>
            @foreach($products as $product)
              <option value="{{ $product->products_id }}">{{ $product->products_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-sptst_products_id"></small>
        </div>

        <div class="mb-3">
          <label class="form-label">Stone Type</label>
          <select class="form-select" id="sptst_stone_type_id" name="sptst_stone_type_id">
            <option value="">Select Stone Type</option>
            @foreach($stoneTypes as $stone)
              <option value="{{ $stone->pst_id }}">{{ $stone->pst_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-sptst_stone_type_id"></small>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Save</button>
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- JS --}}
<script>
$(document).ready(function() {
    // Initialize Toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };

   const stoneTypeTable = $('#dataTable').DataTable({
        ajax: '{{ route("product-to-stone.fetch") }}',
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'product_name' },
            { data: 'stone_type_name' }
        ]
    });

    // Toggle expand row for Stone Type Mappings
    $('#dataTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = stoneTypeTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editStoneTypeBtn" 
                            data-product="${data.sptst_products_id}" 
                            data-stone="${data.sptst_stone_type_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteStoneTypeBtn" 
                            data-product="${data.sptst_products_id}" 
                            data-stone="${data.sptst_stone_type_id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    const stoneTypeModal = new bootstrap.Modal(document.getElementById('formModal'));

    function clearStoneTypeForm() {
        $('#dataForm')[0].reset();
        $('.form-select').removeClass('is-invalid');
        $('small.text-danger').text('');
    }

    $('#addBtn').click(() => {
        clearStoneTypeForm();
        $('#edit_mode').val(0);
        stoneTypeModal.show();
    });

    $('#dataForm').submit(function(e) {
        e.preventDefault();
        const editMode = $('#edit_mode').val();
        const url = editMode == 1
            ? `/admin/product-to-stone-type/update/${$('#original_product_id').val()}/${$('#original_stone_type_id').val()}`
            : `{{ route('product-to-stone.store') }}`;

        $.post(url, $(this).serialize())
            .done(res => {
                stoneTypeModal.hide();
                stoneTypeTable.ajax.reload();
                toastr.success(res.message);
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

    $(document).on('click', '.editStoneTypeBtn', function () {
        clearStoneTypeForm();
        const pid = $(this).data('product');
        const sid = $(this).data('stone');

        $.get(`/admin/product-to-stone-type/show/${pid}/${sid}`, data => {
            $('#edit_mode').val(1);
            $('#original_product_id').val(data.sptst_products_id);
            $('#original_stone_type_id').val(data.sptst_stone_type_id);
            $('#sptst_products_id').val(data.sptst_products_id);
            $('#sptst_stone_type_id').val(data.sptst_stone_type_id);
            stoneTypeModal.show();
        });
    });

    $(document).on('click', '.deleteStoneTypeBtn', function () {
        if (!confirm('Are you sure?')) return;

        $.post(`{{ route('product-to-stone.destroy') }}`, {
            _token: '{{ csrf_token() }}',
            sptst_products_id: $(this).data('product'),
            sptst_stone_type_id: $(this).data('stone')
        }, res => {
            stoneTypeTable.ajax.reload();
            toastr.success(res.message);
        }).fail(() => toastr.error('Delete failed.'));
    });

});
</script>
@endsection
