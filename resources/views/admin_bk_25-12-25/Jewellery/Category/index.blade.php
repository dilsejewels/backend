@extends('admin.layouts.master')

@section('main_section')
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .invalid-feedback {
            display: block;
        }

        .bg-primary {
            background-color: #ffffff !important;
        }
        
        /* Select2 Custom Styles - YEH ADD KARNA IMPORTANT HAI */
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

        .select2-dropdown {
            z-index: 9999 !important;
        }

        /* Ensure dropdown is visible in modal */
        .modal .select2-container--open {
            z-index: 99999 !important;
        }

        .modal .select2-dropdown {
            z-index: 99999 !important;
        }

        .badge {
            font-size: 0.75em;
        }
        
        /* Image preview styles */
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
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Category Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" id="addcategoryBtn">
                    <i class="fas fa-plus me-2"></i>Add New
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="categoryTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Parent Category</th>
                                <th>Product Types</th>
                                <th>Banner Image</th>
                                <th>Status</th>
                                <th>Display Front</th>
                                <th>Sort Order</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="categoryForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="record_id" name="id">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTitle">Add New Category</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-1">
                                            <label for="parent_id" class="form-label mb-0">Parent Category</label>
                                        </div>

                                        <select class="form-control" id="parent_id" name="parent_id">
                                            <option value="">-- No Parent --</option>
                                        </select>

                                        <div id="newParentForm" class="mt-2 border p-2" style="display:none;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold">Create New Parent:</span>
                                                <button type="button" class="btn-close" id="closeParentForm"></button>
                                            </div>
                                            <div class="mb-2">
                                                <input type="text" name="new_parent_name"
                                                    class="form-control form-control-sm" id="new_parent_name"
                                                    placeholder="Category Name">
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-sm btn-primary" id="saveParentBtn">
                                                    <i class="fas fa-save me-1"></i> Save
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category_name" class="form-label">Category Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="category_name" name="category_name">
                                        <div class="invalid-feedback" id="error_category_name"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category_alias" class="form-label">Alias <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="category_alias"
                                            name="category_alias">
                                        <div class="invalid-feedback" id="error_category_alias"></div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="product_type" class="form-label">Product Types</label>
                                        <select class="form-control" id="product_type" name="product_type[]" multiple="multiple" style="width: 100%;">
                                            <option value="0">Jewelry</option>
                                            <option value="1">Engagement</option>
                                            <option value="2">Wedding</option>
                                            <option value="3">Gifts</option>
                                            <option value="4">Sale</option>
                                        </select>
                                        <div class="invalid-feedback" id="error_product_type"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category_h1_tag" class="form-label">H1 Tag</label>
                                        <input type="text" class="form-control" id="category_h1_tag"
                                            name="category_h1_tag">
                                        <div class="invalid-feedback" id="error_category_h1_tag"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="category_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="category_description" name="category_description" rows="3"></textarea>
                                        <div class="invalid-feedback" id="error_category_description"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="seo_url" class="form-label">SEO URL</label>
                                        <input type="text" class="form-control" id="seo_url" name="seo_url">
                                        <div class="invalid-feedback" id="error_seo_url"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category_meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control" id="category_meta_title"
                                            name="category_meta_title">
                                        <div class="invalid-feedback" id="error_category_meta_title"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category_meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="category_meta_description" name="category_meta_description" rows="2"></textarea>
                                        <div class="invalid-feedback" id="error_category_meta_description"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category_meta_keyword" class="form-label">Meta Keywords</label>
                                        <textarea class="form-control" id="category_meta_keyword" name="category_meta_keyword" rows="2"></textarea>
                                        <div class="invalid-feedback" id="error_category_meta_keyword"></div>
                                    </div>

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border p-3 rounded bg-light">
                                    <div class="mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category_status" name="category_status" required>
                                            <option value="">Select Status</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                        <div class="invalid-feedback" id="error_category_status"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Display on Front <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="is_display_front" name="is_display_front"
                                            required>
                                            <option value="">Select Option</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                        <div class="invalid-feedback" id="error_is_display_front"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Sort Order <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order"
                                            required>
                                        <div class="invalid-feedback" id="error_sort_order"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Deleted</label>
                                        <select class="form-select" id="deleted" name="deleted">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category_image" class="form-label">Category Image</label>
                                        <input type="file" class="form-control" id="category_image"
                                            name="category_image" accept="image/*">
                                        <div class="mt-2">
                                            <img id="category_image_preview" src="" class="img-thumbnail"
                                                style="max-height: 150px; display: none;">
                                        </div>
                                        <div class="invalid-feedback" id="error_category_image"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category_header_banner" class="form-label">Header Banner</label>
                                        <input type="file" class="form-control" id="category_header_banner"
                                            name="category_header_banner" accept="image/*">
                                        <div class="mt-2">
                                            <img id="category_header_banner_preview" src="" class="img-thumbnail"
                                                style="max-height: 150px; display: none;">
                                        </div>
                                        <div class="invalid-feedback" id="error_category_header_banner"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="formError" class="col-12 text-danger mt-3"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="savecategoryBtn">
                            <i class="fas fa-save me-2"></i>Save
                        </button>
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
                    <p>Are you sure you want to delete this category? This action cannot be undone.</p>
                    <p class="fw-bold" id="categoryNameToDelete"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Define base URL for storage images
            const storageUrl = "{{ url('storage') }}";

            let allCategories = [];

            // Initialize Select2 for product type - YEH CORRECT WAY SE INITIALIZE KARNA
            function initializeSelect2() {
                // Destroy if already initialized
                if ($('#product_type').hasClass('select2-hidden-accessible')) {
                    $('#product_type').select2('destroy');
                }

                // Initialize with proper configuration
                $('#product_type').select2({
                    placeholder: "Select product types",
                    allowClear: true,
                    width: '100%',
                    closeOnSelect: false,
                    dropdownParent: $('#categoryModal') // YEH IMPORTANT HAI MODAL KE LIYE
                });
            }

            // Initialize on page load
            initializeSelect2();

            // Reinitialize when modal is shown
            $('#categoryModal').on('shown.bs.modal', function() {
                initializeSelect2();
            });

            function buildParentDropdown(excludeId = null, selectedId = null) {
                let options = '<option value="">-- No Parent --</option>';
                allCategories.filter(cat => cat.parent_id === null).forEach(cat => {
                    if (!excludeId || cat.category_id != excludeId) {
                        let selected = selectedId && cat.category_id == selectedId ? 'selected' : '';
                        options +=
                            `<option value="${cat.category_id}" ${selected}>${cat.category_name}</option>`;
                    }
                });
                return options;
            }

            function populateFormFields(response) {
                $('#parent_id').html(buildParentDropdown(response.category_id, response.parent_id));
                $('#record_id').val(response.category_id);
                $('#category_name').val(response.category_name ?? '');
                $('#category_alias').val(response.category_alias ?? '');
                $('#category_description').val(response.category_description ?? '');
                $('#seo_url').val(response.seo_url ?? '');
                $('#category_meta_title').val(response.category_meta_title ?? '');
                $('#category_meta_description').val(response.category_meta_description ?? '');
                $('#category_meta_keyword').val(response.category_meta_keyword ?? '');
                $('#category_h1_tag').val(response.category_h1_tag ?? '');
                $('#category_status').val(response.category_status ? "1" : "0");
                $('#is_display_front').val(response.is_display_front ? "1" : "0");
                $('#sort_order').val(response.sort_order);
                $('#deleted').val(response.deleted ? "1" : "0");
                
                // Fix for product_type - handle JSON string properly
                let productTypes = response.product_type;
                
                console.log('Original product_type:', productTypes);
                console.log('Type:', typeof productTypes);
                
                // If it's a string, parse it as JSON
                if (typeof productTypes === 'string') {
                    try {
                        productTypes = JSON.parse(productTypes);
                        console.log('Parsed product_type:', productTypes);
                    } catch (e) {
                        console.error('Error parsing product_type JSON:', e);
                        productTypes = [];
                    }
                }
                
                // Ensure it's an array and convert all values to strings
                if (productTypes && Array.isArray(productTypes) && productTypes.length > 0) {
                    const stringTypes = productTypes.map(type => type.toString());
                    console.log('Setting select2 values:', stringTypes);
                    $('#product_type').val(stringTypes).trigger('change');
                } else {
                    console.log('No product types found');
                    $('#product_type').val(null).trigger('change');
                }
                
                // Use storageUrl for previews
                previewImageUrl(response.category_image, '#category_image_preview');
                previewImageUrl(response.category_header_banner, '#category_header_banner_preview');
            }

            function previewImageUrl(image, selector) {
                if (image) {
                    $(selector).attr('src', storageUrl + "/" + image).show();
                } else {
                    $(selector).hide();
                }
            }

            // Load categories for parent dropdown
            $.getJSON("{{ route('category.index') }}", function(data) {
                allCategories = data;
                $('#parent_id').html(buildParentDropdown());
            }).fail(function() {
                console.error('Failed to load categories');
            });

            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 3000
            };

            const table = $('#categoryTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('category.index') }}",
                    dataSrc: ''
                },
                columns: [{
                        data: 'category_id'
                    },
                    {
                        data: 'category_name',
                        render: function(data, type, row) {
                            const img = row.category_image ?
                                `<img src="${storageUrl}/${row.category_image}" class="img-thumbnail" width="50">` :
                                `<div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px">
                                  <i class="fas fa-folder text-muted"></i>
                               </div>`;
                            return `<div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">${img}</div>
                                    <div class="flex-grow-1">
                                        <strong>${data}</strong>
                                        <div class="text-muted small">${row.category_alias || 'No alias'}</div>
                                    </div>
                                </div>`;
                        }
                    },
                    {
                        data: 'parent',
                        render: function(data, type, row) {
                            return data?.category_name ||
                                allCategories.find(cat => cat.category_id === row.parent_id)
                                ?.category_name ||
                                '-- Top Level --';
                        }
                    },
                    {
                        data: 'product_type',
                        render: function(data, type, row) {
                            let productTypes = data;
                            
                            console.log('Table product_type:', productTypes);
                            
                            // Handle JSON string in table
                            if (typeof productTypes === 'string') {
                                try {
                                    productTypes = JSON.parse(productTypes);
                                } catch (e) {
                                    productTypes = [];
                                }
                            }
                            
                            if (!productTypes || !Array.isArray(productTypes) || productTypes.length === 0) {
                                return '<span class="text-muted">No types selected</span>';
                            }
                            
                            const productTypeNames = {
                                '0': 'Jewelry',
                                '1': 'Engagement', 
                                '2': 'Wedding',
                                '3': 'Gifts',
                                '4': 'Sale'
                            };
                            
                            const badges = productTypes.map(type => {
                                const typeStr = type.toString();
                                const typeName = productTypeNames[typeStr] || type;
                                return `<span class="badge bg-danger me-1 mb-1">${typeName}</span>`;
                            }).join('');
                            
                            return `<div class="d-flex flex-wrap">${badges}</div>`;
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'category_header_banner',
                        render: function(data) {
                            return data ?
                                `<img src="${storageUrl}/${data}" class="img-thumbnail" width="100" style="max-height: 50px;">` :
                                'No banner';
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'category_status',
                        render: function(data, type, row) {
                            return `<div class="form-check form-switch">
                                <input class="form-check-input toggle-switch" type="checkbox"
                                    data-id="${row.category_id}" data-field="category_status"
                                    ${data ? 'checked' : ''}>
                            </div>`;
                        }
                    },
                    {
                        data: 'is_display_front',
                        render: function(data, type, row) {
                            return `<div class="form-check form-switch">
                                <input class="form-check-input toggle-switch" type="checkbox"
                                    data-id="${row.category_id}" data-field="is_display_front"
                                    ${data ? 'checked' : ''}>
                            </div>`;
                        }
                    },
                    {
                        data: 'sort_order',
                        render: function(data, type, row) {
                            return `<input type="number" value="${data}" class="form-control form-control-sm sort-order" data-id="${row.category_id}" style="width:80px;" data-original-value="${data}">`;
                        }
                    },
                    {
                        data: 'category_date_added',
                        render: data => data ? new Date(data).toLocaleDateString() : ''
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-info editBtn" data-id="${row.category_id}">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.category_id}" data-name="${row.category_name}">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            `;
                        },
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                responsive: true
            });

            $('#categoryTable').on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#confirmDelete').data('id', id);
                $('#categoryNameToDelete').text(name);
                $('#deleteModal').modal('show');
            });

            $('#confirmDelete').click(function() {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/category') }}/" + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            table.ajax.reload(null, false);
                            allCategories = response.allCategories;
                        } else {
                            toastr.error(response.message);
                        }
                        $('#deleteModal').modal('hide');
                    },
                    error: function() {
                        toastr.error('Failed to delete category');
                        $('#deleteModal').modal('hide');
                    }
                });
            });

            $('#categoryTable').on('click', '.editBtn', function() {
                const id = $(this).data('id');
                $.get("{{ url('admin/category') }}/" + id, function(response) {
                    populateFormFields(response);
                    $('#modalTitle').text('Edit Category');
                    $('#savecategoryBtn').html('<i class="fas fa-sync me-2"></i> Update');
                    
                    // Reinitialize Select2 after populating data
                    setTimeout(() => {
                        initializeSelect2();
                    }, 100);
                    
                    $('#categoryModal').modal('show');
                }).fail(function() {
                    toastr.error('Failed to load category');
                });
            });

            $('#categoryForm').submit(function(e) {
                e.preventDefault();
                $('.invalid-feedback').text('');
                $('.is-invalid').removeClass('is-invalid');
                $('#formError').text('');

                const formData = new FormData(this);
                const id = $('#record_id').val();
                const url = id ? "{{ url('admin/category') }}/" + id : "{{ route('category.store') }}";
                formData.append('_method', id ? 'PUT' : 'POST');

                $.ajax({
                    url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#categoryModal').modal('hide');
                            table.ajax.reload(null, false);
                            allCategories = response.allCategories;
                            $('#parent_id').html(buildParentDropdown());
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(field, msg) {
                                $(`#${field}`).addClass('is-invalid');
                                $(`#error_${field}`).text(msg[0]);
                            });
                            $('.modal-body').scrollTop(0);
                        } else {
                            toastr.error('An error occurred.');
                        }
                    }
                });
            });

            $('#categoryModal').on('hidden.bs.modal', function() {
                $('#categoryForm')[0].reset();
                $('.invalid-feedback').text('');
                $('.is-invalid').removeClass('is-invalid');
                $('#formError').text('');
                $('#category_image_preview, #category_header_banner_preview').hide();
                $('#modalTitle').text('Add New Category');
                $('#savecategoryBtn').html('<i class="fas fa-save me-2"></i> Save');
                $('#parent_id').html(buildParentDropdown());
                
                // Reset select2
                $('#product_type').val(null).trigger('change');
            });

            $('#category_image').change(function() {
                previewSelectedImage(this, '#category_image_preview');
            });

            $('#category_header_banner').change(function() {
                previewSelectedImage(this, '#category_header_banner_preview');
            });

            function previewSelectedImage(input, selector) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => $(selector).attr('src', e.target.result).show();
                    reader.readAsDataURL(input.files[0]);
                } else {
                    $(selector).hide();
                }
            }

            $('#categoryTable').on('change', '.toggle-switch', function() {
                const id = $(this).data('id');
                const field = $(this).data('field');
                const value = $(this).is(':checked') ? 1 : 0;
                updateCategoryField(id, field, value, $(this), !value);
            });

            $('#categoryTable').on('blur', '.sort-order', function() {
                const id = $(this).data('id');
                const value = $(this).val();
                const original = $(this).data('original-value');
                if (value != original) {
                    updateCategoryField(id, 'sort_order', value, $(this), original);
                }
            });

            table.on('draw', function() {
                $('.sort-order').each(function() {
                    $(this).data('original-value', $(this).val());
                });
            });

            function updateCategoryField(id, field, value, element, revertValue) {
                $.post("{{ url('admin/category') }}/" + id, {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    field,
                    value
                }).done(res => {
                    if (res.success) {
                        toastr.success('Updated successfully');
                        if (field === 'sort_order') element.data('original-value', value);
                    } else {
                        revertElement(element, field, revertValue);
                    }
                }).fail(xhr => {
                    revertElement(element, field, revertValue);
                    toastr.error('Update failed');
                });
            }

            function revertElement(element, field, revertValue) {
                if (field === 'sort_order') {
                    element.val(revertValue);
                } else {
                    element.prop('checked', revertValue);
                }
            }
        });
    </script>
@endsection