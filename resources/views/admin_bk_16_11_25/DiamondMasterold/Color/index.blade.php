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
                            <input type="text" class="form-control" name="name" required>
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

                        <div class="col-6 d-flex align-items-center">
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
                            <input type="number" class="form-control" name="sort_order">
                        </div>

                        <div id="formError" class="text-danger mt-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2">Save</button>
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
                        let msg = Object.values(errors).join('<br>');
                        $('#formError').html(msg || 'An error occurred');
                    }
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                if (confirm("Are you sure?")) {
                    $.ajax({
                        url: `{{ url('admin/diamond-color') }}/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function() {
                            fetchColors();
                        }
                    });
                }
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
