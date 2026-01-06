@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Fancy Color Overtones</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#fancyColorModal"
                    id="addfancyColorBtn">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="fancyColorTable">
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
    <div class="modal fade" id="fancyColorModal" tabindex="-1" aria-labelledby="fancyColorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="fancyColorForm">
                @csrf
                <input type="hidden" id="record_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">fancyColor Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-2">
                        <input type="hidden" id="record_id" name="record_id">
                        <div class="col-12">
                            <label>Name</label>
                            <input type="text" class="form-control" id="fco_name" name="fco_name">
                            <div class="text-danger mt-1" id="error-fco_name"></div>
                        </div>

                        <div class="col-6">
                            <label>Alias</label>
                            <input type="text" class="form-control" id="fco_alise" name="fco_alise">
                        </div>

                        <div class="col-6">
                            <label>Short Name</label>
                            <input type="text" class="form-control" id="fco_short_name" name="fco_short_name">
                        </div>

                        <div class="col-12">
                            <label>Remark</label>
                            <input type="text" class="form-control" id="fco_remark" name="fco_remark">
                        </div>

                        <div class="col-6">
                            <label>Display in Front</label>
                            <select class="form-control" id="fco_display_in_front" name="fco_display_in_front">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label>Sort Order</label>
                            <input type="number" class="form-control" id="fco_sort_order" name="fco_sort_order">
                        </div>
                        <div id="formError" class="text-danger mt-2"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="savefancyColorBtn">Save</button>
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
                $.get("{{ route('fancyColor.index') }}", function(response) {
                    let rows = '';
                    response.forEach(r => {
                        rows += `
                            <tr>
                                <td>${r.fco_id}</td>
                                <td>${r.fco_name ?? ''}</td>
                                <td>${r.fco_short_name ?? ''}</td>
                                <td>${r.fco_alias ?? ''}</td>
                                <td>${r.fco_remark ?? ''}</td>
                                <td>
                        <input type="checkbox" ${r.fco_display_in_front == 1 ? 'checked' : ''} class="fco_display_in_front" data-id="${r.fco_id}">
                    </td>
                   
                   <td>
                     <input type="number" value="${r.fco_sort_order}" class="sort-order" data-id="${r.fco_id}" style="width: 60px;">
                    </td>

                                <td>${r.date_added ? r.date_added: ''}</td>
                                <td>${r.date_modify ? r.date_modify: ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editBtn" data-id="${r.fco_id}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.fco_id}"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    renderDataTable('fancyColorTable', rows);                   
                });
            }

            $('#addfancyColorBtn').on('click', function() {
                $('#fancyColorForm')[0].reset();
                $('#record_id').val('');
                $('#formError').text('');
                $('#savefancyColorBtn').text('Save');
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                $.get("{{ url('admin/fancyColor') }}/" + id, function(data) {
                    $('#fancyColorForm')[0].reset();
                    $('#formError').text('');
                    $('#record_id').val(data.fco_id);
                    $('#fco_name').val(data.fco_name);
                    $('#fco_alise').val(data.fco_alise);
                    $('#fco_short_name').val(data.fco_short_name);
                    $('#fco_remark').val(data.fco_remark);
                    $('#fco_display_in_front').val(data.fco_display_in_front);
                    $('#fco_sort_order').val(data.fco_sort_order);
                    $('#fancyColorModal').modal('show');
                    $('#fancyColorForm .text-danger').html('');
                    $('#savefancyColorBtn').text('Update');
                });
            });

            $(document).ready(function() {
                let deleteId = null;
                let $currentRow = null;

                $(document).on('click', '.deleteBtn', function() {

                    deleteId = $(this).data('id');
                    $currentRow = $(this).closest('tr');
                    $('.popup-modal.remove-modal').fadeIn(); // Show the modal
                });

                // Close modal on No or overlay click
                $(document).on('click', '.close-pop', function() {
                    $('.popup-modal.remove-modal').fadeOut(); // Hide the modal
                });

                // Confirm delete
                $('#confirmDelete').on('click', function() {
                    if (!deleteId) return;

                    $.ajax({
                        url: `/api/admin/fancyColor/${deleteId}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                $currentRow.remove();
                                toastr.success(response.message);
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            } else {
                                toastr.error("Unexpected server response.");
                            }
                            $('.popup-modal.remove-modal').fadeOut(); // Close the modal
                        },
                        error: function(xhr) {
                            toastr.error("Failed to delete the record.");
                            $('.popup-modal.remove-modal').fadeOut(); // Close the modal
                        }
                    });
                });
            });

            $('#fancyColorForm').submit(function(e) {
                e.preventDefault();
                const id = $('#record_id').val();
                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/fancyColor') }}/${id}` :
                    "{{ route('fancyColor.store') }}";
                let formData = $(this).serialize();
                formData += `&_method=${method}`;
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function() {
                        $('#fancyColorModal').modal('hide');
                        fetchRecords();
                        toastr.success("Record saved successfully!");
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors || {};
                        $('#fancyColorForm .text-danger').html('');
                    for (let field in errors) {
                        $(`#error-${field}`).html(errors[field][0]);
                    }
                        toastr.error("Failed to save record!");
                    }
                });
            });
            $(document).on('change', '.fco_display_in_front', function() {
                const id = $(this).data('id');
                const status = $(this).prop('checked') ? 1 : 0;

                $.ajax({
                    url: `{{ url('admin/fancyColor') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        fco_display_in_front: status
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
                    url: `{{ url('admin/fancyColor') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        fco_sort_order: sortOrder
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
