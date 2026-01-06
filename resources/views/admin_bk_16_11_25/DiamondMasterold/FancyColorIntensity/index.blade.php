@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Fancy Color Intensity Master</h4>
                <button class="btn btn-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#intensityModal">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="dataTable">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Short Name</th>
                            <th>Alias</th>
                            <th>Display Front</th>
                            <th>Sort Order</th>
                            <th>Date Added</th>
                            <th>Date Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data loaded via AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="intensityModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="mainForm">
                @csrf
                <input type="hidden" id="editId" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Intensity Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-2">
                        <div class="col-12">
                            <label>Name</label>
                            <input type="text" class="form-control" name="fci_name">
                        </div>
                        <div class="col-6">
                            <label>Short Name</label>
                            <input type="text" class="form-control" name="fci_short_name">
                        </div>
                        <div class="col-6">
                            <label>Alias</label>
                            <input type="text" class="form-control" name="fci_alias">
                        </div>
                        <div class="col-12">
                            <label>Remark</label>
                            <textarea class="form-control" name="fci_remark"></textarea>
                        </div>
                        <div class="col-6">
                
                                <label>Display in Front</label>
                                <select class="form-control" id="fci_display_in_front" name="fci_display_in_front">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            
                        </div>
                        <div class="col-6">
                            <label>Sort Order</label>
                            <input type="number" class="form-control" name="fci_sort_order">
                        </div>
                        <div class="col-6">
                            <label>Date Added</label>
                            <input type="datetime-local" class="form-control" name="date_added">
                        </div>
                        <div class="col-6">
                            <label>Date Modify</label>
                            <input type="datetime-local" class="form-control" name="date_modify">
                        </div>
                        <div id="formError" class="text-danger mt-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="saveColorIntensityBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
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

        $(document).ready(function() {
            fetchRecords();

            function fetchRecords() {
                $.get("{{ route('fancy-color-intensity.index') }}", function(response) {
                    let rows = '';
                    response.data.forEach(r => {
                        rows += `
                            <tr>
                                <td>${r.fci_id}</td>
                                <td>${r.fci_name ?? ''}</td>
                                <td>${r.fci_short_name ?? ''}</td>
                                <td>${r.fci_alias ?? ''}</td>
                                <td><input type="checkbox" ${r.fci_display_in_front == 1 ? 'checked' : ''} class="fci_display_in_front" data-id="${r.fci_id}"></td>
                                <td><input type="number" value="${r.fci_sort_order}" class="sort-order" data-id="${r.fci_id}" style="width: 60px;"></td>
                                <td>${r.date_added ? r.date_added.substring(0, 10) : ''}</td>
                                <td>${r.date_modify ? r.date_modify.substring(0, 10) : ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editBtn" data-id="${r.fci_id}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.fci_id}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    renderDataTable('dataTable', rows);
                });
            }

            $('#addBtn').click(function() {
                $('#mainForm')[0].reset();
                $('#editId').val('');
                $('#displayFront').prop('checked', false);
                $('#saveColorIntensityBtn').text('Save');
                $('#formError').text('');
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                $.get(`/admin/diamond-fancy-color-intensity/${id}`, function(data) {
                    $('#editId').val(data.fci_id);
                    $('input[name="fci_name"]').val(data.fci_name);
                    $('input[name="fci_short_name"]').val(data.fci_short_name);
                    $('input[name="fci_alias"]').val(data.fci_alias);
                    $('textarea[name="fci_remark"]').val(data.fci_remark);
                    $('#fci_display_in_front').val(data.fci_display_in_front);
                    $('input[name="fci_sort_order"]').val(data.fci_sort_order);
                    $('input[name="date_added"]').val(formatDateForInput(data.date_added));
                    $('input[name="date_modify"]').val(formatDateForInput(data.date_modify));
                    $('#saveColorIntensityBtn').text('Update');
                    $('#intensityModal').modal('show');
                });
            });

            $('#mainForm').submit(function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = $('#editId').val();
                const url = id ?
                    `/admin/diamond-fancy-color-intensity/${id}` :
                    "{{ route('fancy-color-intensity.store') }}";
                formData.append('_method', id ? 'PUT' : 'POST');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#intensityModal').modal('hide');
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
            $(document).on('change', '.fci_display_in_front', function() {
                const id = $(this).data('id');
                const status = $(this).prop('checked') ? 1 : 0;

                $.ajax({
                    url: `{{ url('admin/diamond-fancy-color-intensity') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        fci_display_in_front: status
                    },
                    success: function() {
                        toastr.success('Status updated successfully!');
                    },
                    error: function() {
                        toastr.error('Failed to update Status!');
                    }
                });
            });


            $(document).on('blur', '.sort-order', function() {
                const id = $(this).data('id');
                const sortOrder = $(this).val();

                $.ajax({
                    url: `{{ url('admin/diamond-fancy-color-intensity') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        fci_sort_order: sortOrder
                    },
                    success: function() {
                        toastr.success('Sort order updated successfully!');
                    },
                    error: function() {
                        toastr.error('Failed to update sort order!');
                    }
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                if (confirm('Are you sure you want to delete this record?')) {
                    $.ajax({
                        url: `/admin/diamond-fancy-color-intensity/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function() {
                            row.remove();
                            toastr.success("Record deleted successfully!");
                            fetchRecords();
                        },
                        error: function() {
                            toastr.error("Failed to delete the record.");
                        }
                    });
                }
            });
        });
    </script>
@endsection
