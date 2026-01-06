@extends('admin.layouts.master')

@section('main_section')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4 class="mb-3">Diamond Key To Symbols Master</h4>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#keySymbolModal" id="addKeySymbolBtn">
        Add New
      </button>
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="keySymbolTable">
          <thead>
            <tr class="bg-light">
              <th>ID</th>
              <th>Name</th>
              <th>Alias</th>
              <th>Short Name</th>
              <th>Symbol Status</th>
              <th>Sort Order</th>
              <th>Date Added</th>
              <th>Date Modified</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {{-- Records will be loaded via AJAX --}}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Create/Edit -->
<div class="modal fade" id="keySymbolModal" tabindex="-1" aria-labelledby="keySymbolModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="keySymbolForm">
      @csrf
      <input type="hidden" id="record_id" name="id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Key To Symbols Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Form Fields -->
          <div class="mb-3">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="alias">Alias</label>
            <input type="text" class="form-control" id="alias" name="alias">
          </div>
          <div class="mb-3">
            <label for="short_name">Short Name</label>
            <input type="text" class="form-control" id="short_name" name="short_name">
          </div>
          <div class="mb-3">
            <label for="sym_status">Symbol Status</label>
            <input type="number" class="form-control" id="sym_status" name="sym_status">
          </div>
          <div class="mb-3">
            <label for="sort_order">Sort Order</label>
            <input type="number" class="form-control" id="sort_order" name="sort_order">
          </div>
          <div id="formError" class="text-danger"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary me-2" id="saveLabBtn">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>            
      </div>
      </div>
    </form>
  </div>
</div>

<script>
// Helper function to format datetime for datetime-local input
function formatDateForInput(dateString) {
  if (!dateString) return "";
  let dt = new Date(dateString);
  let year = dt.getFullYear();
  let month = ("0" + (dt.getMonth() + 1)).slice(-2);
  let day = ("0" + dt.getDate()).slice(-2);
  let hours = ("0" + dt.getHours()).slice(-2);
  let minutes = ("0" + dt.getMinutes()).slice(-2);
  return `${year}-${month}-${day}T${hours}:${minutes}`;
}

$(document).ready(function(){
  fetchRecords();

  function fetchRecords(){
    $.get("{{ route('keytosymbols.index') }}", function(response){
      let rows = '';
      response.forEach(record => {
        rows += `
          <tr>
            <td>${record.id}</td>
            <td>${record.name ?? ''}</td>
            <td>${record.alias ?? ''}</td>
            <td>${record.short_name ?? ''}</td>
            <td>${record.sym_status ?? ''}</td>
            <td><input type="number" value="${record.sort_order}" class="sort-order" data-id="${record.id}" style="width: 60px;"></td>
            <td>${record.date_added ? record.date_added : ''}</td>
            <td>${record.date_modify ? record.date_modify: ''}</td>
            <td>
              <button class="btn btn-sm btn-info editBtn" data-id="${record.id}"><i class="fa fa-edit"></i></button>
              <button class="btn btn-sm btn-danger deleteBtn" data-id="${record.id}"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
        `;
      });
      renderDataTable('keySymbolTable', rows); 
    });
  }

  $('#addKeySymbolBtn').click(function(){
    $('#keySymbolForm')[0].reset();
    $('#record_id').val('');
    $('#formError').text('');
    $("#saveLabBtn").text('Save');
  });

  // Edit record via AJAX using the "show" route to fetch record details
  $(document).on('click', '.editBtn', function(){
    let id = $(this).data('id');
    $.get("{{ url('admin/keyToSymbols') }}/" + id, function(data){
      $('#keySymbolForm')[0].reset();
      $('#formError').text('');
      $('#record_id').val(data.id);
      $('#name').val(data.name);
      $('#alias').val(data.alias);
      $('#short_name').val(data.short_name);
      $('#sym_status').val(data.sym_status);
      $('#sort_order').val(data.sort_order);
      $("#saveLabBtn").text('Update');
      $('#keySymbolModal').modal('show');
    });
  });

  // Create/Update form submit
  $('#keySymbolForm').submit(function(e){
    e.preventDefault();
    const id = $('#record_id').val();
    const method = id ? 'PUT' : 'POST';
    const url = id 
                ? `{{ url('admin/keyToSymbols') }}/${id}` 
                : "{{ route('keytosymbols.store') }}";
    
    let formData = $(this).serialize();
    formData += `&_method=${method}`;
    
    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      success: function(res){
        $('#keySymbolModal').modal('hide');
        fetchRecords();
        toastr.success("Record saved successfully!");
      },
      error: function(xhr){
        let errors = xhr.responseJSON?.errors || {};
        let msg = Object.values(errors).join('<br>');
        $('#formError').html(msg || 'An error occurred');
        toastr.error("Failed to save record!");
      }
    });
  });

  $(document).on('blur', '.sort-order', function() {
            const id = $(this).data('id');
            const sortOrder = $(this).val();

            $.ajax({
                url: `{{ url('admin/keyToSymbols') }}/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    sort_order: sortOrder
                },
                success: function() {
                    toastr.success('Sort order updated successfully!');
                },
                error: function() {
                    toastr.error('Failed to update sort order!');
                }
            });
        });


  // Delete record
  $(document).on('click', '.deleteBtn', function(){
    const id = $(this).data('id');
    if(confirm("Are you sure you want to delete this record?")){
      $.ajax({
        url: `{{ url('admin/keyToSymbols') }}/${id}`,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          _method: 'DELETE'
        },
        success: function(){
          fetchRecords();
          toastr.success("Record deleted successfully!");
        }
      });
    }
  });
});
</script>
@endsection
