@extends('admin.layouts.master')

@section('main_section')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h4>Shop Zone to Geo Zone Management</h4>
      <button class="btn btn-primary btn-sm" id="addBtn">Add New</button>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-bordered" id="geoTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Country</th>
            <th>Zone</th>
            <th>Geo Zone</th> 
            <th>Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="geoModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="geoForm" class="modal-content">
      @csrf
      <input type="hidden" id="record_id">
      <div class="modal-header">
        <h5 class="modal-title">Geo Zone Assignment Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Country <span class="text-danger">*</span></label>
          <select name="country_id" id="country_id" class="form-select">
            <option value="">Select</option>
            @foreach($countries as $country)
              <option value="{{ $country->country_id }}">{{ $country->country_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-country_id"></small>
        </div>

        <div class="mb-3">
          <label>Zone <span class="text-danger">*</span></label>
          <select name="zone_id" id="zone_id" class="form-select">
            <option value="">Select</option>
            @foreach($zones as $zone)
              <option value="{{ $zone->zone_id }}">{{ $zone->zone_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-zone_id"></small>
        </div>

        <div class="mb-3">
          <label>Geo Zone <span class="text-danger">*</span></label>
          <select name="geo_zone_id" id="geo_zone_id" class="form-select">
            <option value="">Select</option>
            @foreach($geoZones as $gz)
              <option value="{{ $gz->geo_zone_id }}">{{ $gz->geo_zone_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-geo_zone_id"></small>
        </div>

        <div id="formError" class="text-danger mt-2"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Scripts -->
<script>
$(function () {
  const geoModal = new bootstrap.Modal(document.getElementById('geoModal'));
  let table = $('#geoTable').DataTable({
    ajax: '{{ route("geo-zones.fetch") }}',
    columns: [
      { data: null, render: (d, t, r, m) => m.row + 1 },
      { data: 'country.country_name' },
      { data: 'zone.zone_name' },
      { data: 'geo_zone.geo_zone_name' },
      {
        data: null,
        render: row => `
          <button class="btn btn-sm btn-info editBtn" data-id="${row.association_id}">Edit</button>
          <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.association_id}">Delete</button>
        `
      }
    ]
  });

  function clearForm() {
    $('#geoForm')[0].reset();
    $('#record_id').val('');
    $('.text-danger').text('');
    $('.form-select').removeClass('is-invalid');
    $('#formError').text('');
  }

  $('#addBtn').click(() => {
    clearForm();
    geoModal.show();
  });

  $('#geoForm').submit(function (e) {
    e.preventDefault();
    $('.text-danger').text('');
    $('.form-select').removeClass('is-invalid');

    const id = $('#record_id').val();
    const url = id ? `/admin/geo-zones/update/${id}` : `{{ route('geo-zones.store') }}`;

    $.ajax({
      url,
      method: 'POST',
      data: $(this).serialize(),
      success: res => {
        geoModal.hide();
        $('#geoTable').DataTable().ajax.reload();
        toastr.success(res.success);
      },
      error: xhr => {
        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;
          $.each(errors, (key, val) => {
            $(`#${key}`).addClass('is-invalid');
            $(`.error-${key}`).text(val[0]);
          });
        } else {
          $('#formError').text('An unexpected error occurred.');
          toastr.error('Could not save data.');
        }
      }
    });
  });

  $(document).on('click', '.editBtn', function () {
    clearForm();
    const id = $(this).data('id');
    $.get(`/admin/geo-zones/show/${id}`, res => {
      $('#record_id').val(res.association_id);
      $('#country_id').val(res.country_id);
      $('#zone_id').val(res.zone_id);
      $('#geo_zone_id').val(res.geo_zone_id);
      geoModal.show();
    });
  });

  $(document).on('click', '.deleteBtn', function () {
    if (!confirm('Are you sure you want to delete this record?')) return;
    const id = $(this).data('id');
    $.ajax({
      url: `/admin/geo-zones/destroy/${id}`,
      method: 'DELETE',
      data: { _token: '{{ csrf_token() }}' },
      success: res => {
        $('#geoTable').DataTable().ajax.reload();
        toastr.success(res.success);
      },
      error: () => toastr.error('Failed to delete record.')
    });
  });
});
</script>
@endsection
