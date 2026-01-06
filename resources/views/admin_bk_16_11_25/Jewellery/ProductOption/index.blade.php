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
      <h4 class="mb-0">Product Options</h4>
      <button class="btn btn-primary" id="addOptionBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="optionsTable">
        <thead class="bg-light">
          <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Option Name</th>
            <th>Option Type</th>
            <th>Sort Order</th>
            <th>Is Compulsory</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="optionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="optionForm" class="modal-content">
      @csrf
      <input type="hidden" id="options_id" name="options_id">
      <div class="modal-header">
        <h5 class="modal-title">Option Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {{-- Option Name --}}
        <div class="mb-3">
          <label class="form-label">Option Name <span class="text-danger">*</span></label>
          <input type="text" name="options_name" id="options_name" class="form-control">
          <small class="text-danger error-options_name"></small>
        </div>

        {{-- Option Type --}}
        <div class="mb-3">
          <label class="form-label">Option Type <span class="text-danger">*</span></label>
          <input type="number" name="options_type" id="options_type" class="form-control">
          <small class="text-danger error-options_type"></small>
        </div>

        {{-- Sort Order --}}
        <div class="mb-3">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" id="sort_order" class="form-control">
          <small class="text-danger error-sort_order"></small>
        </div>

        {{-- Is Compulsory --}}
        <div class="mb-3">
          <label class="form-label">Is Compulsory</label>
          <select name="is_compulsory" id="is_compulsory" class="form-select">
            <option value="">Select</option>
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
          <small class="text-danger error-is_compulsory"></small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
const optionTable = $('#optionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("product-options.fetch") }}',
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'options_id' },
            { data: 'options_name' },
            { data: 'options_type' },
            { data: 'sort_order' },
            { 
                data: 'is_compulsory',
                render: data => data == 1 ? 'Yes' : 'No'
            }
        ]
    });

    // Toggle expand row for Product Options
    $('#optionsTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = optionTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editOptionBtn" data-id="${data.options_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteOptionBtn" data-id="${data.options_id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    const optionModal = new bootstrap.Modal(document.getElementById('optionModal'));

    // Open modal for new
    $('#addOptionBtn').click(function() {
        $('#optionForm')[0].reset();
        $('.text-danger').text('');
        $('#options_id').val('');
        $('#saveBtn').text('Save');
        optionModal.show();
    });

    // Save (Create/Update)
    $('#optionForm').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');
        let id = $('#options_id').val();
        let url = id 
            ? '/admin/product-options/update/' + id 
            : '{{ route("product-options.store") }}';

        $.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#optionForm')[0].reset();
                optionModal.hide();
                optionTable.ajax.reload();
                toastr.success(res.message);
            },
            error: function(xhr) {
                $.each(xhr.responseJSON.errors, function(key, val) {
                    $('.error-' + key).text(val[0]);
                });
            }
        });
    });

    // Edit button
    $(document).on('click', '.editOptionBtn', function() {
        let id = $(this).data('id');
        $.get('/admin/product-options/edit/' + id, function(data) {
            $('#options_id').val(data.options_id);
            $('#options_name').val(data.options_name);
            $('#options_type').val(data.options_type);
            $('#sort_order').val(data.sort_order);
            $('#is_compulsory').val(data.is_compulsory);
            $('#saveBtn').text('Update');
            $('.text-danger').text('');
            optionModal.show();
        });
    });

    // Delete button
    $(document).on('click', '.deleteOptionBtn', function() {
        if (confirm('Are you sure you want to delete this?')) {
            let id = $(this).data('id');
            $.ajax({
                url: '/admin/product-options/delete/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    optionTable.ajax.reload();
                    toastr.success(res.message);
                }
            });
        }
    });
});

</script>
@endsection
 