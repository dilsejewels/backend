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
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4 class="mb-3">Metal Type Management</h4>
      <button class="btn btn-primary" id="addMetalBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="metalTable">
        <thead class="bg-light">
          <tr>
            <th></th>
            <th>ID</th>
            <th>Name</th>
            <th>Tooltip</th>
            <th>Status</th>
            <th>Sort Order</th>
            <th>Color</th>
            <th>Icon</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="metalModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="metalForm" class="modal-content">
      @csrf
      <input type="hidden" id="dmt_id" name="dmt_id">
      <div class="modal-header">
        <h5 class="modal-title">Metal Type Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Name *</label>
          <input type="text" name="dmt_name" id="dmt_name" class="form-control">
          <small class="text-danger error-dmt_name"></small>
        </div>
        <div class="mb-3">
          <label class="form-label">Tooltip</label>
          <input type="text" name="dmt_tooltip" id="dmt_tooltip" class="form-control">
          <small class="text-danger error-dmt_tooltip"></small>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="dmt_status" id="dmt_status" class="form-select">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <small class="text-danger error-dmt_status"></small>
        </div>
        <div class="mb-3">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" id="sort_order" class="form-control">
          <small class="text-danger error-sort_order"></small>
        </div>
        <div class="mb-3">
          <label class="form-label">Color Code</label>
          <input type="text" name="color_code" id="color_code" class="form-control">
          <small class="text-danger error-color_code"></small>
        </div>
        <div class="mb-3">
          <label class="form-label">Icon Class</label>
          <input type="text" name="metal_icon" id="metal_icon" class="form-control">
          <small class="text-danger error-metal_icon"></small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">
          <i class="fa fa-save me-1"></i> Save
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
$(function () {
  const metalModal = new bootstrap.Modal('#metalModal');

  const table = $('#metalTable').DataTable({
    ajax: "{{ route('metaltype.fetch') }}",
    columns: [
      {
        className: 'dt-control',
        orderable: false,
        data: null,
        defaultContent: `<div class="toggle-icon"><i class="fa fa-plus"></i></div>`
      },
      { data: 'dmt_id' },
      { data: 'dmt_name' },
      { data: 'dmt_tooltip' },
      {
        data: 'dmt_status',
        render: data => `<input type="checkbox" class="toggle-status" ${data == 1 ? 'checked' : ''}>`
      },
      {
        data: 'sort_order',
        render: data => `<input type="number" class="sort-order form-control" value="${data || 0}" style="width:80px">`
      },
      {
        data: 'color_code',
        render: data => `<div style="background:${data}; padding:5px;">${data}</div>`
      },
      {
        data: 'metal_icon',
        render: data => data ? data : ''
      }
    ]
  });

  $('#metalTable tbody').on('click', 'td.dt-control', function () {
    const tr = $(this).closest('tr');
    const row = table.row(tr);
    const icon = $(this).find('i');

    if (row.child.isShown()) {
      row.child.hide();
      tr.removeClass('shown');
      icon.removeClass('fa-minus').addClass('fa-plus');
    } else {
      row.child(`
        <div class="d-flex gap-2 p-2">
          <button class="btn btn-sm btn-info editBtn" data-id="${row.data().dmt_id}">
            <i class="fa fa-edit"></i> Edit
          </button>
          <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.data().dmt_id}">
            <i class="fa fa-trash"></i> Delete
          </button>
        </div>
      `).show();
      tr.addClass('shown');
      icon.removeClass('fa-plus').addClass('fa-minus');
    }
  });

  $('#addMetalBtn').click(() => {
    $('#metalForm')[0].reset();
    $('#dmt_id').val('');
    $('.text-danger').text('');
    $('.form-control, .form-select').removeClass('is-invalid');
    metalModal.show();
  });

  // Tooltip validation (only PL or 2 digits)
  $('#dmt_tooltip').on('input', function () {
    const val = $(this).val().toUpperCase();
    const regex = /^(PL|\d{0,2})$/;

    if (!regex.test(val)) {
      $(this).addClass('is-invalid');
      $('.error-dmt_tooltip').text('Only "PL" or up to 2 digits are allowed.');
    } else {
      $(this).removeClass('is-invalid');
      $('.error-dmt_tooltip').text('');
    }
    $(this).val(val);
  });

  $('#metalForm').submit(function (e) {
    e.preventDefault();
    const id = $('#dmt_id').val();
    const url = id ? `{{ url('admin/metal-type/update') }}/${id}` : `{{ route('metaltype.store') }}`;
    const $btn = $(this).find('button[type="submit"]');

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');
    $('.text-danger').text('');
    $('.form-control, .form-select').removeClass('is-invalid');

    $.ajax({
      url: url,
      type: 'POST',
      data: $(this).serialize() + (id ? '&_method=PUT' : ''),
      success: res => {
        metalModal.hide();
        table.ajax.reload();
        toastr.success('Saved successfully');
        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
      },
      error: xhr => {
        $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save');
        if (xhr.status === 422) {
          $.each(xhr.responseJSON.errors, (key, val) => {
            $(`.error-${key}`).text(val[0]);
            $(`[name="${key}"]`).addClass('is-invalid');
          });
        } else {
          toastr.error('Something went wrong');
        }
      }
    });
  });

  $(document).on('click', '.editBtn', function () {
    const id = $(this).data('id');
    $.get(`{{ url('admin/metal-type/show') }}/${id}`, data => {
      $('#dmt_id').val(data.dmt_id);
      $('#dmt_name').val(data.dmt_name);
      $('#dmt_tooltip').val(data.dmt_tooltip);
      $('#dmt_status').val(data.dmt_status);
      $('#sort_order').val(data.sort_order);
      $('#color_code').val(data.color_code);
      $('#metal_icon').val(data.metal_icon);
      $('.text-danger').text('');
      $('.form-control, .form-select').removeClass('is-invalid');
      metalModal.show();
    });
  });

  $(document).on('click', '.deleteBtn', function () {
    if (!confirm('Are you sure you want to delete this item?')) return;
    const id = $(this).data('id');

    $.ajax({
      url: `{{ url('admin/metal-type/delete') }}/${id}`,
      type: 'DELETE',
      data: { _token: '{{ csrf_token() }}' },
      success: () => {
        table.ajax.reload();
        toastr.success('Deleted successfully');
      }
    });
  });

  $(document).on('change', '.toggle-status', function () {
    const id = $(this).closest('tr').find('.editBtn').data('id');
    const status = $(this).is(':checked') ? 1 : 0;

    $.ajax({
      url: `{{ url('admin/metal-type/update') }}/${id}`,
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        _method: 'PUT',
        dmt_status: status
      },
      success: () => {
        toastr.success('Status updated');
      }
    });
  });

  $(document).on('blur', '.sort-order', function () {
    const id = $(this).closest('tr').find('.editBtn').data('id');
    const sort = $(this).val();

    $.ajax({
      url: `{{ url('admin/metal-type/update') }}/${id}`,
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        _method: 'PUT',
        sort_order: sort
      },
      success: () => {
        toastr.success('Sort order updated');
      }
    });
  });
});
</script>
@endsection
