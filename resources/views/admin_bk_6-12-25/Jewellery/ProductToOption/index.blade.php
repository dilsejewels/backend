@extends('admin.layouts.master')

@section('main_section')
<style>
    table.dataTable td.dt-control:before {
        background: #317cb1;
    }
</style>
<div class="container mt-4">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4>Product Option Mapping</h4>
      <button class="btn btn-primary btn-sm" id="addBtn">Add New</button>
    </div>
    <div class="card-body">
      <table class="table table-hover" id="dataTable" style="width:100%">
        <thead class="bg-light">
          <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Product</th>
            <th>Option</th>
            <th>Value</th>
            <th>Price</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="dataForm" class="modal-content">
      @csrf
      <input type="hidden" id="edit_id" name="id">
      <div class="modal-header">
        <h5 class="modal-title">Assign Option</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="products_id" class="form-label">Product</label>
          <select name="products_id" id="products_id" class="form-select">
            <option value="">Select</option>
            @foreach($products as $p)
              <option value="{{ $p->products_id }}">{{ $p->products_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-products_id"></small>
        </div>

        <div class="mb-3">
          <label for="options_id" class="form-label">Option</label>
          <select name="options_id" id="options_id" class="form-select">
            <option value="">Select</option>
            @foreach($options as $o)
              <option value="{{ $o->options_id }}">{{ $o->options_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-options_id"></small>
        </div>

        <div class="mb-3">
          <label for="value_id" class="form-label">Value</label>
          <select name="value_id" id="value_id" class="form-select">
            <option value="">Select</option>
            @foreach($values as $v)
              <option value="{{ $v->value_id }}">{{ $v->value_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-value_id"></small>
        </div>

        <div class="mb-3">
          <label for="options_price" class="form-label">Price</label>
          <input type="number" name="options_price" id="options_price" class="form-control" placeholder="Enter Price" step="0.01" min="0" />
          <small class="text-danger error-options_price"></small>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function () {
  const ptoModal = new bootstrap.Modal(document.getElementById('formModal'));

  toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 3000
  };

  const ptoTable = $('#dataTable').DataTable({
    ajax: '{{ route("product_to_option.index") }}',
    columns: [
      {
        className: 'dt-control',
        orderable: false,
        data: null,
        defaultContent: '',
        width: "20px"
      },
      { data: null, render: (d, t, r, m) => m.row + 1 },
      { data: 'product' },
      { data: 'option' },
      { data: 'value' },
      { data: 'options_price' }
    ]
  });

  // Toggle expand row for Edit/Delete buttons
  $('#dataTable tbody').on('click', 'td.dt-control', function () {
    const tr = $(this).closest('tr');
    const row = ptoTable.row(tr);
    const data = row.data();

    if (row.child.isShown()) {
      row.child.hide();
      tr.removeClass('shown');
    } else {
      row.child(`
        <div class="d-flex gap-2 p-2">
          <button class="btn btn-sm btn-info editPTOBtn" data-id="${data.id}">Edit</button>
          <button class="btn btn-sm btn-danger deletePTOBtn" data-id="${data.id}">Delete</button>
        </div>
      `).show();
      tr.addClass('shown');
    }
  });

  // Add button click
  $('#addBtn').click(() => {
    $('#dataForm')[0].reset();
    $('.text-danger').text('');
    $('#edit_id').val('');
    ptoModal.show();
  });

  // Submit form (Add or Update)
  $('#dataForm').submit(function (e) {
    e.preventDefault();
    const id = $('#edit_id').val();
    const url = id
      ? `/admin/product-to-option/update/${id}`
      : `{{ route('product_to_option.store') }}`;

    $.post(url, $(this).serialize())
      .done(res => {
        toastr.success(res.message);
        ptoModal.hide();
        ptoTable.ajax.reload(null, false);
      })
      .fail(err => {
        $('.text-danger').text('');
        if (err.status === 422) {
          const errors = err.responseJSON.errors;
          for (let field in errors) {
            $(`.error-${field}`).text(errors[field][0]);
          }
        } else {
          toastr.error('Error occurred');
        }
      });
  });

  // Edit button click
  $(document).on('click', '.editPTOBtn', function () {
    const id = $(this).data('id');
    $.get(`/admin/product-to-option/show/${id}`, data => {
      $('#products_id').val(data.products_id);
      $('#options_id').val(data.options_id);
      $('#value_id').val(data.value_id);
      $('#options_price').val(data.options_price ?? '');
      $('#edit_id').val(data.products_to_option_id);
      $('.text-danger').text('');
      ptoModal.show();
    }).fail(() => {
      toastr.error('Unable to fetch record.');
    });
  });

  // Delete button click
  $(document).on('click', '.deletePTOBtn', function () {
    if (!confirm('Are you sure you want to delete this record?')) return;

    $.post(`{{ route('product_to_option.delete') }}`, {
      _token: '{{ csrf_token() }}',
      id: $(this).data('id')
    }, res => {
      toastr.success(res.message);
      ptoTable.ajax.reload(null, false);
    }).fail(() => {
      toastr.error('Failed to delete record.');
    });
  });
});
</script>
@endsection
