@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Polish Master</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#polishModal" id="addPolishBtn">
                    Add New
                </button>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover" id="polishTable">
                        <thead>
                            <tr class="bg-light">
                                <th>ID</th>
                                <th>Name</th>
                                <th>Alias</th>
                                <th>Short Name</th>
                                <th>Full Name</th>
                                <th>Status</th>
                                <th>Sort Order</th>
                                <th>Date Added</th>
                                <th>Date updated</th>
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

    <!-- Modal -->
    <div class="modal fade" id="polishModal" tabindex="-1" aria-labelledby="polishModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="polishForm">
                @csrf
                <input type="hidden" id="record_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Polish Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label>Name</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>
                        <div class="mb-3"><label>Alias</label>
                            <input type="text" class="form-control" name="alias" id="alias">
                        </div>
                        <div class="mb-3"><label>Short Name</label>
                            <input type="text" class="form-control" name="short_name" id="short_name">
                        </div>
                        <div class="mb-3"><label>Full Name</label>
                            <input type="text" class="form-control" name="full_name" id="full_name">
                        </div>
                        <div class="mb-3"><label>Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" id="sort_order">
                        </div>
                        <div class="mb-3"><label>Status</label>
                            <select class="form-control" name="pol_status" id="pol_status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div id="formError" class="text-danger"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="savePolishBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            fetchPolishes();

            function fetchPolishes() {
                $.get("{{ route('diamondpolish.index') }}", function(response) {
                    let rows = '';
                    response.forEach((record) => {
                        rows += `
          <tr>
            <td>${record.id}</td>
            <td>${record.name}</td>
            <td>${record.alias ?? ''}</td>
            <td>${record.short_name ?? ''}</td>
            <td>${record.full_name ?? ''}</td>
            <td>
            <input type="checkbox" ${record.pol_status == 1 ? 'checked' : ''} class="status" data-id="${record.id}">
             </td>
            <td><input type="number" value="${record.sort_order}" class="sort-order" data-id="${record.id}" style="width: 60px;"></td>
            <td>${record.date_added ? record.date_added : ''}</td>
             <td>${record.date_modify ? record.date_modify : ''}</td>
            <td>
              <button class="btn btn-sm btn-info editBtn" data-id="${record.id}"><i class="fa fa-edit"></i></button>
              <button class="btn btn-sm btn-danger deleteBtn" data-id="${record.id}"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
        `;
                    });
                    renderDataTable('polishTable', rows);
                });
            }

            $('#addPolishBtn').click(function() {
                $('#polishForm')[0].reset();
                $('#record_id').val('');
                $('#formError').text('');
                $('#savePolishBtn').text('Save'); // change to "Save"
            });

            $(document).on('click', '.editBtn', function() {
                const id = $(this).data('id');
                $.get("{{ url('admin/polish') }}/" + id, function(data) {
                    $('#record_id').val(data.id);
                    $('#name').val(data.name);
                    $('#alias').val(data.alias);
                    $('#short_name').val(data.short_name);
                    $('#full_name').val(data.full_name);
                    $('#sort_order').val(data.sort_order);
                    $('#pol_status').val(data.pol_status);
                    $('#formError').text('');
                    $('#savePolishBtn').text('Update'); // change to "Update"
                    $('#polishModal').modal('show');
                });
            });

            $('#polishForm').submit(function(e) {
                e.preventDefault();
                const id = $('#record_id').val();
                const method = id ? 'PUT' : 'POST';
                const url = id ? `{{ url('admin/polish') }}/${id}` : `{{ route('diamondpolish.store') }}`;

                let formData = $(this).serialize();
                formData += `&_method=${method}`;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function() {
                        $('#polishModal').modal('hide');
                        fetchPolishes();
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

            $(document).on('change', '.status', function() {
                const id = $(this).data('id');
                const status = $(this).prop('checked') ? 1 : 0;

                $.ajax({
                    url: `{{ url('admin/polish') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        pol_status: status
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
                    url: `{{ url('admin/polish') }}/${id}`,
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

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                if (confirm('Are you sure you want to delete this record?')) {
                    $.ajax({
                        url: `{{ url('admin/polish') }}/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function() {
                            fetchPolishes();
                            toastr.success("Record deleted successfully!");
                        }
                    });
                }
            });
        });
    </script>
@endsection
