@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Color Master</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#colorModal" id="addColorBtn">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="colorTable">
                    <thead>
                        <tr class="bg-light">
                            <th>ID</th>
                            <th>Name</th>
                            <th>Alias</th>
                            <th>Short Name</th>
                            <th>Display In Front</th>
                            <th>Fancy Color?</th>
                            <th>Sort Order</th>
                            <th>Date Added</th>
                            <th>Date Modify</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Records loaded via JS --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="colorModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="colorForm">
                @csrf
                <input type="hidden" id="editId" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Color Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-2">
                        <div class="col-12">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" >
                              <div class="text-danger mt-1" id="error-name"></div>
                        </div>
                        <div class="col-6">
                            <label>Alias</label>
                            <input type="text" class="form-control" name="ALIAS">
                        </div>
                        <div class="col-6">
                            <label>Short Name</label>
                            <input type="text" class="form-control" name="short_name">
                        </div>
                        <div class="col-12">
                            <label>Remark</label>
                            <input type="text" class="form-control" name="remark">
                        </div>

                        <div class="col-6">
                            <label>Display In Front</label>
                            <select class="form-control" id="display_in_front" name="display_in_front">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="col-6 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="dc_is_fancy_color"
                                    name="dc_is_fancy_color" value="1">
                                <label class="form-check-label" for="dc_is_fancy_color">Fancy Color?</label>
                            </div>
                        </div>

                        <div class="col-6">
                            <label>Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order">
                        </div>

                        <div id="formError" class="text-danger mt-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="saveColorBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        $(document).ready(function() {
            fetchColors();

            function fetchColors() {
                $.get("{{ route('color.index') }}", function(response) {
                    let rows = '';
                    response.forEach(r => {
                        rows += `
                    <tr>
                        <td>${r.id}</td>
                        <td>${r.name ?? ''}</td>
                        <td>${r.ALIAS ?? ''}</td>
                        <td>${r.short_name ?? ''}</td>
                        <td><input type="checkbox" ${r.display_in_front == 1 ? 'checked' : ''} class="display_in_front" data-id="${r.id}"></td>
                        <td>${r.dc_is_fancy_color == 1 ? 'Yes' : 'No'}</td>
                        <td><input type="number" value="${r.sort_order}" class="sort-order" data-id="${r.id}" style="width: 60px;"></td>
                         <td>${r.date_added ? r.date_added : ''}</td>
                    <td>${r.date_modify ? r.date_modify: ''}</td>
                        <td>
                            <button class="btn btn-sm btn-info editBtn" data-id="${r.id}"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.id}"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                    });
                    renderDataTable('colorTable', rows);
                });
            }

            $('#addColorBtn').click(function() {
                $('#colorForm')[0].reset();
                $('#editId').val('');
                 $('#saveColorBtn').text('Save');
                $('#formError').text('');
            });

            $(document).on('click', '.editBtn', function() {
                const id = $(this).data('id');
                $.get("{{ url('admin/diamond-color') }}/" + id, function(data) {
                    $('#colorForm')[0].reset();
                    $('#editId').val(data.id);
                    $('input[name="name"]').val(data.name);
                    $('input[name="ALIAS"]').val(data.ALIAS);
                    $('input[name="short_name"]').val(data.short_name);
                    $('input[name="remark"]').val(data.remark);
                    $('#display_in_front').val(data.display_in_front);
                    $('#dc_is_fancy_color').prop('checked', data.dc_is_fancy_color == 1);
                    $('#sort_order').val(data.sort_order);
                    $('#formError').text('');
                    $('#saveColorBtn').text('Update')
                     $('#colorForm .text-danger').html('');
                    $('#colorModal').modal('show');
                });
            });

            $('#colorForm').submit(function(e) {
                e.preventDefault();
                const id = $('#editId').val();
                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/diamond-color') }}/${id}` :
                    `{{ route('color.store') }}`;

                // Create FormData object
                const formData = new FormData(this);
                formData.append('_method', method);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#colorModal').modal('hide');
                        fetchColors();
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors || {};
                         $('#colorForm .text-danger').html('');
                        for (let field in errors) {
                            $(`#error-${field}`).html(errors[field][0]);
                        }
                        toastr.error("Failed to save record!");
                    }
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
                        url: `/api/admin/diamond-color/${deleteId}`,
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
        });
        $(document).on('blur', '.sort-order', function() {
                const id = $(this).data('id');
                const sortOrder = $(this).val();

                $.ajax({
                    url: `{{ url('admin/diamond-color') }}/${id}`,
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


        $(document).on('change', '.display_in_front', function() {
            const id = $(this).data('id');
            const status = $(this).prop('checked') ? 1 : 0;

            $.ajax({
                url: `{{ url('admin/diamond-color') }}/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    display_in_front: status
                },
                success: function() {
                    toastr.success('Status updated successfully!');
                },
                error: function() {
                    toastr.error('Failed to update Status!');
                }
            });
        });
    </script>
@endsection
