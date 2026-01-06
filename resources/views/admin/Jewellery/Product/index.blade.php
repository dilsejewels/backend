@extends('admin.layouts.master')

@section('main_section')
<style>
    table.dataTable td.dt-control:before {
        background: #337ab7;
    }
    .sku-filter {
        max-width: 200px;
    }
    #errorsTable th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    #errorsTable tr:hover {
        background-color: #f8f9fa;
    }
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.2em;
    }
    #errorsTable th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    #errorsTable tr:hover {
        background-color: #f8f9fa;
    }
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.2em;
    }
    /* Fix for long error messages */
    #errorsTable td {
        max-width: 300px;
        word-wrap: break-word;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Product Management</h4>
      <div class="d-flex gap-2 align-items-center">
        <input type="text" id="skuFilter" class="form-control sku-filter" placeholder="Filter by SKU...">
        
        <!-- Import/Export Dropdown Button -->
        <div class="dropdown me-2">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="importExportDropdown" 
                    data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-import"></i> Import/Export
            </button>
            <ul class="dropdown-menu" aria-labelledby="importExportDropdown">
                <!-- Combined Options -->
                <li><h6 class="dropdown-header">Combined Import/Export</h6></li>
                <li>
                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importCombinedModal">
                        <i class="bx bx-upload me-2"></i> Import Products & Variations
                    </button>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('products.export-combined') }}">
                        <i class="bx bx-download me-2"></i> Export All (Products + Variations)
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('products.download-combined-sample') }}">
                        <i class="bx bx-file me-2"></i> Combined Sample File
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- Separate Options -->
                {{-- <li><h6 class="dropdown-header">Separate Import/Export</h6></li>
                <li>
                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importProductsModal">
                        <i class="bx bx-upload me-2"></i> Import Products Only
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importVariationsModal">
                        <i class="bx bx-upload me-2"></i> Import Variations Only
                    </button>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('products.export') }}">
                        <i class="bx bx-download me-2"></i> Export Products Only
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('products.export-variations') }}">
                        <i class="bx bx-download me-2"></i> Export Variations Only
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- Sample Files -->
                <li><h6 class="dropdown-header">Sample Files</h6></li>
                <li>
                    <a class="dropdown-item" href="{{ route('products.download-sample', 'products') }}">
                        <i class="bx bx-file me-2"></i> Products Sample
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('products.download-sample', 'variations') }}">
                        <i class="bx bx-file me-2"></i> Variations Sample
                    </a>
                </li> --}}
            </ul>
        </div>
        
        <a href="{{ route('product.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> Add New Product
        </a>
      </div>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table id="productTable" class="table table-hover">
        <thead class="bg-light">
          <tr>
            <th></th> 
            <th>#</th> 
            <th>Image</th>
            <th>Name</th> 
            <th>Category</th>      
            <th>SKU</th>
            <th>Status</th>      
            <th>Added Date</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!-- Combined Import Modal -->
