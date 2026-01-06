@extends('admin.layouts.master')

@section('main_section')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Metal Prices</h4>
            <div>
                {{-- <a href="{{ route('metal-prices.update-products.form') }}" class="btn btn-success me-2">
                    <i class="fas fa-sync-alt me-1"></i> Update Product Prices
                </a> --}}
                <button class="btn btn-primary" id="createMetalPriceBtn">
                    <i class="fas fa-plus me-1"></i> Add New Metal Price
                </button>
            </div>
        </div>
        <div class="card-body table-responsive text-nowrap">
            <table id="metalPricesTable" class="table table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Metal Quality</th>
                        <th>Metal Type</th>
                        <th>Price/Gram ($)</th>
                        <th>Price/10g ($)</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="metalPriceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="metalPriceForm" class="modal-content">
            @csrf
            <input type="hidden" name="id" id="metalPriceId">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Metal Price Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control">
                            <small class="text-danger error-date"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metal Quality</label>
                            <input type="text" name="metal_quality" id="metal_quality" class="form-control" placeholder="e.g., 24K, 22K, 18K, 14K, 925, 999 etc.">
                            <small class="text-danger error-metal_quality"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Metal Type <span class="text-danger">*</span></label>
                            <select name="metal_type" id="metal_type" class="form-control">
                                <option value="">Select Metal Type</option>

                                <!-- 🟡 GOLD -->
                                <optgroup label="Yellow Gold">
                                    <option value="14K Yellow Gold">14K Yellow Gold</option>
                                    <option value="18K Yellow Gold">18K Yellow Gold</option>
                                    <option value="22K Yellow Gold">22K Yellow Gold</option>
                                    <option value="24K Yellow Gold">24K Yellow Gold</option>
                                </optgroup>

                                <optgroup label="White Gold">
                                    <option value="14K White Gold">14K White Gold</option>
                                    <option value="18K White Gold">18K White Gold</option>
                                    <option value="22K White Gold">22K White Gold</option>
                                </optgroup>

                                <optgroup label="Rose Gold">
                                    <option value="14K Rose Gold">14K Rose Gold</option>
                                    <option value="18K Rose Gold">18K Rose Gold</option>
                                    <option value="22K Rose Gold">22K Rose Gold</option>
                                </optgroup>

                                <optgroup label="Green Gold">
                                    <option value="14K Green Gold">14K Green Gold</option>
                                    <option value="18K Green Gold">18K Green Gold</option>
                                    <option value="22K Green Gold">22K Green Gold</option>
                                </optgroup>

                                <!-- ⚪ SILVER -->
                                <optgroup label="Silver">
                                    <option value="Fine Silver (999)">Fine Silver (999)</option>
                                    <option value="Sterling Silver (925)">Sterling Silver (925)</option>
                                    <option value="Argentium Silver">Argentium Silver</option>
                                    <option value="Coin Silver (900)">Coin Silver (900)</option>
                                    <option value="Silver Plated">Silver Plated</option>
                                </optgroup>

                                <!-- ⚫ PLATINUM -->
                                <optgroup label="Platinum">
                                    <option value="950 Platinum">950 Platinum</option>
                                    <option value="900 Platinum">900 Platinum</option>
                                    <option value="Platinum Plated">Platinum Plated</option>
                                </optgroup>

                                <!-- ⚙️ OTHERS -->
                                <optgroup label="Other Metals">
                                    <option value="Palladium">Palladium</option>
                                    <option value="Titanium">Titanium</option>
                                    <option value="Tungsten">Tungsten</option>
                                    <option value="Rhodium">Rhodium</option>
                                    <option value="Stainless Steel">Stainless Steel</option>
                                </optgroup>
                            </select>
                            <small class="text-danger error-metal_type"></small>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">Price per Gram ($) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_gram" id="price_per_gram" class="form-control" step="0.01" min="0" placeholder="0.00">
                            <small class="text-danger error-price_per_gram"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price per 10 Gram ($) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_10gram" id="price_per_10gram" class="form-control" step="0.01" min="0" placeholder="0.00">
                            <small class="text-danger error-price_per_10gram"></small>
                        </div>
                    </div>
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
        const metalPricesTable = $('#metalPricesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('metal-prices.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'date', name: 'date' },
                { data: 'metal_quality', name: 'metal_quality' },
                { data: 'metal_type', name: 'metal_type' },
                { data: 'price_per_gram', name: 'price_per_gram' },
                { data: 'price_per_10gram', name: 'price_per_10gram' },
                { data: 'created_at', name: 'created_at' },
                { 
                    data: 'action', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false 
                }
            ],
            order: [[0, 'asc']],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"></div> Loading...'
            }
        });

        // Create new metal price
        $('#createMetalPriceBtn').click(() => {
            $('#metalPriceForm')[0].reset();
            $('#metalPriceId').val('');
            $('.text-danger').text('');
            $('#modalTitle').text('Add New Metal Price');
            $('#date').val(new Date().toISOString().split('T')[0]);
            $('#metalPriceModal').modal('show');
        });

        // Edit metal price
        $(document).on('click', '.editMetalPriceBtn', function () {
            const id = $(this).data('id');
            $.get("{{ route('metal-prices.edit', ['id' => ':id']) }}".replace(':id', id), function (data) {
                $('#metalPriceId').val(data.id);
                $('#date').val(data.date);
                $('#metal_quality').val(data.metal_quality);
                $('#metal_type').val(data.metal_type);
                $('#price_per_gram').val(data.price_per_gram);
                $('#price_per_10gram').val(data.price_per_10gram);
                
                $('.text-danger').text('');
                $('#modalTitle').text('Edit Metal Price');
                $('#metalPriceModal').modal('show');
            }).fail(function() {
                toastr.error('Failed to fetch metal price details!');
            });
        });

        // Update product prices from table
        $(document).on('click', '.updateProductPricesBtn', function () {
            const id = $(this).data('id');
            const metalType = $(this).data('metal-type');
            
            if(confirm(`Update product prices for ${metalType}?`)) {
                showLoading();
                $.ajax({
                    url: "{{ route('metal-prices.update-products') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        metal_price_id: id
                    },
                    success: function(response) {
                        hideLoading();
                        if(response.status) {
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errorMessage = 'Something went wrong!';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error('Error: ' + errorMessage);
                    }
                });
            }
        });

        // Save metal price (create/update)
        $('#metalPriceForm').submit(function (e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const id = $('#metalPriceId').val();
            const url = id ? "{{ route('metal-prices.update', ['id' => ':id']) }}".replace(':id', id) 
                            : "{{ route('metal-prices.store') }}";
            const method = id ? 'PUT' : 'POST';

            $('.text-danger').text('');
            $('#saveBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                headers: {
                    'X-HTTP-Method-Override': method
                },
                success: function(res) {
                    $('#saveBtn').prop('disabled', false).html('Save');
                    if (res.status) {
                        $('#metalPriceModal').modal('hide');
                        toastr.success(res.message);
                        metalPricesTable.ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    $('#saveBtn').prop('disabled', false).html('Save');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        for (let field in errors) {
                            $(`.error-${field}`).text(errors[field][0]);
                        }
                    } else {
                        toastr.error('Failed to save metal price!');
                    }
                }
            });
        });

        // Delete metal price
        $(document).on('click', '.deleteMetalPriceBtn', function () {
            if (!confirm('Are you sure you want to delete this metal price?')) return;
            
            const id = $(this).data('id');
            $.ajax({
                url: "{{ route('metal-prices.destroy', ['id' => ':id']) }}".replace(':id', id),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status) {
                        toastr.success(res.message);
                        metalPricesTable.ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to delete metal price!');
                }
            });
        });

        // Auto-calculate price per 10 gram when price per gram changes
        $('#price_per_gram').on('input', function() {
            const pricePerGram = parseFloat($(this).val()) || 0;
            $('#price_per_10gram').val((pricePerGram * 10).toFixed(2));
        });

        // Auto-calculate price per gram when price per 10 gram changes
        $('#price_per_10gram').on('input', function() {
            const pricePer10Gram = parseFloat($(this).val()) || 0;
            $('#price_per_gram').val((pricePer10Gram / 10).toFixed(2));
        });

        // Loading functions
        function showLoading() {
            $('body').append(`
                <div class="loading-overlay">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-white">Updating prices...</p>
                </div>
            `);
        }

        function hideLoading() {
            $('.loading-overlay').remove();
        }
    });
</script>

<style>
    .dt-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .table td, .table th {
        vertical-align: middle;
    }
    
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-overlay .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    .loading-overlay p {
        font-size: 1.1rem;
        margin-top: 1rem;
    }

    .btn-sm {
        margin: 2px;
    }
</style>
@endsection