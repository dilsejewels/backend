@extends('admin.layouts.master')

@section('main_section')
<style>
  .fa-plus-circle:before {
    content: "\f055";
    visibility: hidden;
  }

  .fa-minus-circle:before {
    content: "\f056";
    visibility: hidden;
  }

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
      <h4 class="mb-3">Product Clarity Master Management</h4>
      <button class="btn btn-primary btn-sm" id="addClarityBtn">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="clarityTable">
        <thead class="bg-light">
          <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Name</th>
            <th>Alias</th>
            <th>Remark</th>
            <th>Display In Front</th>
            <th>Sort Order</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="clarityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="clarityForm" class="modal-content">
      @csrf
      <input type="hidden" id="pcm_id" name="id">
      <div class="modal-header">
        <h5 class="modal-title">Clarity Master Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Name <span class="text-danger">*</span></label>
          <input type="text" name="name" id="name" class="form-control">
          <span class="text-danger error-name"></span>
        </div>
        <div class="mb-3">
          <label>Alias</label>
          <input type="text" name="alias" id="alias" class="form-control">
          <span class="text-danger error-alias"></span>
        </div>
        <div class="mb-3">
          <label>Remark</label>
          <input type="text" name="remark" id="remark" class="form-control">
          <span class="text-danger error-remark"></span>
        </div>
        <div class="mb-3">
          <label>Display In Front</label>
          <select name="display_in_front" id="display_in_front" class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
          <span class="text-danger error-display_in_front"></span>
        </div>
        <div class="mb-3">
          <label>Sort Order</label>
          <input type="number" name="sort_order" id="sort_order" class="form-control">
          <span class="text-danger error-sort_order"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" id="saveClarityBtn" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
$(function() {
  const clarityModal = new bootstrap.Modal(document.getElementById('clarityModal'));

  const clarityTable = $('#clarityTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('ProductClarity.fetch') }}",
    columns: [
      {
        className: 'dt-control text-center',
        orderable: false,
        data: null,
        defaultContent: '<i class="fa fa-plus-circle text-primary fs-5"></i>',
        width: "30px"
      },
      { data: 'id', name: 'id' },
      { data: 'name', name: 'name' },
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

  // Toggle child row for edit/delete
  $('#clarityTable tbody').on('click', 'td.dt-control', function () {
    const tr = $(this).closest('tr');
    const row = clarityTable.row(tr);
    const icon = $(this).find('i');

    if (row.child.isShown()) {
      row.child.hide();
      tr.removeClass('shown');
    } else {
      row.child(`
        <div class="p-2">
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-info editClarityBtn" data-id="${row.data().id}">
              <i class="fa fa-edit"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger deleteClarityBtn" data-id="${row.data().id}">
              <i class="fa fa-trash"></i> Delete
            </button>
          </div>
        </div>
      `).show();
      tr.addClass('shown');
      icon.removeClass('fa-plus-circle text-primary').addClass('fa-minus-circle text-danger');
    }
  });

  // Add new
  $('#addClarityBtn').on('click', function() {
    $('#clarityForm')[0].reset();
    $('.text-danger').text('');
    $('#pcm_id').val('');
    $('#saveClarityBtn').text('Save');
    clarityModal.show();
  });

  // Save / Update
  $('#clarityForm').submit(function(e) {
    e.preventDefault();
    let id = $('#pcm_id').val();
    let url = id
      ? "{{ url('admin/product-clarity-master/update') }}/" + id
      : "{{ route('ProductClarity.store') }}";

    let formData = new FormData(this);
    if (id) formData.append('_method', 'PUT');

    $.ajax({
      url: url,
      method: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(response) {
        toastr.success(response.success);
        clarityModal.hide();
        clarityTable.ajax.reload();
      },
      error: function(xhr) {
        $('.text-danger').text('');
        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;
          $.each(errors, function(field, msg) {
            $('.error-' + field).text(msg[0]);
          });
        } else {
          toastr.error('Something went wrong.');
        }
      }
    });
  });

  // ✅ Edit Record
  $(document).on('click', '.editClarityBtn', function(e) {
    e.stopPropagation();
    let id = $(this).data('id');

    $.ajax({
      url: `{{ url('admin/product-clarity-master/edit') }}/${id}`,
      type: 'GET',
      success: function(response) {
        if (response.id) {
          $('#pcm_id').val(response.id);
          $('#name').val(response.name);
          $('#alias').val(response.alias);
          $('#remark').val(response.remark);
          $('#display_in_front').val(response.display_in_front);
          $('#sort_order').val(response.sort_order);
          $('#saveClarityBtn').text('Update');
          clarityModal.show();
        } else {
          toastr.error('Record not found.');
        }
      },
      error: function() {
        toastr.error('Failed to load clarity details.');
      }
    });
  });

  // ✅ Delete Record
  $(document).on('click', '.deleteClarityBtn', function(e) {
    e.stopPropagation();
    if (!confirm('Are you sure you want to delete this record?')) return;

    let id = $(this).data('id');
    $.ajax({
      url: `{{ url('admin/product-clarity-master/destroy') }}/${id}`,
      type: 'DELETE',
      data: { _token: '{{ csrf_token() }}' },
      success: function(res) {
        toastr.success(res.success);
        clarityTable.ajax.reload();
      },
      error: function() {
        toastr.error('Failed to delete clarity');
      }
    });
  });
});
</script>
@endsection
