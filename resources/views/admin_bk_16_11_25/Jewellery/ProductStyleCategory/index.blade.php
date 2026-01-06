@extends('admin.layouts.master')

@section('main_section')
<style>
    .dt-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .dt-banner {
        max-width: 300px;
        max-height: 150px;
        object-fit: contain;
        border-radius: 4px;
    }

    table.dataTable td.dt-control:before {
        background: #317cb1;
    }
    
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
        cursor: pointer;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4 class="mb-0">Product Style Categories</h4>
            <button class="btn btn-primary" id="addPSCBtn">Add New</button>
        </div>
        <div class="table-responsive text-nowrap card-body">
            <table class="table table-hover" id="pscTable" style="width: 100%;">
                <thead class="bg-light">
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Display</th>
                        <th>Engagement</th>
                        <th>Sort</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="pscModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="pscForm" class="modal-content" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="psc_id" name="psc_id">
            <input type="hidden" id="current_image" name="current_image">
            <input type="hidden" id="current_banner" name="current_banner">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Product Style Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $parent)
                            <optgroup label="{{ $parent->category_name }}" data-parent-id="{{ $parent->category_id }}">
                                <option value="parent_{{ $parent->category_id }}">
                                    {{ $parent->category_name }} (Parent)
                                </option>
                                @foreach($parent->children as $child)
                                    <option value="{{ $child->category_id }}">
                                        {{ $child->category_name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <small class="text-danger error-category_id"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="psc_name" id="psc_name" class="form-control" required>
                    <small class="text-danger error-psc_name"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alias <span class="text-danger">*</span></label>
                    <input type="text" name="psc_alias" id="psc_alias" class="form-control" required>
                    <small class="text-danger error-psc_alias"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="psc_image" id="psc_image" class="form-control">
                    <small class="text-danger error-psc_image"></small>
                    <div class="mt-2" id="imagePreviewContainer" style="display:none">
                        <img id="imagePreview" class="dt-thumbnail" style="max-height:150px;">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Banner Image</label>
                    <input type="file" name="banner_image" id="banner_image" class="form-control">
                    <small class="text-danger error-banner_image"></small>
                    <div class="mt-2" id="bannerImagePreviewContainer" style="display:none">
                        <img id="bannerImagePreview" class="dt-banner" style="max-height:150px;">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Engagement Menu</label>
                    <select name="engagement_menu" id="engagement_menu" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="psc_sort_order" id="psc_sort_order" class="form-control" value="0">
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- JS Code -->
<script>
$(document).ready(function () {
    const pscModal = new bootstrap.Modal(document.getElementById('pscModal'));
    let pscTable;

    function initDataTable() {
        pscTable = $('#pscTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("product-style-category.index") }}',
            columns: [
                {
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: '',
                },
                { data: 'psc_id' },
                { data: 'psc_name' },
                { 
                    data: 'image_url', 
                    render: data => data ? 
                        `<img src="${data}" class="dt-thumbnail">` : 
                        '<span class="text-muted">No image</span>' 
                },
                { data: 'category_name' },
                { 
                    data: 'psc_status',
                    render: (data, type, row) => `
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" 
                                data-id="${row.psc_id}" ${data == 1 ? 'checked' : ''}>
                        </div>`
                },
                { 
                    data: 'psc_display_in_front',
                    render: (data, type, row) => `
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input display-toggle" 
                                data-id="${row.psc_id}" ${data == 1 ? 'checked' : ''}>
                        </div>`
                },
                {
                    data: 'engagement_menu',
                    render: (data, type, row) => `
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input engagement-toggle" 
                                data-id="${row.psc_id}" ${data == 1 ? 'checked' : ''}>
                        </div>`
                },
                { data: 'psc_sort_order' }
            ],
            order: [[1, 'desc']]
        });

        function format(row) {
            return `
                <div class="p-2">
                    <strong>Alias:</strong> ${row.psc_alias || '-'}<br>
                    <strong>Sort Order:</strong> ${row.psc_sort_order || 0}<br>
                    <strong>Engagement Menu:</strong> ${row.engagement_menu ? 'Yes' : 'No'}<br>
                    <strong>Banner Image:</strong><br>
                    ${row.banner_image_url ? 
                        `<img src="${row.banner_image_url}" class="dt-banner">` : 
                        '<span class="text-muted">No banner image</span>'}
                    <div class="mt-2">
                        <button class="btn btn-sm btn-info editPSCBtn" data-id="${row.psc_id}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger deletePSCBtn" data-id="${row.psc_id}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `;
        }

        $('#pscTable tbody').on('click', 'td.dt-control', function () {
            const tr = $(this).closest('tr');
            const row = pscTable.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
        });
    }

    initDataTable();

    $('#psc_image').on('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview').attr('src', e.target.result);
                $('#imagePreviewContainer').show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#banner_image').on('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#bannerImagePreview').attr('src', e.target.result);
                $('#bannerImagePreviewContainer').show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#addPSCBtn').click(function () {
        $('#pscForm')[0].reset();
        $('#psc_id').val('');
        $('#imagePreview').attr('src', '');
        $('#bannerImagePreview').attr('src', '');
        $('#imagePreviewContainer').hide();
        $('#bannerImagePreviewContainer').hide();
        $('#current_image').val('');
        $('#current_banner').val('');
        $('#category_id').val('');
        $('#engagement_menu').val('0');
        $('#modalTitle').text('Add Product Style Category');
        pscModal.show();
    });

    $('#pscForm').on('submit', function (e) {
        e.preventDefault();
        $('.text-danger').text('');
        const formData = new FormData(this);
        const id = $('#psc_id').val();
        const url = id ? '{{ route("product-style-category.update", ":id") }}'.replace(':id', id) : '{{ route("product-style-category.store") }}';

        if (id) formData.append('_method', 'PUT');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                toastr.success(res.message);
                pscTable.ajax.reload();
                pscModal.hide();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, val) {
                        $('.error-' + key).text(val[0]);
                    });
                } else {
                    toastr.error('Something went wrong!');
                }
            }
        });
    });

    $(document).on('click', '.editPSCBtn', function () {
        const id = $(this).data('id');
        $.get('{{ route("product-style-category.edit", ":id") }}'.replace(':id', id), function (data) {
            $('#psc_id').val(data.psc_id);
            $('#psc_name').val(data.psc_name);
            $('#psc_alias').val(data.psc_alias);
            $('#psc_sort_order').val(data.psc_sort_order);
            $('#current_image').val(data.psc_image);
            $('#current_banner').val(data.banner_image);
            $('#category_id').val(data.category_id);
            $('#engagement_menu').val(data.engagement_menu);
            $('#modalTitle').text('Edit Product Style Category');

            if (data.image_url) {
                $('#imagePreview').attr('src', data.image_url);
                $('#imagePreviewContainer').show();
            } else {
                $('#imagePreviewContainer').hide();
            }

            if (data.banner_image_url) {
                $('#bannerImagePreview').attr('src', data.banner_image_url);
                $('#bannerImagePreviewContainer').show();
            } else {
                $('#bannerImagePreviewContainer').hide();
            }

            pscModal.show();
        });
    });

    $(document).on('click', '.deletePSCBtn', function () {
        if (!confirm('Are you sure you want to delete this category?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: '{{ route("product-style-category.destroy", ":id") }}'.replace(':id', id),
            type: 'DELETE',
            data: { 
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function (res) {
                toastr.success(res.message);
                pscTable.ajax.reload();
            },
            error: function() {
                toastr.error('Failed to delete category');
            }
        });
    });
    
    // Update status toggle
    $(document).on('change', '.status-toggle', function () {
        const id = $(this).data('id');
        const status = this.checked ? 1 : 0;
        
        $.ajax({
            url: '{{ route("product-style-category.status", ":id") }}'.replace(':id', id),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            success: function (res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error('Failed to update status');
            }
        });
    });
    
    // Update display toggle
    $(document).on('change', '.display-toggle', function () {
        const id = $(this).data('id');
        const display = this.checked ? 1 : 0;
        
        $.ajax({
            url: '{{ route("product-style-category.display", ":id") }}'.replace(':id', id),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                display: display
            },
            success: function (res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error('Failed to update display setting');
            }
        });
    });
    
    $(document).on('change', '.engagement-toggle', function () {
        const id = $(this).data('id');
        const engagement = this.checked ? 1 : 0;
        
        $.ajax({
            url: '{{ route("product-style-category.engagement", ":id") }}'.replace(':id', id),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                engagement: engagement
            },
            success: function (res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error('Failed to update engagement menu setting');
            }
        });
    });
});
</script>
@endsection