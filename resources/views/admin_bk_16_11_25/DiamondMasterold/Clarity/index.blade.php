@extends('admin.layouts.master')

@section('main_section')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4 class="mb-3">Diamond Clarity Master</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#clarityModal" id="addClarityBtn">
                Add New
            </button>
        </div>

        <div class="table-responsive text-nowrap card-body">
            <table class="table table-hover" id="clarityTable">
                <thead>
                    <tr class="bg-light">
                        <th>ID</th>
                        <th>Name</th>
                        <th>Alias</th>
                        <th>Remark</th>
                        <th>Display In Front</th>
                        <th>Sort Order</th>
                        <th>Date Added</th>
                        <th>Date Modify</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Records will be loaded by JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="clarityModal" tabindex="-1" aria-labelledby="clarityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="clarityForm">
            @csrf
            <input type="hidden" id="record_id" name="id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Clarity Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-2">
                    <div class="col-12">
                        <label>Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-6">
                        <label>Alias</label>
                        <input type="text" class="form-control" id="ALIAS" name="ALIAS">
                    </div>
                    <div class="col-6">
                        <label>Remark</label>
                        <input type="text" class="form-control" id="remark" name="remark">
                    </div>
                    <div class="col-6">
                        <label>Display In Front</label>
                        <input type="number" class="form-control" id="display_in_front" name="display_in_front">
                    </div>
                    <div class="col-6">
                        <label>Sort Order</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order">
                    </div>
                    <div class="col-6">
                        <label>Date Added</label>
                        <input type="datetime-local" class="form-control" id="date_added" name="date_added">
                    </div>
                    <div class="col-6">
                        <label>Date Modify</label>
                        <input type="datetime-local" class="form-control" id="date_modify" name="date_modify">
                    </div>
                    <div id="formError" class="text-danger mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary me-2" id="saveShadeBtn">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function formatDateForInput(dateString) {
    if (!dateString) return "";
    // Convert the date string to Date object
    let dt = new Date(dateString);
    // Pad month, day, hours and minutes with leading zeros if needed
    let year = dt.getFullYear();
    let month = ("0" + (dt.getMonth() + 1)).slice(-2);
    let day = ("0" + dt.getDate()).slice(-2);
    let hours = ("0" + dt.getHours()).slice(-2);
    let minutes = ("0" + dt.getMinutes()).slice(-2);
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

$(document).ready(function() {
    fetchRecords();

    function fetchRecords() {
        $.get("{{ route('clarity.index') }}", function(response) {
            let rows = '';
            response.forEach(r => {
                rows += `
                <tr>
                    <td>${r.id}</td>
                    <td>${r.name ?? ''}</td>
                    <td>${r.ALIAS ?? ''}</td>
                    <td>${r.remark ?? ''}</td>
                    <td>${r.display_in_front ?? ''}</td>
                    <td>${r.sort_order ?? ''}</td>
                    <td>${r.date_added ? r.date_added.substring(0, 10) : ''}</td>
                    <td>${r.date_modify ? r.date_modify.substring(0, 10) : ''}</td>
                    <td>
                        <button class="btn btn-sm btn-info editBtn" data-id="${r.id}"><i class="fa fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.id}"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
                `;
            });
            renderDataTable('clarityTable', rows);
        });
    }

    // Clear form on modal open for new record
    $('#addClarityBtn').click(function() {
        $('#clarityForm')[0].reset();
        $('#record_id').val('');
        $('#formError').text('');
        $('#saveShadeBtn').text('Save');
    });

    // On edit button click, fetch record data and populate the modal
    $(document).on('click', '.editBtn', function() {
        let id = $(this).data('id');
        $.get("{{ url('admin/clarity') }}/" + id, function(data) {
            $('#clarityForm')[0].reset();
            $('#formError').text('');
            $('#record_id').val(data.id);
            $('#name').val(data.name);
            $('#ALIAS').val(data.ALIAS);
            $('#remark').val(data.remark);
            $('#display_in_front').val(data.display_in_front);
            $('#sort_order').val(data.sort_order);
            $('#date_added').val(formatDateForInput(data.date_added));
            $('#date_modify').val(formatDateForInput(data.date_modify));
            $('#saveShadeBtn').text('Update')
            $('#clarityModal').modal('show');
        });
    });

    $('#clarityForm').submit(function(e) {
        e.preventDefault();
        const id = $('#record_id').val();
        const method = id ? 'PUT' : 'POST';
        const url = id 
                    ? `{{ url('admin/clarity') }}/${id}` 
                    : "{{ route('clarity.store') }}";
        
        let formData = $(this).serialize();
        formData += `&_method=${method}`;

        $.ajax({
            url: url,
            type: 'POST', 
            data: formData,
            success: function(res) {
                $('#clarityModal').modal('hide');
                fetchRecords();
                toastr.success("Record saved successfully!");
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors || {};
                let msg = Object.values(errors).join('<br>');
                $('#formError').html(msg || 'An error occurred');
                toastr.error("Failed to save record!");
            }
        });
    });

    $(document).on('click', '.deleteBtn', function() {
        const id = $(this).data('id');
        if (confirm("Are you sure you want to delete this record?")) {
            $.ajax({
                url: `{{ url('admin/clarity') }}/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function() {
                    fetchRecords();
                    toastr.success("Record deleted successfully!");
                }
            });
        }
    });
});
</script>
@endsection
