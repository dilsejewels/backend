@extends('admin.layouts.master')

@section('main_section')
<style>
    table.dataTable td.dt-control:before {
        background: #317cb1;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Product Cut Master</h4>
      <button class="btn btn-primary" id="addCutBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="cutTable">
        <thead class="bg-light">
          <tr>
            <th>Actions</th>
            <th>ID</th>
            <th>Name</th>
            <th>Alias</th> 
            <th>Short Name</th>
            <th>Remark</th>
            <th>Front</th>
            <th>Sort Order</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="cutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="cutForm" class="modal-content">
      @csrf
      <input type="hidden" id="cut_id" name="cut_id">
      <div class="modal-header">
        <h5 class="modal-title">Cut Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {{-- Name --}}
        <div class="mb-3">
          <label class="form-label">Name <span class="text-danger">*</span></label>
          <input type="text" name="name" id="name" class="form-control">
          <small class="text-danger error-name"></small>
        </div>

        {{-- Alias --}}
        <div class="mb-3">
          <label class="form-label">Alias</label>
          <input type="text" name="alias" id="alias" class="form-control">
          <small class="text-danger error-alias"></small>
        </div>

        {{-- Short Name --}}
        <div class="mb-3">
          <label class="form-label">Short Name</label>
          <input type="text" name="shortname" id="shortname" class="form-control">
          <small class="text-danger error-shortname"></small>
        </div>

        {{-- Remark --}}
        <div class="mb-3">
          <label class="form-label">Remark</label>
          <input type="text" name="remark" id="remark" class="form-control">
          <small class="text-danger error-remark"></small>
        </div>

        {{-- Display In Front --}}
        <div class="mb-3">
          <label class="form-label">Display in Front</label>
          <select name="display_in_front" id="display_in_front" class="form-select">
            <option value="">Select</option>
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
          <small class="text-danger error-display_in_front"></small>
        </div>

        {{-- Sort Order --}}
        <div class="mb-3">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" id="sort_order" class="form-control">
          <small class="text-danger error-sort_order"></small>
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
 const cutTable = $('#cutTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("product-cut.fetch") }}',
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'id' },
            { data: 'name' },
            { data: 'alias' },
            { data: 'shortname' },
            { data: 'remark' },
            { 
                data: 'display_in_front',
                render: function(data) {
                    return data == 1 ? 'Yes' : 'No';
                }
            },
            { data: 'sort_order' }
        ]
    });

    // Toggle expand row for Cut Master
    $('#cutTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = cutTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editBtn" data-id="${data.id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    const cutModal = new bootstrap.Modal(document.getElementById('cutModal'));

    // "Add New" button
    $('#addCutBtn').click(function() {
        $('#cutForm')[0].reset();
        $('.text-danger').text('');
        $('#cut_id').val('');
        $('#saveBtn').text('Save');
        cutModal.show();
    });

    // Create / Update via AJAX
    $('#cutForm').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');
        let id = $('#cut_id').val();
        let url = id 
            ? 'product-cut-master/update/' + id 
            : '{{ route("product-cut.store") }}';

        $.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#cutForm')[0].reset();
                $('#cut_id').val('');
                cutTable.ajax.reload();
                cutModal.hide();
                toastr.success(res.success);
            },
            error: function(xhr) {
                $.each(xhr.responseJSON.errors, function(key, val) {
                    $('.error-' + key).text(val[0]);
                });
            }
        });
    });

    // Edit button click
    $(document).on('click', '.editBtn', function() {
        let id = $(this).data('id');
        $.get('product-cut-master/edit/' + id, function(data) {
            $('#cut_id').val(data.id);
            $('#name').val(data.name);
            $('#alias').val(data.alias);
            $('#shortname').val(data.shortname);
            $('#remark').val(data.remark);
            $('#display_in_front').val(data.display_in_front);
            $('#sort_order').val(data.sort_order);
            $('#saveBtn').text('Update');
            cutModal.show();
        });
    });

    // Delete button click
    $(document).on('click', '.deleteBtn', function() {
        if (confirm('Are you sure you want to delete this?')) {
            let id = $(this).data('id');
            $.ajax({
                url: 'product-cut-master/delete/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    cutTable.ajax.reload();
                    toastr.success(res.success);
                }
            });
        }
    });
  });
</script>
@endsection