<div class="modal fade" id="importCombinedModal" tabindex="-1" aria-labelledby="importCombinedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importCombinedModalLabel">Import Products & Variations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('products.import-combined') }}" method="POST" enctype="multipart/form-data" id="combinedImportForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i> 
                        Download the sample file to see the required format.
                        File must contain two sheets: "Products" and "Variations".
                        Supported formats: .xlsx, .xls (Max: 10MB)
                    </div>
                    
                    <div class="mb-3">
                        <label for="combinedFile" class="form-label">Select Excel File</label>
                        <input type="file" class="form-control" id="combinedFile" name="import_file" required accept=".xlsx,.xls">
                        <div class="form-text">
                            Excel file must contain two sheets named "Products" and "Variations"
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Products Sheet Format</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small mb-2"><strong>Required Columns:</strong></p>
                                    <ul class="small">
                                        <li>products_name (Required)</li>
                                        <li>products_description</li>
                                        <li>products_short_description</li>
                                        <li>gender (0=Man, 1=Woman)</li>
                                        <li>bond (0=Metal, 1=Diamond)</li>
                                        <li>available (yes/no)</li>
                                        <li>products_quantity</li>
                                        <li>products_model</li>
                                        <li>products_weight</li>
                                        <li>products_status (0/1)</li>
                                        <li>products_slug (auto-generated if empty)</li>
                                        <li>vendor_name</li>
                                        <li>category_name</li>
                                        <li>parent_category_id</li>
                                    </ul>
                                    <p class="small mb-2"><strong>Optional Fields:</strong></p>
                                    <ul class="small">
                                        <li>psc_id</li>
                                        <li>product_collection_id</li>
                                        <li>product_style_group_id</li>
                                        <li>country_of_origin</li>
                                        <li>products_tax_class_id</li>
                                        <li>products_tax</li>
                                        <li>is_bestseller (0/1)</li>
                                        <li>is_featured (0/1)</li>
                                        <li>ready_to_ship (0/1)</li>
                                        <li>is_collection (0/1)</li>
                                        <li>diamond_weight_group_id</li>
                                        <li>diamond_quality_id</li>
                                        <li>diamond_clarity_id</li>
                                        <li>diamond_color_id</li>
                                        <li>diamond_cut_id</li>
                                        <li>center_stone_type_id</li>
                                        <li>stone_type_id</li>
                                        <li>metal_type_id</li>
                                        <li>metal_color_name</li>
                                        <li>metal_weight</li>
                                        <li>is_build_product (0/1)</li>
                                        <li>shape_ids</li>
                                        <li>build_product_type</li>
                                        <li>certified_lab</li>
                                        <li>certificate_number</li>
                                        <li>products_meta_title</li>
                                        <li>products_meta_description</li>
                                        <li>products_meta_keyword</li>
                                        <li>delivery_days</li>
                                        <li>deleted (0/1)</li>
                                        <li>sort_order</li>
                                        <li>shop_zone_id</li>
                                        <li>is_sale (0/1)</li>
                                        <li>is_gift (0/1)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">Variations Sheet Format</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small mb-2"><strong>Required Columns:</strong></p>
                                    <ul class="small">
                                        <li>product_id OR product_name (to link with Products sheet)</li>
                                        <li>price</li>
                                        <li>regular_price</li>
                                        <li>stock</li>
                                        <li>weight</li>
                                        <li>shape_name</li>
                                        <li>diamond_weight</li>
                                        <li>diamond_quality_name</li>
                                        <li>metal_color_name</li>
                                        <li>is_best_selling (0/1)</li>
                                    </ul>
                                    <p class="small mb-2"><strong>Optional Columns:</strong></p>
                                    <ul class="small">
                                        <li>sku (auto-generated if empty)</li>
                                        <li>carat</li>
                                    </ul>
                                    <p class="small mb-2"><strong>Note:</strong></p>
                                    <ul class="small">
                                        <li>Product must exist in Products sheet</li>
                                        <li>Missing shapes/metal colors will be skipped</li>
                                        <li>SKU auto-generated if not provided</li>
                                        <li>All other fields are NOT required</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="bx bx-error"></i> 
                        <strong>Important:</strong> Import is processed in transaction. 
                        If any row fails, entire import will be rolled back for that product/variation.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="combinedImportBtn">
                        <i class="bx bx-upload"></i> Import All Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Products Modal -->
<div class="modal fade" id="importProductsModal" tabindex="-1" aria-labelledby="importProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importProductsModalLabel">Import Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i> 
                        Download the sample file to see the required format.
                        Supported formats: .xlsx, .xls, .csv (Max: 10MB)
                    </div>
                    
                    <div class="mb-3">
                        <label for="productsFile" class="form-label">Select Excel/CSV File</label>
                        <input type="file" class="form-control" id="productsFile" name="import_file" required accept=".xlsx,.xls,.csv">
                        <div class="form-text">
                            Ensure your file includes all required columns.
                        </div>
                    </div>
                    
                    <div class="border p-3 bg-light rounded">
                        <h6 class="mb-2">Required Columns for Products:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="small mb-0">
                                    <li>products_name</li>
                                    <li>products_description</li>
                                    <li>products_short_description</li>
                                    <li>gender (0=Man, 1=Woman)</li>
                                    <li>bond (0=Metal, 1=Diamond)</li>
                                    <li>available (yes/no)</li>
                                    <li>products_quantity</li>
                                    <li>products_model</li>
                                    <li>products_weight</li>
                                    <li>products_status (0/1)</li>
                                    <li>products_slug</li>
                                    <li>vendor_name</li>
                                    <li>category_name</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="small mb-0">
                                    <li>parent_category_id</li>
                                    <li>is_sale (0/1)</li>
                                    <li>is_gift (0/1)</li>
                                    <li>is_bestseller (0/1)</li>
                                    <li>is_featured (0/1)</li>
                                    <li>ready_to_ship (0/1)</li>
                                    <li>is_collection (0/1)</li>
                                    <li>delivery_days</li>
                                    <li>deleted (0/1)</li>
                                    <li>sort_order</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-2">
                            <p class="small mb-1"><strong>Optional Fields:</strong> All other product fields are optional</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-upload"></i> Import Products
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Variations Modal -->
<div class="modal fade" id="importVariationsModal" tabindex="-1" aria-labelledby="importVariationsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importVariationsModalLabel">Import Product Variations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('products.import-variations') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i> 
                        Product must exist before importing variations.
                        Supported formats: .xlsx, .xls, .csv (Max: 10MB)
                    </div>
                    
                    <div class="mb-3">
                        <label for="variationsFile" class="form-label">Select Excel/CSV File</label>
                        <input type="file" class="form-control" id="variationsFile" name="import_file" required accept=".xlsx,.xls,.csv">
                        <div class="form-text">
                            Ensure product exists in the system.
                        </div>
                    </div>
                    
                    <div class="border p-3 bg-light rounded">
                        <h6 class="mb-2">Required Columns for Variations:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="small mb-0">
                                    <li>product_id OR product_name</li>
                                    <li>price</li>
                                    <li>regular_price</li>
                                    <li>stock</li>
                                    <li>weight</li>
                                    <li>shape_name</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="small mb-0">
                                    <li>diamond_weight</li>
                                    <li>diamond_quality_name</li>
                                    <li>metal_color_name</li>
                                    <li>is_best_selling (0/1)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-2">
                            <p class="small mb-1"><strong>Optional Fields:</strong> sku, carat</p>
                            <p class="small mb-0"><strong>Note:</strong> vendor_id, category_name, master_sku are NOT required</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-upload"></i> Import Variations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Errors Modal -->
