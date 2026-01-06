@extends('admin.layouts.master')

@section('main_section')
<style>
    table.dataTable td.dt-control:before {
        background: #317cb1 !important;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4 class="mb-3">Tax Rates Management</h4>
      <button class="btn btn-primary btn-sm" id="addTaxRateBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="taxRatesTable">
        <thead class="bg-light">
          <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Zone</th>
            <th>Tax Class</th>
            <th>Priority</th>
            <th>Rate (%)</th>
            <th>Description</th>
            <th>Date Added</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="taxRateModal" tabindex="-1" aria-labelledby="taxRateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="taxRateForm">
      @csrf
      <input type="hidden" id="tax_rates_id" name="id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="taxRateModalLabel">Add / Edit Tax Rate</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="formError" class="text-danger mb-2"></div>

          <div class="mb-3">
            <label>Tax Zone *</label>
            <select class="form-control" name="tax_zone_id" id="tax_zone_id" required>
              <option value="">Select Zone</option>
              @foreach($zones as $zone)
                <option value="{{ $zone->zone_id }}">{{ $zone->zone_name }}</option>
              @endforeach
            </select>
            <div class="invalid-feedback" id="error_tax_zone_id"></div>
          </div>

          <div class="mb-3">
            <label>Tax Class *</label>
            <select class="form-control" name="tax_class_id" id="tax_class_id" required>
              <option value="">Select Class</option>
              @foreach($taxClasses as $tc)
                <option value="{{ $tc->tax_class_id }}">{{ $tc->tax_class_title }}</option>
              @endforeach
            </select>
            <div class="invalid-feedback" id="error_tax_class_id"></div>
          </div>

          <div class="mb-3">
            <label>Priority *</label>
            <input type="number" class="form-control" name="tax_priority" id="tax_priority" required>
            <div class="invalid-feedback" id="error_tax_priority"></div>
          </div>

          <div class="mb-3">
            <label>Tax Rate (%) *</label>
            <input type="number" class="form-control" step="0.01" name="tax_rate" id="tax_rate" required>
            <div class="invalid-feedback" id="error_tax_rate"></div>
          </div>

          <div class="mb-3">
            <label>Description</label>
            <textarea class="form-control" name="tax_description" id="tax_description"></textarea>
            <div class="invalid-feedback" id="error_tax_description"></div>
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

<script>
$(document).ready(function () {
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };

    const table = $('#taxRatesTable').DataTable({
        ajax: "{{ route('tax-rates.data') }}",
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'tax_rates_id' },
            {
                data: 'tax_zone',
                render: data => data ? data.zone_name : 'N/A'
            },
            {
                data: 'tax_class',
                render: data => data ? data.tax_class_title : 'N/A'
            },
            { data: 'tax_priority' },
            {
                data: 'tax_rate',
                render: data => data + '%'
            },
            { data: 'tax_description' },
            {
                data: 'date_added',
                render: data => new Date(data).toLocaleDateString()
            }
        ],
        order: [[1, 'desc']]
    });

    // Expand row
    $('#taxRatesTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = table.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-primary editBtn" data-id="${data.tax_rates_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.tax_rates_id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    const taxModal = new bootstrap.Modal(document.getElementById('taxRateModal'));

    function clearValidation() {
        $('#taxRateForm .form-control').removeClass('is-invalid');
        $('[id^="error_"]').text('');
        $('#formError').text('');
    }

    $('#addTaxRateBtn').click(() => {
        clearValidation();
        $('#taxRateForm')[0].reset();
        $('#tax_rates_id').val('');
        $('#saveBtn').text('Save');
        taxModal.show();
    });

    $('#taxRateForm').submit(function (e) {
        e.preventDefault();
        clearValidation();

        const id = $('#tax_rates_id').val();
        const url = id
            ? `/admin/tax-rates/${id}`
            : `{{ route('tax-rates.store') }}`;
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize() + (id ? '&_method=PUT' : ''),
            success: res => {
                toastr.success(res.message);
                taxModal.hide();
                table.ajax.reload();
            },
            error: xhr => {
                if (xhr.status === 422) {
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

    $(document).on('click', '.editBtn', function () {
        clearValidation();
        const id = $(this).data('id');
        $.get(`/admin/tax-rates/${id}`, data => {
            const d = data;
            $('#tax_rates_id').val(d.tax_rates_id);
            $('#tax_zone_id').val(d.tax_zone_id);
            $('#tax_class_id').val(d.tax_class_id);
            $('#tax_priority').val(d.tax_priority);
            $('#tax_rate').val(d.tax_rate);
            $('#tax_description').val(d.tax_description);
            $('#saveBtn').text('Update');
            taxModal.show();
        }).fail(() => {
            $('#formError').text('Failed to fetch data.');
        });
    });

    $(document).on('click', '.deleteBtn', function () {
        if (!confirm('Are you sure you want to delete this record?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: `/admin/tax-rates/${id}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: res => {
                toastr.success(res.message);
                table.ajax.reload();
            },
            error: () => {
                toastr.error('Failed to delete');
            }
        });
    });
});

</script>
@endsection
