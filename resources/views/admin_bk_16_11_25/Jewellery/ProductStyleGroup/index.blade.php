@extends('admin.layouts.master')

@section('main_section')
<style>
    .dt-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    .image-preview-container {
        position: relative;
        display: inline-block;
    }

    table.dataTable td.dt-control:before {
        background: #317cb1 !important;
    }

    .image-remove {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 0, 0, 0.7);
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
    }
    .form-switch .form-check-input {
        width: 2.5em;
    }
    .sort-order-input {
        width: 80px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4>Product Style Groups</h4>
            <button class="btn btn-primary" id="addStyleGroupBtn">Add New Group</button>
        </div>
        <div class="table-responsive text-nowrap card-body">
            <table class="table table-hover" id="styleGroupsTable">
                <thead class="bg-light">
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Collection</th>
                        <th>Status</th>
                        <th>Display</th>
                        <th>Sort</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="styleGroupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="styleGroupForm" class="modal-content" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="psg_id" name="psg_id">
            <input type="hidden" id="remove_image" name="remove_image" value="0">

            <div class="modal-header">
                <h5 class="modal-title">Product Style Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter Name" required>
                </div>

                <div class="mb-3">
                    <label>Collection</label>
                    <select name="collection_id" id="collection_id" class="form-select">
                        <option value="">Select Collection</option>
                        @foreach($collections as $collection)
                            <option value="{{ $collection->id }}">{{ $collection->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="psg_status" id="psg_status" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Display In Front</label>
                    <select name="psg_display_in_front" id="psg_display_in_front" class="form-select">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Sort Order</label>
                    <input type="number" name="psg_sort_order" id="psg_sort_order" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Alias</label>
                    <input type="text" name="psg_alias" id="psg_alias" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Image</label>
                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                    <div id="imagePreview" class="mt-2"></div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    const modal = new bootstrap.Modal($('#styleGroupModal')[0]);
    const storageUrl = '{{ asset("storage") }}';

    function format(d) {
        return `
            <div class="d-flex gap-2 ps-4">
                <button class="btn btn-sm btn-primary edit-btn" data-id="${d.psg_id}">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="${d.psg_id}">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        `;
    }

    const table = $('#styleGroupsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("style-groups.index") }}',
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
            },
            { data: 'psg_id' },
            { data: 'psg_names' },
            { data: 'psg_image' },
            { data: 'collection_id', name: 'collection_id' },
            { data: 'psg_status' },
            { data: 'psg_display_in_front' },
            { data: 'psg_sort_order' }
        ],
        order: [[1, 'asc']]
    });

    $('#styleGroupsTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(format(row.data())).show();
            tr.addClass('shown');
        }
    });

    $('#addStyleGroupBtn').click(function () {
        $('#styleGroupForm')[0].reset();
        $('#psg_id').val('');
        $('#remove_image').val(0);
        $('#imagePreview').empty();
        modal.show();
    });

    $('#image').change(function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                $('#imagePreview').html(`
                    <div class="image-preview-container">
                        <img src="${e.target.result}" class="dt-thumbnail">
                        <div class="image-remove" id="removeImageBtn">×</div>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        }
    });

    $(document).on('click', '#removeImageBtn', function () {
        $('#imagePreview').empty();
        $('#image').val('');
        $('#remove_image').val(1);
    });

    $(document).on('change', '.toggle-status', function () {
        const id = $(this).data('id');
        const status = $(this).prop('checked') ? 1 : 0;
        $.post('{{ route("style-groups.toggle-status") }}', {
            id, status, _token: '{{ csrf_token() }}'
        }, function (res) {
            res.success ? toastr.success('Status updated') : toastr.error('Failed');
        });
    });

    $(document).on('change', '.toggle-display', function () {
        const id = $(this).data('id');
        const display = $(this).prop('checked') ? 1 : 0;
        $.post('{{ route("style-groups.toggle-display") }}', {
            id, display, _token: '{{ csrf_token() }}'
        }, function (res) {
            res.success ? toastr.success('Display updated') : toastr.error('Failed');
        });
    });

    $(document).on('blur', '.sort-order-input', function () {
        const id = $(this).data('id');
        const sortOrder = $(this).val();
        $.post('{{ route("style-groups.update-sort") }}', {
            id, sort_order: sortOrder, _token: '{{ csrf_token() }}'
        }, function (res) {
            res.success ? toastr.success('Sort order updated') : toastr.error('Failed');
        });
    });

    $('#styleGroupForm').submit(function (e) {
        e.preventDefault();
        const id = $('#psg_id').val();
        const formData = new FormData(this);
        const url = id
            ? '{{ route("style-groups.update", ":id") }}'.replace(':id', id)
            : '{{ route("style-groups.store") }}';

        if (id) formData.append('_method', 'PUT');

        $('#saveBtn').html('Saving...').prop('disabled', true);

        $.ajax({
            url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: res => {
                toastr.success(res.message);
                modal.hide();
                table.ajax.reload();
            },
            error: xhr => {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) toastr.error(errors[field][0]);
                } else toastr.error('Something went wrong.');
            },
            complete: () => $('#saveBtn').html('Save').prop('disabled', false)
        });
    });

    $(document).on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        $.get('{{ route("style-groups.edit", ":id") }}'.replace(':id', id), function (data) {
            $('#psg_id').val(data.group.psg_id);
            $('#collection_id').val(data.group.collection_id);
            $('#psg_status').val(data.group.psg_status);
            $('#psg_display_in_front').val(data.group.psg_display_in_front);
            $('#psg_sort_order').val(data.group.psg_sort_order);
            $('#psg_alias').val(data.group.psg_alias);
            $('#name').val(data.name);

            $('#imagePreview').empty();
            if (data.image) {
                $('#imagePreview').html(`
                    <div class="image-preview-container">
                        <img src="${storageUrl}/${data.image}" class="dt-thumbnail">
                        <div class="image-remove" id="removeImageBtn">×</div>
                    </div>
                `);
                $('#remove_image').val(0);
            }

            modal.show();
        });
    });

    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('id');
        if (confirm('Are you sure to delete this style group?')) {
            $.ajax({
                url: '{{ route("style-groups.destroy", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: res => {
                    toastr.success(res.message);
                    table.ajax.reload();
                },
                error: () => toastr.error('Delete failed.')
            });
        }
    });
});
</script>
@endsection
