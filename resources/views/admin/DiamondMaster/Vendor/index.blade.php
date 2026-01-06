@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Vendor Master</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#vendorModal" id="addvendorBtn">
                    Add New Vendor
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="vendorTable">
                    <thead>
                        <tr class="bg-light">
                            <th>ID</th>
                            <th>Company Name</th>
                            <th>Vendor Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this vendor? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Modal -->
    <div class="modal fade" id="vendorModal" tabindex="-1" aria-labelledby="vendorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="vendorForm">
                    @csrf
                    <input type="hidden" id="record_id" name="id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add New Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body row g-3">
                        @php
                            $fields = [
                                ['vendor_company_name', 'Company Name', 'text'],
                                ['vendor_name', 'Vendor Name', 'text'],
                                ['vendor_email', 'Email', 'email'],
                                ['vendor_phone', 'Phone', 'text'],
                                ['vendor_status', 'Status', 'select', ['1' => 'Active', '0' => 'Inactive']],
                            ];
                        @endphp

                        @foreach ($fields as $field)
                            @php
                                $name = $field[0];
                                $label = $field[1];
                                $type = $field[2] ?? 'text';
                                $options = $field[3] ?? [];
                                
                                // Set default values
                                $defaultValue = '';
                                if (in_array($name, ['vendor_status'])) {
                                    $defaultValue = '0';
                                }
                            @endphp

                            <div class="col-md-12">
                                <label for="{{ $name }}" class="form-label">{{ $label }}</label>

                                @if ($type === 'select')
                                    <select class="form-select" id="{{ $name }}" name="{{ $name }}">
                                        <option value="">-- Select --</option>
                                        @foreach ($options as $val => $option)
                                            <option value="{{ $val }}" {{ $defaultValue == $val ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $type }}" class="form-control" id="{{ $name }}"
                                        name="{{ $name }}" value="{{ old($name, $defaultValue) }}">
                                @endif

                                <small class="text-danger error-{{ $name }}" style="display: none;"></small>
                            </div>
                        @endforeach

                        <div id="formError" class="col-12 text-danger mt-2"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="savevendorBtn">Save Vendor</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .text-danger {
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
    </style>

    <script>
    // Define fields array in JavaScript scope
    const fields = @json($fields);

    $(document).ready(function() {
        let deleteId = null;
        let $currentRow = null;

        // Initialize DataTable
        let dataTable = $('#vendorTable').DataTable({
            processing: true,
            serverSide: false,
            pageLength: 10,
            ordering: true,
            ajax: {
                url: "{{ route('vendor.index') }}",
                dataSrc: ''
            },
            columns: [
                { data: 'vendorid' },
                { data: 'vendor_company_name' },
                { data: 'vendor_name' },
                { data: 'vendor_email' },
                { data: 'vendor_phone' },
                { 
                    data: null,
                    render: function(data, type, row) {
                        return `<input type="checkbox" ${row.vendor_status == 1 ? 'checked' : ''} class="status" data-id="${row.vendorid}">`;
                    }
                },
                { 
                    data: 'date_addded',
                    render: function(data) {
                        return data ? data.substring(0, 10) : '';
                    }
                },
                { 
                    data: 'date_updated',
                    render: function(data) {
                        return data ? data.substring(0, 10) : '';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-info editBtn" data-id="${row.vendorid}"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.vendorid}"><i class="fa fa-trash"></i></button>
                        `;
                    }
                }
            ]
        });

        // Add New Vendor Button
        $('#addvendorBtn').on('click', function() {
            $('#vendorForm')[0].reset();
            $('#record_id').val('');
            $('#formError').text('');
            $('#modalTitle').text('Add New Vendor');
            $('#savevendorBtn').text('Save Vendor');
            
            // Reset all error messages and classes
            $('.form-control, .form-select').removeClass('is-invalid');
            $('.text-danger').hide();
            
            // Set default values
            fields.forEach(field => {
                const name = field[0];
                const type = field[2] || 'text';
                let defaultValue = '';
                
                if (['vendor_status'].includes(name)) {
                    defaultValue = '0';
                }
                
                if (type === 'select') {
                    $(`#${name}`).val(defaultValue);
                } else {
                    $(`#${name}`).val(defaultValue);
                }
            });
        });

        // Edit Vendor
        $(document).on('click', '.editBtn', function() {
            let id = $(this).data('id');
            
            $.ajax({
                url: "{{ url('admin/vendor') }}/" + id,
                type: 'GET',
                success: function(data) {
                    $('#vendorForm')[0].reset();
                    $('#formError').text('');
                    $('#record_id').val(data.vendorid);
                    $('#modalTitle').text('Edit Vendor');
                    $('#savevendorBtn').text('Update Vendor');

                    // Reset all error messages and classes
                    $('.form-control, .form-select').removeClass('is-invalid');
                    $('.text-danger').hide();

                    // Populate only the 5 form fields
                    fields.forEach(field => {
                        const name = field[0];
                        const type = field[2] || 'text';
                        const value = data[name];
                        
                        if (value !== null && value !== undefined) {
                            if (type === 'select') {
                                $(`#${name}`).val(value.toString());
                            } else {
                                $(`#${name}`).val(value);
                            }
                        }
                    });

                    // Show modal
                    const vendorModal = new bootstrap.Modal(document.getElementById('vendorModal'));
                    vendorModal.show();
                },
                error: function() {
                    toastr.error('Failed to load vendor data!');
                }
            });
        });

        // Delete Vendor
        $(document).on('click', '.deleteBtn', function() {
            deleteId = $(this).data('id');
            $currentRow = $(this).closest('tr');
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });

        // Confirm Delete
        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;

            $.ajax({
                url: `{{ url('admin/vendor') }}/${deleteId}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        dataTable.ajax.reload();
                        toastr.success(response.message);
                        
                        // Close delete modal
                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                        deleteModal.hide();
                    } else {
                        toastr.error("Unexpected server response.");
                    }
                },
                error: function(xhr) {
                    toastr.error("Failed to delete the record.");
                }
            });
        });

        // Form submission
        $('#vendorForm').submit(function(e) {
            e.preventDefault();
            const id = $('#record_id').val();

            // Reset previous errors
            $('.form-control, .form-select').removeClass('is-invalid');
            $('.text-danger').hide();
            $('#formError').text('');

            const method = id ? 'PUT' : 'POST';
            const url = id ? 
                `{{ url('admin/vendor') }}/${id}` : 
                "{{ route('vendor.store') }}";
            
            let formData = $(this).serialize();
            
            // Show loading state
            $('#savevendorBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData + `&_method=${method}`,
                success: function(response) {
                    // Close modal
                    const vendorModal = bootstrap.Modal.getInstance(document.getElementById('vendorModal'));
                    vendorModal.hide();
                    
                    // Reload DataTable
                    dataTable.ajax.reload();
                    
                    toastr.success("Vendor saved successfully!");
                    $('#savevendorBtn').prop('disabled', false).html(id ? 'Update Vendor' : 'Save Vendor');
                },
                error: function(xhr) {
                    $('#savevendorBtn').prop('disabled', false).html(id ? 'Update Vendor' : 'Save Vendor');
                    
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors || {};
                        
                        // Display field-specific errors
                        $.each(errors, function(field, messages) {
                            let errorElement = $(`.error-${field}`);
                            let inputElement = $(`[name="${field}"]`);
                            
                            if (errorElement.length && inputElement.length) {
                                errorElement.text(messages[0]).show();
                                inputElement.addClass('is-invalid');
                            }
                        });
                        
                        // Show general errors
                        if (Object.keys(errors).length > 0) {
                            $('#formError').html('Please fix the validation errors above.');
                        }
                    } else {
                        $('#formError').html('An error occurred while saving the vendor.');
                    }
                    
                    toastr.error("Failed to save vendor!");
                }
            });
        });

        // Remove error when user starts typing
        $('.form-control, .form-select').on('input change', function() {
            const fieldName = $(this).attr('name');
            $(this).removeClass('is-invalid');
            $(`.error-${fieldName}`).hide();
        });

        // Status update - ONLY send vendor_status
        $(document).on('change', '.status', function() {
            const id = $(this).data('id');
            const status = $(this).prop('checked') ? 1 : 0;
            const $checkbox = $(this);

            $.ajax({
                url: `{{ url('admin/vendor') }}/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    vendor_status: status
                },
                success: function(response) {
                    toastr.success('Status updated successfully!');
                    dataTable.ajax.reload();
                },
                error: function(xhr) {
                    toastr.error('Failed to update status!');
                    // Revert checkbox on error
                    $checkbox.prop('checked', !status);
                    
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors || {};
                        let errorMsg = Object.values(errors).join(', ');
                        toastr.error('Validation error: ' + errorMsg);
                    }
                }
            });
        });
    });
</script>
@endsection