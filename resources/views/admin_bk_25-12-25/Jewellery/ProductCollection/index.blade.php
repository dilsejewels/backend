@extends('admin.layouts.master')

@section('main_section')
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .dt-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
        }

        .image-preview-container {
            position: relative;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 18px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .video-preview-container {
            position: relative;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .remove-video {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 18px;
            cursor: pointer;
            font-size: 12px;
        }

        /* Select2 Custom Styles */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            min-height: 42px;
            padding: 5px;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            padding: 2px 8px;
            margin: 3px;
            border-radius: 0.25rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 5px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff;
        }

        .parent-option {
            font-weight: bold;
            background-color: #f8f9fa !important;
            color: #000 !important;
        }

        .child-option {
            padding-left: 20px;
            color: #6c757d;
        }

        .select2-dropdown {
            z-index: 9999;
        }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-0">Product Collections</h4>
                <button class="btn btn-primary" id="createCollectionBtn">Add New Collection</button>
            </div>
            <div class="card-body table-responsive text-nowrap">
                <table id="collectionsTable" class="table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Product Types</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Display in Menu</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="collectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="collectionForm" class="modal-content" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="collectionId">
                <input type="hidden" name="remove_image" id="removeImage" value="0">
                <input type="hidden" name="remove_banner" id="removeBanner" value="0">
                <input type="hidden" name="remove_banner_video" id="removeBannerVideo" value="0">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Collection Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Collection Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Enter collection name">
                                <small class="text-danger error-name"></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Product Types <span class="text-danger">*</span></label>
                                <select class="form-control" id="product_type" name="product_type[]" multiple="multiple" style="width: 100%;">
                                    @foreach($productTypes as $key => $type)
                                        <option value="{{ $key }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                                <small class="text-danger error-product_type"></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Categories</label>
                                <select class="form-control" id="category_ids" name="category_ids[]" multiple="multiple" style="width: 100%;">
                                    @foreach($categories as $parent)
                                        <option value="parent_{{ $parent->category_id }}" data-class="parent-option">
                                            📁 {{ $parent->category_name }} (Parent)
                                        </option>
                                        @foreach($parent->children as $child)
                                            <option value="{{ $child->category_id }}" data-class="child-option">
                                                └─ {{ $child->category_name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Heading</label>
                                <input type="text" name="heading" id="heading" class="form-control" placeholder="Enter heading">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Collection Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="collection_image" name="collection_image" accept="image/*">
                                <small class="text-danger error-collection_image"></small>
                                <div class="mt-2" id="imagePreview"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Banner Image</label>
                                <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/*">
                                <div class="mt-2" id="bannerPreview"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Banner Video (Max 500MB)</label>
                                <input type="file" class="form-control" id="banner_video" name="banner_video" accept="video/*">
                                <div class="mt-2" id="bannerVideoPreview"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alias</label>
                                <input type="text" name="alias" id="alias" class="form-control" placeholder="Enter alias">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="sort_order" class="form-control" value="0">
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="display_in_menu" name="display_in_menu" value="1" checked>
                                    <label class="form-check-label" for="display_in_menu">Display in Menu</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Collection</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    <!-- JS Code -->
<script>
    $(document).ready(function () {
        const storageUrl = "{{ url('storage') }}";

        // Initialize Select2 for both dropdowns
        function initializeSelect2() {
            // Check if Select2 is already initialized before destroying
            if ($('#product_type').hasClass('select2-hidden-accessible')) {
                $('#product_type').select2('destroy');
            }
            if ($('#category_ids').hasClass('select2-hidden-accessible')) {
                $('#category_ids').select2('destroy');
            }

            // Initialize product type
            $('#product_type').select2({
                placeholder: "Select product types",
                allowClear: true,
                width: '100%',
                closeOnSelect: false,
                dropdownParent: $('#collectionModal')
            });

            // Initialize categories
            $('#category_ids').select2({
                placeholder: "Select categories",
                allowClear: true,
                width: '100%',
                closeOnSelect: false,
                dropdownParent: $('#collectionModal'),
                templateResult: formatCategoryOption,
                templateSelection: formatCategorySelection
            });
        }

        function formatCategoryOption(option) {
            if (!option.id) {
                return option.text;
            }
            
            var $option = $(
                '<span class="' + $(option.element).data('class') + '">' + option.text + '</span>'
            );
            return $option;
        }

        function formatCategorySelection(option) {
            return option.text;
        }

        // Initialize on page load
        initializeSelect2();

        const collectionsTable = $('#collectionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('collections.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { 
                    data: 'collection_image', 
                    name: 'collection_image',
                    render: function(data) {
                        return data ? `<img src="${storageUrl}/${data}" class="dt-thumbnail">` : '<span class="text-muted">No image</span>';
                    }
                },
                { data: 'name', name: 'name' },
                { data: 'product_types', name: 'product_types' },
                { 
    data: 'categories_info', 
    name: 'categories_info',
    render: function(data, type, row) {
        if (!data) return '';
        return data.length > 15 ? data.substr(0, 15) + '...' : data;
    }
},
                { 
                    data: 'status', 
                    name: 'status',
                    render: (data, type, row) => `
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" 
                                data-id="${row.id}" ${data == 1 ? 'checked' : ''}>
                        </div>`
                },
                { 
                    data: 'display_in_menu', 
                    name: 'display_in_menu',
                    render: (data, type, row) => `
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input display-toggle" 
                                data-id="${row.id}" ${data == 1 ? 'checked' : ''}>
                        </div>`
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[0, 'desc']],
            language: {
                emptyTable: "No collections found",
                search: "Search:",
                paginate: {
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        $('#createCollectionBtn').click(() => {
            $('#collectionForm')[0].reset();
            $('#collectionId').val('');
            $('#removeImage').val('0');
            $('#removeBanner').val('0');
            $('#removeBannerVideo').val('0');
            $('#imagePreview, #bannerPreview, #bannerVideoPreview').html('');
            $('.text-danger').text('');
            $('#modalTitle').text('Create New Collection');
            
            // Reset Select2
            $('#product_type').val(null).trigger('change');
            $('#category_ids').val(null).trigger('change');
            $('#display_in_menu').prop('checked', true);
            
            // Reinitialize Select2
            initializeSelect2();
            
            $('#collectionModal').modal('show');
        });

        // Handle image previews and removal
        $('#collection_image').change(function () {
            handleImagePreview(this, '#imagePreview', '#removeImage');
        });

        $('#banner_image').change(function () {
            handleImagePreview(this, '#bannerPreview', '#removeBanner');
        });
        
        $('#banner_video').change(function () {
            handleVideoPreview(this, '#bannerVideoPreview', '#removeBannerVideo');
        });

        function handleImagePreview(input, previewSelector, removeFlagSelector) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    $(previewSelector).html(`
                        <div class="image-preview-container">
                            <img src="${e.target.result}" width="150" height="150" style="object-fit: cover; border-radius: 8px;">
                            <span class="remove-image" onclick="removePreview('${previewSelector}', '${removeFlagSelector}')">×</span>
                        </div>`);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function handleVideoPreview(input, previewSelector, removeFlagSelector) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const videoUrl = URL.createObjectURL(file);
                $(previewSelector).html(`
                    <div class="video-preview-container">
                        <video width="200" height="150" controls style="object-fit: cover; border-radius: 8px;">
                            <source src="${videoUrl}" type="${file.type}">
                        </video>
                        <span class="remove-video" onclick="removePreview('${previewSelector}', '${removeFlagSelector}')">×</span>
                    </div>
                `);
            }
        }

        // Global function for removing previews
        window.removePreview = function(previewSelector, removeFlagSelector) {
            $(removeFlagSelector).val('1');
            $(previewSelector).html('');
            $(previewSelector).append('<div class="text-danger mt-2">File will be removed on save</div>');
        };

        $('#collectionForm').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = $('#collectionId').val();
            const url = id ? "{{ route('collections.update', ['id' => ':id']) }}".replace(':id', id) 
                            : "{{ route('collections.store') }}";

            // Show loading state
            $('#saveBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: res => {
                    $('#collectionModal').modal('hide');
                    toastr.success(res.message);
                    collectionsTable.ajax.reload();
                    $('#saveBtn').prop('disabled', false).html('Save Collection');
                },
                error: xhr => {
                    $('#saveBtn').prop('disabled', false).html('Save Collection');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        // Clear previous errors
                        $('.text-danger').text('');
                        for (let field in errors) {
                            $(`.error-${field}`).text(errors[field][0]);
                        }
                    } else {
                        toastr.error('Failed to save collection');
                    }
                }
            });
        });
        
        $(document).on('click', '.editCollectionBtn', function () {
            const id = $(this).data('id');
            
            // Show loading
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

            $.get("{{ route('collections.edit', ['id' => ':id']) }}".replace(':id', id), function (data) {
                $('#collectionId').val(data.id);
                $('#removeImage').val('0');
                $('#removeBanner').val('0');
                $('#removeBannerVideo').val('0');
                $('#name').val(data.name);
                $('#heading').val(data.heading);
                $('#description').val(data.description);
                $('#alias').val(data.alias);
                $('#sort_order').val(data.sort_order);
                
                // Set product types
                $('#product_type').val(data.product_type).trigger('change');
                
                // Set categories
                $('#category_ids').val(data.category_ids).trigger('change');
                
                // Set display in menu
                $('#display_in_menu').prop('checked', data.display_in_menu == 1);

                // Handle image previews
                if (data.collection_image) {
                    const imageUrl = `${storageUrl}/${data.collection_image}`;
                    $('#imagePreview').html(`
                        <div class="image-preview-container">
                            <img src="${imageUrl}" width="150" height="150" style="object-fit: cover; border-radius: 8px;">
                            <span class="remove-image" onclick="removePreview('#imagePreview', '#removeImage')">×</span>
                        </div>`);
                } else {
                    $('#imagePreview').html('');
                }

                if (data.banner_image) {
                    const bannerUrl = `${storageUrl}/${data.banner_image}`;
                    $('#bannerPreview').html(`
                        <div class="image-preview-container">
                            <img src="${bannerUrl}" width="150" height="150" style="object-fit: cover; border-radius: 8px;">
                            <span class="remove-image" onclick="removePreview('#bannerPreview', '#removeBanner')">×</span>
                        </div>`);
                } else {
                    $('#bannerPreview').html('');
                }
                
                if (data.banner_video) {
                    const videoUrl = `${storageUrl}/${data.banner_video}`;
                    $('#bannerVideoPreview').html(`
                        <div class="video-preview-container">
                            <video width="200" height="150" controls style="object-fit: cover; border-radius: 8px;">
                                <source src="${videoUrl}">
                            </video>
                            <span class="remove-video" onclick="removePreview('#bannerVideoPreview', '#removeBannerVideo')">×</span>
                        </div>
                    `);
                } else {
                    $('#bannerVideoPreview').html('');
                }

                $('.text-danger').text('');
                $('#modalTitle').text('Edit Collection');
                
                // Reinitialize Select2
                initializeSelect2();
                
                $('#collectionModal').modal('show');
                
                // Re-enable button
                $('.editCollectionBtn').prop('disabled', false).html('<i class="fas fa-edit"></i> Edit');
            }).fail(function() {
                toastr.error('Failed to load collection data');
                $('.editCollectionBtn').prop('disabled', false).html('<i class="fas fa-edit"></i> Edit');
            });
        });

        $(document).on('click', '.deleteCollectionBtn', function () {
            if (!confirm('Are you sure you want to delete this collection?')) return;
            
            const id = $(this).data('id');
            const $button = $(this);
            
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

            $.ajax({
                url: "{{ route('collections.destroy', ['id' => ':id']) }}".replace(':id', id),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: res => {
                    toastr.success(res.message);
                    collectionsTable.ajax.reload();
                },
                error: () => {
                    toastr.error('Failed to delete collection');
                    $button.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                }
            });
        });

        $(document).on('change', '.status-toggle', function () {
            const id = $(this).data('id');
            const status = this.checked ? 1 : 0;
            const $toggle = $(this);

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route('collections.status', ['id' => ':id']) }}".replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                success: res => {
                    toastr.success(res.message);
                    $toggle.prop('disabled', false);
                },
                error: () => {
                    toastr.error('Failed to update status');
                    $toggle.prop('disabled', false);
                    // Revert toggle
                    $toggle.prop('checked', !status);
                }
            });
        });

        $(document).on('change', '.display-toggle', function () {
            const id = $(this).data('id');
            const display = this.checked ? 1 : 0;
            const $toggle = $(this);

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route('collections.display', ['id' => ':id']) }}".replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    display: display
                },
                success: res => {
                    toastr.success(res.message);
                    $toggle.prop('disabled', false);
                },
                error: () => {
                    toastr.error('Failed to update display setting');
                    $toggle.prop('disabled', false);
                    // Revert toggle
                    $toggle.prop('checked', !display);
                }
            });
        });

        // Reinitialize Select2 when modal is shown
        $('#collectionModal').on('shown.bs.modal', function () {
            initializeSelect2();
        });
    });
</script>
@endsection