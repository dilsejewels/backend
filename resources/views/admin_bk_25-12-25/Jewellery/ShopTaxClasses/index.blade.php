@extends('admin.layouts.master')

@section('main_section')

<style>
    table.dataTable td.dt-control:before {
        background: #317cb1 !important;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Tax Classes Management</h4>
            <button class="btn btn-primary" id="addTaxClassBtn">
                <i class="fas fa-plus me-2"></i> Add New
            </button>
        </div>
        <div class="card-body">
            <table class="table table-hover" id="taxClassesTable">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Date Added</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="taxClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="taxClassForm">
            @csrf
            <input type="hidden" id="tax_class_id" name="id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Tax Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Title *</label>
                        <input type="text" class="form-control" id="tax_class_title" name="tax_class_title" required>
                        <div class="invalid-feedback" id="error_tax_class_title"></div>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" id="tax_class_description" name="tax_class_description"></textarea>
                        <div class="invalid-feedback" id="error_tax_class_description"></div>
                    </div>
                    <div id="formError" class="text-danger"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    const taxModal = new bootstrap.Modal(document.getElementById('taxClassModal'));
    // Create route templates
    const storeRoute = "{{ route('tax-classes.store') }}";
    const showRoute = "{{ route('tax-classes.show', ':id') }}";
    const updateRoute = "{{ route('tax-classes.update', ':id') }}";
    const destroyRoute = "{{ route('tax-classes.destroy', ':id') }}";
    const dataRoute = "{{ route('tax-classes.data') }}";
    
    const table = $('#taxClassesTable').DataTable({
        ajax: dataRoute,
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: '20px'
            },
            { data: 'tax_class_id' },
            { data: 'tax_class_title' },
            { data: 'tax_class_description' },
            {
                data: 'date_added',
                render: data => data ? new Date(data).toLocaleDateString() : ''
            }
        ],
        order: [[1, 'desc']]
    });

    $('#taxClassesTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = table.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-primary editBtn" data-id="${data.tax_class_id}">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.tax_class_id}" data-name="${data.tax_class_title}">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    function clearValidation() {
        $('.form-control').removeClass('is-invalid');
        $('[id^="error_"]').text('');
        $('#formError').text('');
    }

    $('#addTaxClassBtn').click(() => {
        clearValidation();
        $('#taxClassForm')[0].reset();
        $('#tax_class_id').val('');
        $('#modalTitle').text('Add Tax Class');
        taxModal.show();
    });

    $('#taxClassForm').submit(function (e) {
        e.preventDefault();
        clearValidation();

        const id = $('#tax_class_id').val();
        const url = id ? updateRoute.replace(':id', id) : storeRoute;
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize() + (id ? '&_method=PUT' : ''),
            success: res => {
                if (res.success) {
                    toastr.success(res.message);
                    taxModal.hide();
                    table.ajax.reload();
                } else {
                    toastr.error(res.message || 'Something went wrong');
                }
            },
            error: xhr => {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        $(`[name="${field}"]`).addClass('is-invalid');
                        $(`#error_${field}`).text(errors[field][0]);
                    });
                } else {
                    $('#formError').text('Something went wrong.');
                }
            }
        });
    });

    $(document).on('click', '.editBtn', function () {
        const id = $(this).data('id');
        clearValidation();
        $.get(showRoute.replace(':id', id), function (res) {
            $('#tax_class_id').val(res.tax_class_id);
            $('#tax_class_title').val(res.tax_class_title);
            $('#tax_class_description').val(res.tax_class_description);
            $('#modalTitle').text('Edit Tax Class');
            taxModal.show();
        }).fail(() => {
            toastr.error('Failed to fetch data');
        });
    });

    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        if (!confirm(`Are you sure you want to delete "${name}"?`)) return;

        $.ajax({
            url: destroyRoute.replace(':id', id),
            type: 'POST',
            data: { 
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: res => {
                toastr.success(res.message);
                table.ajax.reload();
            },
            error: () => {
                toastr.error('Failed to delete');
            }
        });
    });
});
</script>
@endsection