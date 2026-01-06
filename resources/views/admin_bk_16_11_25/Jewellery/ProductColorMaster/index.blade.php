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
      <h4 class="mb-0">Product Color Master</h4>
      <button class="btn btn-primary btn-sm" id="addColorBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
        <table class="table table-hover" id="colorTable">
          <thead class="bg-light">
            <tr>
              <th>Actions</th>
              <th>ID</th>
              <th>Name</th>
              <th>Short Name</th>
              <th>Alias</th>
              <th>Remark</th>
              <th>Front</th>
              <th>Sort</th>
              
            </tr>
          </thead>
          <tbody></tbody>
        </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="colorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="colorForm" class="modal-content">
      @csrf
      <input type="hidden" id="color_id" name="color_id">
      <div class="modal-header">
        <h5 class="modal-title">Color Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Color Name</label>
          <input type="text" name="name" id="name" class="form-control">
          <small class="text-danger error-name"></small>
        </div>
        <div class="mb-3">
          <label class="form-label">Short Name</label>
          <input type="text" name="short_name" id="short_name" class="form-control">
          <small class="text-danger error-short_name"></small>
        </div>
        <div class="mb-3">
          <label class="form-label">Alias</label>
          <input type="text" name="alias" id="alias" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Remark</label>
          <input type="text" name="remark" id="remark" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Display in Front</label>
            <select name="display_in_front" id="display_in_front" class="form-select">
                <option value="">Select</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
            <small class="text-danger error-display_in_front"></small>
        </div>

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
$(document).ready(function () {
    const colorTable = $('#colorTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('product-color.fetch') }}",
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'short_name', name: 'short_name' },
            { data: 'alias', name: 'alias' },
            { data: 'remark', name: 'remark' },
            { 
                data: 'display_in_front', 
                name: 'display_in_front',
                render: function(data) {
                    return data == 1 
                        ? '<span class="badge bg-primary">Yes</span>' 
                        : '<span class="badge bg-danger">No</span>';
                }
            },
            { data: 'sort_order', name: 'sort_order' }
        ],
        order: [[1, 'asc']]
    });

    // Toggle expand row for Color Master
    $('#colorTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = colorTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editColorBtn" data-id="${data.id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteColorBtn" data-id="${data.id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    // Add new color
    $('#addColorBtn').on('click', function() {
        $('#colorForm')[0].reset();
        $('#color_id').val('');
        $('#colorModal').modal('show');
    });

    // Save color
    $('#colorForm').submit(function(e) {
        e.preventDefault();
        const id = $('#color_id').val();
        const url = id 
            ? "{{ url('admin/product-color-master/update') }}/" + id 
            : "{{ route('product-color.store') }}";
            
        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#colorModal').modal('hide');
                toastr.success(res.success);
                colorTable.ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        $(`.error-${field}`).text(errors[field][0]);
                    }
                } else {
                    toastr.error('Failed to save color');
                }
            }
        });
    });

    // Edit color
    $(document).on('click', '.editColorBtn', function() {
        const id = $(this).data('id');
        $.get(`{{ url('admin/product-color-master/edit') }}/${id}`, function(data) {
            $('#color_id').val(data.id);
            $('#name').val(data.name);
            $('#short_name').val(data.short_name);
            $('#alias').val(data.alias);
            $('#remark').val(data.remark);
            $('#display_in_front').val(data.display_in_front);
            $('#sort_order').val(data.sort_order);
            $('#colorModal').modal('show');
        });
    });

    // Delete color
    $(document).on('click', '.deleteColorBtn', function() {
      if (!confirm('Are you sure you want to delete this color?')) return;
      const id = $(this).data('id');
        
      $.ajax({
        url: `{{ url('admin/product-color-master/delete') }}/${id}`,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
          toastr.success(res.success);
          colorTable.ajax.reload();
        },
        error: function() {
          toastr.error('Failed to delete color');
        }
      });
    });
});

</script>
@endsection




