@extends('admin.layouts.master')

@section('main_section')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Diamond Weight Groups</h4>
            <button class="btn btn-primary" id="addWeightGroupBtn">
                <i class="fas fa-plus me-2"></i>Add New
            </button>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="weightGroupsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>From (ct)</th>
                            <th>To (ct)</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Date Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="weightGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="weightGroupForm">
            @csrf
            <input type="hidden" id="dwg_id" name="dwg_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Weight Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dwg_name" class="form-label">Group Name *</label>
                        <input type="text" class="form-control" id="dwg_name" name="dwg_name" required>
                        <div class="invalid-feedback" id="error_dwg_name"></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dwg_from" class="form-label">From Weight (ct) *</label>
                            <input type="number" step="0.01" class="form-control" id="dwg_from" name="dwg_from" required>
                            <div class="invalid-feedback" id="error_dwg_from"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="dwg_to" class="form-label">To Weight (ct) *</label>
                            <input type="number" step="0.01" class="form-control" id="dwg_to" name="dwg_to" required>
                            <div class="invalid-feedback" id="error_dwg_to"></div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dwg_sort_order" class="form-label">Sort Order *</label>
                            <input type="number" class="form-control" id="dwg_sort_order" name="dwg_sort_order" required>
                            <div class="invalid-feedback" id="error_dwg_sort_order"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="dwg_status" class="form-label">Status *</label>
                            <select class="form-select" id="dwg_status" name="dwg_status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback" id="error_dwg_status"></div>
                        </div>
                    </div>
                    
                    <div id="formError" class="text-danger mb-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this weight group?</p>
                <p class="fw-bold" id="weightGroupNameToDelete"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize Toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": 3000
        };

        // Initialize DataTable
        var table = $('#weightGroupsTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('diamond-weight-groups.getData') }}",
                dataSrc: 'data'
            },
            columns: [
                { data: 'dwg_id' },
                { data: 'dwg_name' },
                { data: 'dwg_from' },
                { data: 'dwg_to' },
                { data: 'dwg_sort_order' },
                { 
                    data: 'dwg_status',
                    render: function(data) {
                        return data ? 
                            '<span class="badge bg-success">Active</span>' : 
                            '<span class="badge bg-danger">Inactive</span>';
                    }
                },
                { 
                    data: 'date_added',
                    render: function(data) {
                        return data ? data.substr(0,10) : '';
                    }
                },
                { 
                    data: 'date_updated',
                    render: function(data) {
                        return data ? data.substr(0,10) : '';
                    }
                },
                {
                    data: 'dwg_id',
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex">
                                <button class="btn btn-sm btn-info editBtn me-2" data-id="${data}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="${data}" data-name="${row.dwg_name}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search weight groups...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        // Reset form and show modal for adding
        $('#addWeightGroupBtn').click(function() {
            $('#weightGroupForm')[0].reset();
            $('#dwg_id').val('');
            $('.invalid-feedback').text('');
            $('#modalTitle').text('Add Weight Group');
            $('#weightGroupModal').modal('show');
        });

        // Edit button click
        $('#weightGroupsTable').on('click', '.editBtn', function() {
            const id = $(this).data('id');
            $.ajax({
                url: "{{ route('diamond-weight-groups.edit', ['id' => ':id']) }}".replace(':id', id),
                type: 'GET',
                success: function(response) {
                    $('#dwg_id').val(response.dwg_id);
                    $('#dwg_name').val(response.dwg_name);
                    $('#dwg_from').val(response.dwg_from);
                    $('#dwg_to').val(response.dwg_to);
                    $('#dwg_sort_order').val(response.dwg_sort_order);
                    $('#dwg_status').val(response.dwg_status);
                    $('#modalTitle').text('Edit Weight Group');
                    $('#weightGroupModal').modal('show');
                },
                error: function() {
                    toastr.error('Failed to load weight group data');
                }
            });
        });

        // Delete button click
        $('#weightGroupsTable').on('click', '.deleteBtn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#confirmDelete').data('id', id);
            $('#weightGroupNameToDelete').text(name);
            $('#deleteModal').modal('show');
        });

        // Confirm delete
        $('#confirmDelete').click(function() {
            const id = $(this).data('id');
            $.ajax({
                url: "{{ route('diamond-weight-groups.destroy', ['id' => ':id']) }}".replace(':id', id),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.success);
                        table.ajax.reload(null, false);
                        $('#deleteModal').modal('hide');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to delete weight group');
                    $('#deleteModal').modal('hide');
                }
            });
        });

        // Form submission
        $('#weightGroupForm').submit(function(e) {
            e.preventDefault();
            $('.invalid-feedback').text('');
            $('#formError').text('');
            
            const formData = $(this).serialize();
            const id = $('#dwg_id').val();
            
            let url, method;
            if (id) {
                url = "{{ route('diamond-weight-groups.update', ['id' => ':id']) }}".replace(':id', id);
                method = 'PUT';
            } else {
                url = "{{ route('diamond-weight-groups.store') }}";
                method = 'POST';
            }
            
            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(response) {
                    toastr.success(response.success);
                    $('#weightGroupModal').modal('hide');
                    table.ajax.reload(null, false);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $('#error_' + field).text(messages[0]);
                        });
                    } else {
                        $('#formError').text('An error occurred. Please try again.');
                    }
                }
            });
        });
    });
</script>
@endsection