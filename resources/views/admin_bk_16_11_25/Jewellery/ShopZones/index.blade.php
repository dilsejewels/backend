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
      <h4 class="mb-3">Shop Zones Management</h4>
      <button class="btn btn-primary btn-sm" id="createNewZone">Add New</button>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="zonesTable">
        <thead class="bg-light">
          <tr>
            <th>ID</th>
            <th>Country ID</th>
            <th>Zone Code</th>
            <th>Zone Name</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

{{-- Bootstrap Modal for Add/Edit --}}
<div class="modal fade" id="zoneModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="zoneForm" class="modal-content">
      @csrf
      <input type="hidden" id="zone_id" name="zone_id">

      <div class="modal-header">
        <h5 class="modal-title">Shop Zone Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {{-- Country ID --}}
        <div class="mb-3">
          <label for="zone_country_id" class="form-label">Country ID <span class="text-danger">*</span></label>
          <input type="text" id="zone_country_id" name="zone_country_id" class="form-control">
          <div id="error_zone_country_id" class="text-danger"></div>
        </div>

        {{-- Zone Code --}}
        <div class="mb-3">
          <label for="zone_code" class="form-label">Zone Code <span class="text-danger">*</span></label>
          <input type="text" id="zone_code" name="zone_code" class="form-control">
          <div id="error_zone_code" class="text-danger"></div>
        </div>

        {{-- Zone Name --}}
        <div class="mb-3">
          <label for="zone_name" class="form-label">Zone Name <span class="text-danger">*</span></label>
          <input type="text" id="zone_name" name="zone_name" class="form-control">
          <div id="error_zone_name" class="text-danger"></div>
        </div>

        {{-- Generic form error --}}
        <div id="formError" class="text-danger mt-2"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="saveZoneBtn">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- Page-specific JavaScript --}}
<script>
  
   $(document).ready(function() {
    // Initialize Toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };
 const zonesTable = $('#zonesTable').DataTable({
        ajax: "{{ route('shopzones.index') }}",
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'zone_id' },
            { data: 'zone_country_id' },
            { data: 'zone_code' },
            { data: 'zone_name' }
        ]
    });

    // Toggle expand row for Shop Zones
    $('#zonesTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = zonesTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editZoneBtn" data-id="${data.zone_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteZoneBtn" data-id="${data.zone_id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    const zoneModal = new bootstrap.Modal(document.getElementById('zoneModal'));

    function clearZoneValidation(){
        $('#zoneForm .form-control').removeClass('is-invalid');
        $('[id^="error_"]').text('');
        $('#formError').text('');
    }

    $('#createNewZone').click(function(){
        clearZoneValidation();
        $('#zoneForm')[0].reset();
        $('#zone_id').val('');
        $('#saveZoneBtn').text('Save');
        zoneModal.show();
    });

    $('#zoneForm').submit(function(e){
        e.preventDefault();
        clearZoneValidation();

        const id = $('#zone_id').val();
        const url = id
            ? `/admin/shop-zones/${id}`
            : `{{ route('shopzones.store') }}`;
        let payload = $(this).serialize();
        if(id) payload += '&_method=PUT';

        $.ajax({
            url: url,
            type: 'POST',
            data: payload,
            success: res => {
                toastr.success(res.success);
                zoneModal.hide();
                zonesTable.ajax.reload();
            },
            error: xhr => {
                if(xhr.status === 422){
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#error_${field}`).text(errors[field][0]);
                    });
                } else {
                    $('#formError').text('Something went wrong.');
                    toastr.error('Error saving data');
                }
            }
        });
    });

    $(document).on('click', '.editZoneBtn', function(){
        clearZoneValidation();
        const id = $(this).data('id');
        $.get(`/admin/shop-zones/${id}/edit`, function(data){
            $('#zone_id').val(data.zone_id);
            $('#zone_country_id').val(data.zone_country_id);
            $('#zone_code').val(data.zone_code);
            $('#zone_name').val(data.zone_name);
            $('#saveZoneBtn').text('Update');
            zoneModal.show();
        }).fail(() => {
            $('#formError').text('Failed to fetch data.');
        });
    });

    $(document).on('click', '.deleteZoneBtn', function(){
        if(!confirm('क्या आप वाकई इसे डिलीट करना चाहते हैं?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: `/admin/shop-zones/${id}`,
            type: 'DELETE',
            data: {_token: '{{ csrf_token() }}'},
            success: res => {
                toastr.success(res.success);
                zonesTable.ajax.reload();
            },
            error: () => {
                toastr.error('Failed to delete');
            }
        });
    });
});
</script>
@endsection
