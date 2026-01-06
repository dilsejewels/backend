@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Fancy Color Overtones</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#girdleModal"
                    id="addgirdleBtn">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="girdleTable">
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
    <div class="modal fade" id="girdleModal" tabindex="-1" aria-labelledby="girdleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="girdleForm">
                @csrf
                <input type="hidden" id="record_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">girdle Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-2">
                        <input type="hidden" id="record_id" name="record_id">
                        <div class="col-12">
                            <label>Name</label>
                            <input type="text" class="form-control" id="dg_name" name="dg_name">
                        </div>

                        <div class="col-6">
                            <label>Alias</label>
                            <input type="text" class="form-control" id="dg_alise" name="dg_alise">
                        </div>

                        <div class="col-6">
                            <label>Short Name</label>
                            <input type="text" class="form-control" id="dg_short_name" name="dg_short_name">
                        </div>

                        <div class="col-12">
                            <label>Remark</label>
                            <input type="text" class="form-control" id="dg_remark" name="dg_remark">
                        </div>

                        <div class="col-6">
                            <label>Display in Front</label>
                            <select class="form-control" id="dg_display_in_front" name="dg_display_in_front">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label>Sort Order</label>
                            <input type="number" class="form-control" id="dg_sort_order" name="dg_sort_order">
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
                        <button type="submit" class="btn btn-primary me-2" id="savegirdleBtn">Save</button>
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
                $.get("{{ route('girdle.index') }}", function(response) {
                    let rows = '';
                    response.forEach(r => {
                        rows += `
                            <tr>
                                <td>${r.dg_id}</td>
                                <td>${r.dg_name ?? ''}</td>
                                <td>${r.dg_short_name ?? ''}</td>
                                <td>${r.dg_alias ?? ''}</td>
                                <td>${r.dg_remark ?? ''}</td>
                                <td>
                        <input type="checkbox" ${r.dg_display_in_front == 1 ? 'checked' : ''} class="dg_display_in_front" data-id="${r.dg_id}">
                    </td>
                   
                   <td>
                     <input type="number" value="${r.dg_sort_order}" class="sort-order" data-id="${r.dg_id}" style="width: 60px;">
                    </td>

                                <td>${r.date_added ? r.date_added.substring(0, 10) : ''}</td>
                                <td>${r.date_modify ? r.date_modify.substring(0, 10) : ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editBtn" data-id="${r.dg_id}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.dg_id}"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    renderDataTable('girdleTable', rows); 
                });
            }

            $('#addgirdleBtn').on('click', function() {
                $('#girdleForm')[0].reset();
                $('#record_id').val('');
                $('#formError').text('');
                $('#savegirdleBtn').text('Save');
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                $.get("{{ url('admin/girdle') }}/" + id, function(data) {
                    $('#girdleForm')[0].reset();
                    $('#formError').text('');
                    $('#record_id').val(data.dg_id);
                    $('#dg_name').val(data.dg_name);
                    $('#dg_alise').val(data.dg_alise);
                    $('#dg_short_name').val(data.dg_short_name);
                    $('#dg_remark').val(data.dg_remark);
                    $('#dg_display_in_front').val(data.dg_display_in_front);
                    $('#dg_sort_order').val(data.dg_sort_order);
                    $('#date_added').val(formatDateForInput(data.date_added));
                    $('#date_modify').val(formatDateForInput(data.date_modify));
                    $('#girdleModal').modal('show');
                    $('#savegirdleBtn').text('Update');
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                if (confirm("Are you sure you want to delete this record?")) {
                    $.ajax({
                        url: `/admin/girdle/${id}`,
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

            $('#girdleForm').submit(function(e) {
                e.preventDefault();
                const id = $('#record_id').val();

                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/girdle') }}/${id}` :
                    "{{ route('girdle.store') }}";
                let formData = $(this).serialize();
                formData += `&_method=${method}`;
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function() {
                        $('#girdleModal').modal('hide');
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
            $(document).on('change', '.dg_display_in_front', function() {
                const id = $(this).data('id');
                const status = $(this).prop('checked') ? 1 : 0;

                $.ajax({
                    url: `{{ url('admin/girdle') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        dg_display_in_front: status
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
                    url: `{{ url('admin/girdle') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        dg_sort_order: sortOrder
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
