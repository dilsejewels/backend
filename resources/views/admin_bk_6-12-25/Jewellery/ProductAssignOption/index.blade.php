@extends('admin.layouts.master')

@section('main_section')
<style>
    table.dataTable td.dt-control:before {
        background: #337ab7;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4 class="mb-3">Product Option Assignment Management</h4>
            <button class="btn btn-primary btn-sm" id="addAssignBtn">Add New</button>
        </div>
        <div class="card-body table-responsive text-nowrap">
            <table class="table table-hover" id="assignTable">
                <thead>
                    <tr class="bg-light">
                        <th>Action</th>
                        <th>Product ID</th>
                        <th>Option ID</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Records will be loaded by JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Option Assignment -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="assignForm" class="modal-content">
            @csrf
            <input type="hidden" id="original_product_id" name="original_product_id">
            <input type="hidden" id="original_option_id" name="original_option_id">
            <div class="modal-header">
                <h5 class="modal-title">Product Option Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Product <span class="text-danger">*</span></label>
                    <select id="products_id" name="products_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($products as $product)
                            <option value="{{ $product->products_id }}">{{ $product->products_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger error-products_id"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Option <span class="text-danger">*</span></label>
                    <select id="options_id" name="options_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($options as $option)
                            <option value="{{ $option->options_id }}">{{ $option->options_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger error-options_id"></small>
                </div>

                <div id="formError" class="text-danger mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
  $(document).ready(function() {
    // Initialize Toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000
    };
 const assignTable = $('#assignTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('assign-option.fetch') }}",
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'products_id', name: 'products_id' },
            // { data: 'product_name', name: 'products_name' },
            { data: 'options_id', name: 'options_id' },
            // { data: 'option_name', name: 'options_name' }
        ],
        order: [[1, 'asc']]
    });

    // Toggle expand row for Option Assignment
    $('#assignTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = assignTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editAssignBtn" 
                        data-pid="${data.products_id}" 
                        data-oid="${data.options_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteAssignBtn" 
                        data-pid="${data.products_id}" 
                        data-oid="${data.options_id}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    // Add new assignment
    $('#addAssignBtn').on('click', function() {
        $('#assignForm')[0].reset();
        $('#original_product_id').val('');
        $('#original_option_id').val('');
        $('#assignModal').modal('show');
    });

    // Save assignment
    $('#assignForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const pid = $('#original_product_id').val();
        const oid = $('#original_option_id').val();
        const url = pid && oid 
            ? `/admin/assign-option/update/${pid}/${oid}`
            : "{{ route('assign-option.store') }}";

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(res) {
                $('#assignModal').modal('hide');
                toastr.success(res.success);
                assignTable.ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        $(`.error-${field}`).text(errors[field][0]);
                    }
                } else {
                    toastr.error('Failed to save assignment');
                }
            }
        });
    });

    // Edit assignment
    $(document).on('click', '.editAssignBtn', function() {
        const pid = $(this).data('pid');
        const oid = $(this).data('oid');
        
        $.get(`/admin/assign-option/show/${pid}/${oid}`, function(data) {
            $('#products_id').val(data.products_id);
            $('#options_id').val(data.options_id);
            $('#original_product_id').val(data.products_id);
            $('#original_option_id').val(data.options_id);
            $('#assignModal').modal('show');
        });
    });

    // Delete assignment
    $(document).on('click', '.deleteAssignBtn', function() {
        if (!confirm('Are you sure you want to delete this assignment?')) return;
        const pid = $(this).data('pid');
        const oid = $(this).data('oid');

        $.ajax({
            url: '{{ route("assign-option.destroy") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                products_id: pid,
                options_id: oid
            },
            success: function(res) {
                toastr.success(res.success);
                assignTable.ajax.reload();
            },
            error: function() {
                toastr.error('Failed to delete assignment');
            }
        });
    });
  });
</script>
@endsection