<div class="modal fade" id="importErrorsModal" tabindex="-1" aria-labelledby="importErrorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importErrorsModalLabel">Import Errors</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bx bx-error"></i> 
                    The following errors occurred during import. These rows were not imported.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="errorsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">Sheet</th>
                                <th width="15%">Row/Identifier</th>
                                <th>Error Message</th>
                            </tr>
                        </thead>
                        <tbody id="errorsBody">
                            <!-- Errors will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="exportErrorsToExcel()">
                    <i class="bx bx-download"></i> Export Errors to Excel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const productEditUrl = "{{ url('/admin/') }}";
    
    function format(rowData) {
        const deleteUrl = "{{ route('product.destroy', ['id' => ':id']) }}".replace(':id', rowData.products_id);
        return `
        <div class="d-flex">
            <a href="${productEditUrl}/${rowData.products_id}/edit" class="btn btn-sm btn-primary me-2">Edit</a>
            <button data-url="${deleteUrl}" class="btn btn-sm btn-danger deleteBtn">Delete</button>
        </div>
        `;
    }

    $(document).ready(function () {
        var table = $('#productTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("product.index") }}',
                data: function (d) {
                    d.sku_filter = $('#skuFilter').val();
                }
            },
            columns: [
                {
                    className: 'dt-control',
                    orderable: false,
                    searchable: false,
                    data: null,
                    defaultContent: '',
                },
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'product_image',
                    name: 'product_image',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'products_name',
                    name: 'products_name',
                    render: function (data) {
                        return data?.length > 15 ? data.substring(0, 15) + '...' : data;
                    }
                },
                {
                    data: 'category_name',
                    name: 'category_name'
                },
                {
                    data: 'sku',
                    name: 'sku'
                },
                {
                    data: 'products_status',
                    name: 'products_status'
                },
                {
                    data: 'date_added',
                    name: 'date_added'
                },
            ],
            order: [[1, 'asc']],
            columnDefs: [
                {
                    targets: 2, // Image column
                    render: function (data) {
                        return data;
                    }
                },
                {
                    targets: 5, // SKU column
                    render: function (data) {
                        return data;
                    }
                },
                {
                    targets: 6, // Status
                    render: function (data) {
                        return data;
                    }
                },
                {
                    targets: 7, // Date
                    render: function (data) {
                        return data ? new Date(data).toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) : '';
                    }
                }
            ]
        });

        // SKU filter functionality
        $('#skuFilter').on('keyup', function() {
            table.ajax.reload();
        });

        // Add event listener for opening and closing details
        $('#productTable tbody').on('click', 'td.dt-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
        });

        // Delete button click handler
        $('#productTable tbody').on('click', '.deleteBtn', function() {
            const url = $(this).data('url'); 

            if(confirm('Are you sure you want to delete this product?')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(response) {
                        // Show toast notification
                        toastr[response.type](response.message);
                        
                        // Reload datatable
                        table.ajax.reload(null, false); 
                    },
                    error: function(xhr) {
                        // Show error toast
                        const message = xhr.responseJSON?.message || 'Failed to delete product';
                        toastr.error(message);
                    }
                });
            }
        });
        
        // Show success/error messages
        @if(session('success'))
            var successMessage = {!! json_encode(session('success')) !!};
            // Replace literal \n with actual newlines for display
            successMessage = successMessage.replace(/\\n/g, '\n');
            toastr.success(successMessage);
            
            // Check if there are import errors to show
            @if(session()->has('import_errors') && !empty(session('import_errors')))
                setTimeout(function() {
                    showImportErrors();
                }, 1500);
            @endif
        @endif
        
        @if(session('warning'))
            var warningMessage = {!! json_encode(session('warning')) !!};
            warningMessage = warningMessage.replace(/\\n/g, '\n');
            toastr.warning(warningMessage);
            
            // Check if there are import errors to show
            @if(session()->has('import_errors') && !empty(session('import_errors')))
                setTimeout(function() {
                    showImportErrors();
                }, 1500);
            @endif
        @endif
        
        @if(session('error'))
            var errorMessage = {!! json_encode(session('error')) !!};
            errorMessage = errorMessage.replace(/\\n/g, '\n');
            toastr.error(errorMessage);
        @endif
        
        // Combined import form submission
        $('#combinedImportForm').on('submit', function(e) {
            $('#combinedImportBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Importing...');
        });
        
        // Auto-close modals after successful import
        $('form').on('submit', function() {
            setTimeout(function() {
                $('#importCombinedModal, #importProductsModal, #importVariationsModal').modal('hide');
                // Reset button after modal closes
                setTimeout(function() {
                    $('#combinedImportBtn').prop('disabled', false).html('<i class="bx bx-upload"></i> Import All Data');
                }, 2000);
            }, 2000);
        });
        
        // Auto-show errors modal if there are errors in session
        @if(session()->has('import_errors') && !empty(session('import_errors')))
            @if(!session('success') && !session('warning') && !session('error'))
                $(document).ready(function() {
                    showImportErrors();
                });
            @endif
        @endif
    });
    
    function showImportErrors() {
        $.ajax({
            url: '{{ route("products.import-errors") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.count > 0 && response.errors) {
                    // Populate errors table
                    let errorsHtml = '';
                    response.errors.forEach(function(error, index) {
                        errorsHtml += `
                            <tr>
                                <td>${error.sheet || 'N/A'}</td>
                                <td>${error.row || 'N/A'}</td>
                                <td class="text-danger">${error.error || 'Unknown error'}</td>
                            </tr>
                        `;
                    });
                    $('#errorsBody').html(errorsHtml);
                    
                    // Show modal
                    $('#importErrorsModal').modal('show');
                } else {
                    // Check session for errors
                    @if(session()->has('import_errors'))
                        let errorsHtml = '';
                        let errors = {!! json_encode(session('import_errors')) !!};
                        errors.forEach(function(error, index) {
                            errorsHtml += `
                                <tr>
                                    <td>${error.sheet || 'N/A'}</td>
                                    <td>${error.row || 'N/A'}</td>
                                    <td class="text-danger">${error.error || 'Unknown error'}</td>
                                </tr>
                            `;
                        });
                        $('#errorsBody').html(errorsHtml);
                        $('#importErrorsModal').modal('show');
                    @endif
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load import errors:', error);
                // Fallback to session
                @if(session()->has('import_errors'))
                    let errorsHtml = '';
                    let errors = {!! json_encode(session('import_errors')) !!};
                    errors.forEach(function(error, index) {
                        errorsHtml += `
                            <tr>
                                <td>${error.sheet || 'N/A'}</td>
                                <td>${error.row || 'N/A'}</td>
                                <td class="text-danger">${error.error || 'Unknown error'}</td>
                            </tr>
                        `;
                    });
                    $('#errorsBody').html(errorsHtml);
                    $('#importErrorsModal').modal('show');
                @else
                    toastr.error('Failed to load import errors');
                @endif
            }
        });
    }
    
    function exportErrorsToExcel() {
        let errors = [];
        
        // Add headers
        errors.push(['Sheet', 'Row/Identifier', 'Error Message']);
        
        // Get rows from table
        $('#errorsTable tbody tr').each(function() {
            let row = [];
            $(this).find('td').each(function() {
                row.push($(this).text());
            });
            errors.push(row);
        });
        
        if (errors.length <= 1) { // Only header row
            toastr.warning('No errors to export');
            return;
        }
        
        // Convert to CSV
        let csvContent = "data:text/csv;charset=utf-8,";
        errors.forEach(function(rowArray) {
            let row = rowArray.map(function(cell) {
                // Escape quotes and wrap in quotes if contains comma or quotes
                if (typeof cell === 'string' && (cell.includes(',') || cell.includes('"') || cell.includes('\n'))) {
                    cell = '"' + cell.replace(/"/g, '""') + '"';
                }
                return cell;
            }).join(",");
            csvContent += row + "\r\n";
        });
        
        // Download
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        let fileName = 'import_errors_' + new Date().toISOString().slice(0,10) + '.csv';
        link.setAttribute("download", fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

@endsection