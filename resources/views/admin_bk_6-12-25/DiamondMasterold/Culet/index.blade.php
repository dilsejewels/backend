@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Culet</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#culetModal" id="addculetBtn">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="culetTable">
                    <thead>
                        <tr class="bg-light">
                            <th>ID</th>
                            <th>Name</th>
                            <th>Short Name</th>
                            <th>Alias</th>
                            <th>Remark</th>
                            <th>Display</th>
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
    <div class="modal fade" id="culetModal" tabindex="-1" aria-labelledby="culetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="culetForm">
                @csrf
                <input type="hidden" id="record_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">culet Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-2">
                        <input type="hidden" id="record_id" name="record_id">
                        <div class="col-12">
                            <label>Name</label>
                            <input type="text" class="form-control" id="dc_name" name="dc_name">
                        </div>

                        <div class="col-6">
                            <label>Alias</label>
                            <input type="text" class="form-control" id="dc_alise" name="dc_alise">
                        </div>

                        <div class="col-6">
                            <label>Short Name</label>
                            <input type="text" class="form-control" id="dc_short_name" name="dc_short_name">
                        </div>

                        <div class="col-12">
                            <label>Remark</label>
                            <input type="text" class="form-control" id="dc_remark" name="dc_remark">
                        </div>

                        <div class="col-6">
                            <label>Display in Front</label>
                            <select class="form-control" id="dc_display_in_front" name="dc_display_in_front">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label>Sort Order</label>
                            <input type="number" class="form-control" id="dc_sort_order" name="dc_sort_order">
                        </div>

                        <div class="col-6">
                            <label>Date Added</label>
                            <input type="datetime-local" class="form-control" id="date_added" name="date_added">
                        </div>

                        <div class="col-6">
                            <label>Date Modified</label>
                            <input type="datetime-local" class="form-control" id="date_modify" name="date_modify">
                        </div>

                        <div id="formError" class="text-danger mt-2"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="saveculetBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                $.get("{{ route('culet.index') }}", function(response) {
                    let rows = '';
                    response.forEach(r => {
                        rows += `
                            <tr>
                                <td>${r.dc_id}</td>
                                <td>${r.dc_name ?? ''}</td>
                                <td>${r.dc_short_name ?? ''}</td>
                                <td>${r.dc_alias ?? ''}</td>
                                <td>${r.dc_remark ?? ''}</td>
                                <td>
                        <input type="checkbox" ${r.dc_display_in_front == 1 ? 'checked' : ''} class="dc_display_in_front" data-id="${r.dc_id}">
                    </td>
                   
                   <td>
                     <input type="number" value="${r.dc_sort_order}" class="sort-order" data-id="${r.dc_id}" style="width: 60px;">
                    </td>

                                <td>${r.date_added ? r.date_added.substring(0, 10) : ''}</td>
                                <td>${r.date_modify ? r.date_modify.substring(0, 10) : ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editBtn" data-id="${r.dc_id}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.dc_id}"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    renderDataTable('culetTable', rows);
                });
            }

            $('#addculetBtn').on('click', function() {
                $('#culetForm')[0].reset();
                $('#record_id').val('');
                $('#formError').text('');
                $('#saveculetBtn').text('Save');
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                $.get("{{ url('admin/culet') }}/" + id, function(data) {
                    $('#culetForm')[0].reset();
                    $('#formError').text('');
                    $('#record_id').val(data.dc_id);
                    $('#dc_name').val(data.dc_name);
                    $('#dc_alise').val(data.dc_alise);
                    $('#dc_short_name').val(data.dc_short_name);
                    $('#dc_remark').val(data.dc_remark);
                    $('#dc_display_in_front').val(data.dc_display_in_front);
                    $('#dc_sort_order').val(data.dc_sort_order);
                    $('#date_added').val(formatDateForInput(data.date_added));
                    $('#date_modify').val(formatDateForInput(data.date_modify));
                    $('#culetModal').modal('show');
                    $('#saveculetBtn').text('Update');
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                if (confirm("Are you sure you want to delete this record?")) {
                    $.ajax({
                        url: `/admin/culet/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
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

            $('#culetForm').submit(function(e) {
                e.preventDefault();
                const id = $('#record_id').val();

                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/culet') }}/${id}` :
                    "{{ route('culet.store') }}";
                let formData = $(this).serialize();
                formData += `&_method=${method}`;
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function() {
                        $('#culetModal').modal('hide');
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
            $(document).on('change', '.dc_display_in_front', function() {
                const id = $(this).data('id');
                const status = $(this).prop('checked') ? 1 : 0;

                $.ajax({
                    url: `{{ url('admin/culet') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        dc_display_in_front: status
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
                    url: `{{ url('admin/culet') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        dc_sort_order: sortOrder
                    },
                    success: function() {
                        toastr.success('Sort order updated successfully!');
                    },
                    error: function() {
                        toastr.error('Failed to update sort order!');
                    }
                });
            });


        });
    </script>
@endsection
