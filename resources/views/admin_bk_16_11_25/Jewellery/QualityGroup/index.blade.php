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
      <h4 class="mb-3">Diamond Quality Group Management</h4>
      <button class="btn btn-primary btn-sm" id="createNewDQG">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="dqgTable">
        <thead class="bg-light">
          <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Name</th>
            <th>Alias</th>
            <th>Short Name</th>
            <th>Sort Order</th>
            <th>Status</th>
            <th>Origin</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="dqgModal" tabindex="-1" aria-labelledby="dqgModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="dqgForm">
        <div class="modal-header">
          <h5 class="modal-title" id="dqgModalLabel">Add / Edit Quality Group</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body row g-3">
          <input type="hidden" id="dqg_id" name="dqg_id">

          <div class="col-md-6">
            <label>Name</label>
            <input type="text" class="form-control" name="dqg_name" id="dqg_name">
          </div>

          <div class="col-md-6">
            <label>Alias</label>
            <input type="text" class="form-control" name="dqg_alias" id="dqg_alias">
          </div>

          <div class="col-md-6">
            <label>Short Name</label>
            <input type="text" class="form-control" name="dqg_short_name" id="dqg_short_name">
          </div>

          <div class="col-md-6">
            <label>Sort Order</label>
            <input type="number" class="form-control" name="dqg_sort_order" id="dqg_sort_order">
          </div>

          <div class="col-md-6">
            <label>Status</label>
            <select class="form-control" name="dqg_status" id="dqg_status">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>

          <div class="col-md-6">
            <label>Origin</label>
            <input type="text" class="form-control" name="dqg_origin" id="dqg_origin">
          </div>

          <div class="col-12">
            <label>Description</label>
            <textarea class="form-control" name="description" id="description"></textarea>
          </div>

          <div class="col-12">
            <label>Icon</label>
            <input type="text" class="form-control" name="dqg_icon" id="dqg_icon">
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Script -->
<script>
$(document).ready(function() {
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };

    const dqgTable = $('#dqgTable').DataTable({
        ajax: "{{ route('diamondqualitygroup.fetch') }}",
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'dqg_id' },
            { data: 'dqg_name' },
            { data: 'dqg_alias' },
            { data: 'dqg_short_name' },
            { data: 'dqg_sort_order' },
            {
                data: 'dqg_status',
                render: data => data == 1 ? 'Active' : 'Inactive'
            },
            { data: 'dqg_origin' }
        ]
    });

    $('#dqgTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = dqgTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-primary editDQGBtn" data-id="${data.dqg_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteDQGBtn" data-id="${data.dqg_id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    const dqgModal = new bootstrap.Modal(document.getElementById('dqgModal'));

    function clearDQGValidation(){
        $('#dqgForm .form-control').removeClass('is-invalid');
        $('[id^="error_"]').text('');
        $('#formError').text('');
    }

    $('#createNewDQG').click(() => {
        clearDQGValidation();
        $('#dqgForm')[0].reset();
        $('#dqg_id').val('');
        $('#saveBtn').text('Save');
        dqgModal.show();
    });

    $('#dqgForm').submit(function(e){
        e.preventDefault();
        clearDQGValidation();

        const id = $('#dqg_id').val();
        const url = id 
            ? `/api/admin/diamondqualitygroup/update/${id}` 
            : `{{ route('diamondqualitygroup.store') }}`;

        const method = id ? 'POST' : 'POST';
        const data = $(this).serialize() + (id ? '&_method=POST' : '');

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: res => {
                toastr.success(res.success);
                dqgModal.hide();
                dqgTable.ajax.reload();
            },
            error: xhr => {
                if(xhr.status === 422){
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

    $(document).on('click', '.editDQGBtn', function(){
    clearDQGValidation();
    const id = $(this).data('id');

    $.get(`/api/admin/diamondqualitygroup/edit/${id}`)
        .done(response => {
            if (response.data) {
                const d = response.data;
                $('#dqg_id').val(d.dqg_id);
                $('#dqg_name').val(d.dqg_name);
                $('#dqg_alias').val(d.dqg_alias);
                $('#dqg_short_name').val(d.dqg_short_name);
                $('#description').val(d.description);
                $('#dqg_icon').val(d.dqg_icon);
                $('#dqg_sort_order').val(d.dqg_sort_order);
                $('#dqg_status').val(d.dqg_status);
                $('#dqg_origin').val(d.dqg_origin);
                dqgModal.show();
            } else {
                toastr.error('Record not found');
            }
        })
        .fail(xhr => {
            if (xhr.status === 404) {
                toastr.error('Record not found');
            } else {
                toastr.error('Failed to fetch record');
            }
        });
});

// And for the delete function:
$(document).on('click', '.deleteDQGBtn', function(){
    if(!confirm('Are you sure you want to delete this record?')) return;
    const id = $(this).data('id');
    
    $.ajax({
        url: `/api/admin/diamondqualitygroup/destroy/${id}`, // ✅ Correct URL
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: res => {
            toastr.success(res.success);
            dqgTable.ajax.reload();
        },
        error: () => {
            toastr.error('Failed to delete');
        }
    });
});
});
</script>
@endsection
