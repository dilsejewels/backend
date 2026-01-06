@extends('admin.layouts.master')

@section('main_section')
<div class="container py-4">
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4>Diamond Master</h4>
            <div class="d-flex gap-2">
                <!-- Import/Export Dropdown -->
                <div class="dropdown me-2">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                        id="importExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-import"></i> Import/Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="importExportDropdown">
                        <li>
                            <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                data-bs-target="#importModal">
                                <i class="bx bx-upload me-2"></i> Import Diamonds
                            </button>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('diamond-master.export') }}">
                                <i class="bx bx-download me-2"></i> Export All Diamonds
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('diamond-master.download-sample') }}">
                                <i class="bx bx-file me-2"></i> Download Sample File
                            </a>
                        </li>
                    </ul>
                </div>

                <a href="{{ route('diamond-master.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Add New Diamond
                </a>
            </div>
        </div>
        <div class="card-body">
            <table id="diamondTable" class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor</th>
                        <th>Shape</th>
                        <th>Color</th>
                        <th>Clarity</th>
                        <th>Carat</th>
                        <th>Price/Carat</th>
                        <th>Date added</th>
                        <th>Date Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Diamonds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('diamond-master.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i>
                        Download the sample file to see the required format.
                        Supported formats: .xlsx, .xls, .csv (Max: 10MB)
                    </div>

                    <div class="mb-3">
                        <label for="importFile" class="form-label">Select Excel/CSV File</label>
                        <input type="file" class="form-control" id="importFile" name="import_file" required accept=".xlsx,.xls,.csv">
                        <div class="form-text">
                            Ensure your file includes all required columns.
                        </div>
                    </div>

                    <div class="border p-3 bg-light rounded">
                        <h6 class="mb-2">Required Columns:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="small mb-0">
                                    <li>Stock Number (Required)</li>
                                    <li>Carat Weight (Required)</li>
                                    <li>Shape (Required)</li>
                                    <li>Color (Required)</li>
                                    <li>Clarity (Required)</li>
                                    <li>Cut (Required)</li>
                                    <li>Price (Required)</li>
                                    <li>Price Per Carat (Required)</li>
                                    <li>Vendor Name</li>
                                    <li>Certificate Number</li>
                                    <li>Certificate Company</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="small mb-0">
                                    <li>Polish</li>
                                    <li>Symmetry</li>
                                    <li>Fluorescence</li>
                                    <li>Is Superdeal (Yes/No)</li>
                                    <li>Availability (Available/Sold/On Hold)</li>
                                    <li>Status (Active/Inactive)</li>
                                    <li>Measurements</li>
                                    <li>Measurement L</li>
                                    <li>Measurement W</li>
                                    <li>Measurement H</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-2">
                            <p class="small mb-1"><strong>Note:</strong></p>
                            <ul class="small">
                                <li>For dropdown fields (Shape, Color, Clarity, etc.), use exact values from the system</li>
                                <li>Boolean fields accept: Yes/No, 1/0, True/False</li>
                                <li>Existing diamonds will be updated based on Stock Number or Certificate Number</li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="bx bx-error"></i>
                        <strong>Important:</strong>
                        - Stock Number is used to identify unique diamonds
                        - Required fields must be filled
                        - Invalid rows will be skipped
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="importBtn">
                        <i class="bx bx-upload"></i> Import Diamonds
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
                                <th width="10%">Row</th>
                                <th width="20%">Field</th>
                                <th width="30%">Error</th>
                                <th width="40%">Values</th>
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

<style>
    table.dataTable td.dt-control:before {
        background: #337ab7;
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

<script>
    $(function() {
        $('#diamondTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("diamond-master.data") }}',
                type: 'GET'
            },
            responsive: true,
            scrollX: false,
            columns: [
                { data: 'diamondid' },
                { data: 'vendor.vendor_name', defaultContent: '—' },
                { data: 'shape.name', defaultContent: '—' },
                { data: 'color.name', defaultContent: '—' },
                { data: 'clarity.name', defaultContent: '—' },
                { data: 'carat_weight' },
                { data: 'price_per_carat' },
                { 
                    data: 'date_added',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) : '';
                    }
                },
                { 
                    data: 'date_updated',
                    render: function(data) {
                        return data ? new Date(data).toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) : '';
                    }
                },
                {
                    data: 'diamondid',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                            <a href="DiamondMaster/master/${data}/edit" class="btn btn-sm btn-info">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger deleteBtn" data-id="${data}">
                                <i class="fa fa-trash"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        // Delete handler
        $(document).on('click', '.deleteBtn', function() {
            const diamondId = $(this).data('id');

            if (confirm('Are you sure you want to delete this diamond?')) {
                $.ajax({
                    url: '/admin/diamond-master/' + diamondId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#diamondTable').DataTable().ajax.reload();
                        toastr.success('Diamond deleted successfully!');
                    },
                    error: function(xhr) {
                        toastr.error('Failed to delete the diamond.');
                    }
                });
            }
        });

        // Import form submission
        $('#importForm').on('submit', function(e) {
            $('#importBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Importing...');
        });

        // Auto-close modal after successful import
        $('#importForm').on('submit', function() {
            setTimeout(function() {
                $('#importModal').modal('hide');
                setTimeout(function() {
                    $('#importBtn').prop('disabled', false).html('<i class="bx bx-upload"></i> Import Diamonds');
                }, 2000);
            }, 2000);
        });

        // Show success/error messages
        @if(session('success'))
            var successMessage = @json(session('success'));
            // Replace literal \n with actual newlines for display
            if (typeof successMessage === 'string') {
                successMessage = successMessage.replace(/\\n/g, '\n');
                toastr.success(successMessage);
            }
            
            // Check if there are import errors to show
            @if(session()->has('import_errors') && !empty(session('import_errors')))
                setTimeout(function() {
                    showImportErrors();
                }, 1500);
            @endif
        @endif
        
        @if(session('warning'))
            var warningMessage = @json(session('warning'));
            if (typeof warningMessage === 'string') {
                warningMessage = warningMessage.replace(/\\n/g, '\n');
                toastr.warning(warningMessage);
            }
            
            // Check if there are import errors to show
            @if(session()->has('import_errors') && !empty(session('import_errors')))
                setTimeout(function() {
                    showImportErrors();
                }, 1500);
            @endif
        @endif
        
        @if(session('error'))
            var errorMessage = @json(session('error'));
            if (typeof errorMessage === 'string') {
                errorMessage = errorMessage.replace(/\\n/g, '\n');
                toastr.error(errorMessage);
            }
        @endif
        
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
            url: '{{ route("diamond-master.import-errors") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.count > 0 && response.errors) {
                    // Populate errors table
                    let errorsHtml = '';
                    response.errors.forEach(function(error, index) {
                        errorsHtml += `
                            <tr>
                                <td>${error.row || 'N/A'}</td>
                                <td>${error.attribute || 'N/A'}</td>
                                <td class="text-danger">${error.errors || 'Unknown error'}</td>
                                <td><small>${JSON.stringify(error.values || {})}</small></td>
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
                        let errors = @json(session('import_errors'));
                        errors.forEach(function(error, index) {
                            errorsHtml += `
                                <tr>
                                    <td>${error.row || 'N/A'}</td>
                                    <td>${error.attribute || 'N/A'}</td>
                                    <td class="text-danger">${error.errors || 'Unknown error'}</td>
                                    <td><small>${JSON.stringify(error.values || {})}</small></td>
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
                    let errors = @json(session('import_errors'));
                    errors.forEach(function(error, index) {
                        errorsHtml += `
                            <tr>
                                <td>${error.row || 'N/A'}</td>
                                <td>${error.attribute || 'N/A'}</td>
                                <td class="text-danger">${error.errors || 'Unknown error'}</td>
                                <td><small>${JSON.stringify(error.values || {})}</small></td>
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
        errors.push(['Row', 'Field', 'Error', 'Values']);
        
        // Get rows from table
        $('#errorsTable tbody tr').each(function() {
            let row = [];
            $(this).find('td').each(function() {
                row.push($(this).text().trim());
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
        let fileName = 'diamond_import_errors_' + new Date().toISOString().slice(0,10) + '.csv';
        link.setAttribute("download", fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endsection