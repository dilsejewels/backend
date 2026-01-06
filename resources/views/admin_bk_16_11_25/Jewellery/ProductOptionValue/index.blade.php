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
      <h4>Product Option Values</h4>
      <button class="btn btn-primary" id="addBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="optionValueTable">
        <thead class="bg-light">
          <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Option</th>
            <th>Value Name</th>
            <th>Sort Order</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="valueModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="valueForm" class="modal-content">
      @csrf
      <input type="hidden" name="value_id" id="value_id">
      <div class="modal-header">
        <h5 class="modal-title">Option Value</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3">
          <label>Option Name <span class="text-danger">*</span></label>
          <select name="options_id" id="options_id" class="form-select">
            <option value="">Select Option</option>
            @foreach($options as $opt)
              <option value="{{ $opt->options_id }}">{{ $opt->options_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-options_id"></small>
        </div>

        <div class="mb-3">
          <label>Value Name <span class="text-danger">*</span></label>
          <input type="text" name="value_name" id="value_name" class="form-control">
          <small class="text-danger error-value_name"></small>
        </div>

        <div class="mb-3">
          <label>Sort Order</label>
          <input type="number" name="sort_order" id="sort_order" class="form-control">
          <small class="text-danger error-sort_order"></small>
        </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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

const valueTable = $('#optionValueTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("product-option-values.index") }}',
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'value_id' },
            { data: 'option_name' },
            { data: 'value_name' },
            { data: 'sort_order' }
        ]
    });

    // Toggle expand row for Option Values
    $('#optionValueTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = valueTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editValueBtn" data-id="${data.value_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteValueBtn" data-id="${data.value_id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    const valueModal = new bootstrap.Modal(document.getElementById('valueModal'));

    $('#addBtn').click(function () {
        $('#valueForm')[0].reset();
        $('.text-danger').text('');
        $('#value_id').val('');
        valueModal.show();
    });

    $('#valueForm').on('submit', function (e) {
        e.preventDefault();
        $('.text-danger').text('');
        let id = $('#value_id').val();
        let url = id ? `/admin/product-option-values/update/${id}` : `{{ route('product-option-values.store') }}`;

        $.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: res => {
                valueTable.ajax.reload();
                valueModal.hide();
                toastr.success(res.message);
            },
            error: err => {
                $.each(err.responseJSON.errors, (key, val) => {
                    $('.error-' + key).text(val[0]);
                });
            }
        });
    });

    $(document).on('click', '.editValueBtn', function () {
        let id = $(this).data('id');
        $.get(`/admin/product-option-values/edit/${id}`, function (data) {
            $('#value_id').val(data.value_id);
            $('#options_id').val(data.options_id);
            $('#value_name').val(data.value_name);
            $('#sort_order').val(data.sort_order);
            $('.text-danger').text('');
            valueModal.show();
        });
    });

    $(document).on('click', '.deleteValueBtn', function () {
        if (!confirm('Are you sure you want to remove this?')) return;
        let id = $(this).data('id');
        $.ajax({
            url: `/admin/product-option-values/delete/${id}`,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: res => {
                valueTable.ajax.reload();
                toastr.success(res.message);
            }
        });
    });
  });
</script>
@endsection
 