@extends('admin.layouts.master')

@section('main_section')
<style>
    table.dataTable td.dt-control:before {
        background: #317cb1;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4 class="mb-3">Product-Category Mapping</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#mappingModal" id="addMappingBtn">Add New</button>
        </div>

        <div class="table-responsive text-nowrap card-body">
            <table class="table table-hover" id="mappingTable">
                <thead>
                    <tr class="bg-light">
                        <th>Action</th>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Category ID</th>
                        <th>Category Name</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="mappingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="mappingForm">
            @csrf
            <input type="hidden" id="old_products_id" name="old_products_id">
            <input type="hidden" id="old_categories_id" name="old_categories_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mapping Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="products_id" class="form-label">Select Product</label>
                        <select class="form-select" id="products_id" name="products_id">
                            <option value="">-- Select Product --</option>
                            @foreach(\App\Models\Product::select('products_id', 'products_name')->get() as $product)
                                <option value="{{ $product->products_id }}">{{ $product->products_name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger" id="error_products_id"></div>
                    </div>

                    <div class="mb-3">
                        <label for="categories_id" class="form-label">Select Category</label>
                        <select class="form-select" id="categories_id" name="categories_id">
                            <option value="">-- Select Category --</option>
                            @foreach(\App\Models\Category::select('category_id', 'category_name')->get() as $cat)
                                <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger" id="error_categories_id"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="saveMappingBtn">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2 text-center">
                <h5 class="modal-title w-100">Are you sure you want to delete it?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-footer p-2 justify-content-center">
                <input type="hidden" id="deleteProductId">
                <input type="hidden" id="deleteCategoryId">
                <button type="button" class="btn btn-danger btn-sm me-2" id="confirmDelete">Yes</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    const mappingModal = new bootstrap.Modal(document.getElementById('mappingModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));

    const mappingTable = $('#mappingTable').DataTable({
        ajax: {
            url: '{{ route("productCategory.index") }}',
            dataSrc: 'data'
        },
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '',
                width: "20px"
            },
            { data: 'products_id' },
            { data: 'product_name' },
            { data: 'categories_id' },
            { data: 'category_name' }
        ]
    });

    $('#mappingTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = mappingTable.row(tr);
        const data = row.data();

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(`
                <div class="d-flex gap-2 p-2">
                    <button class="btn btn-sm btn-info editMappingBtn" data-product="${data.products_id}" data-category="${data.categories_id}">Edit</button>
                    <button class="btn btn-sm btn-danger deleteMappingBtn" data-product="${data.products_id}" data-category="${data.categories_id}">Delete</button>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    $('#addMappingBtn').on('click', function () {
        $('#mappingForm')[0].reset();
        $('#old_products_id, #old_categories_id').val('');
        $('#saveMappingBtn').text('Save');
        $('#error_products_id, #error_categories_id').text('');
        mappingModal.show();
    });

    $('#mappingForm').submit(function (e) {
        e.preventDefault();
        let oldProduct = $('#old_products_id').val();
        let oldCategory = $('#old_categories_id').val();
        let isUpdate = oldProduct !== '' && oldCategory !== '';
        let url = isUpdate
            ? `{{ url('admin/product-category') }}/${oldProduct}/${oldCategory}`
            : `{{ route('productCategory.store') }}`;
        let method = isUpdate ? 'PUT' : 'POST';

        $('#error_products_id, #error_categories_id').text('');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize() + `&_method=${method}`,
            success: function (res) {
                mappingModal.hide();
                mappingTable.ajax.reload();
                toastr.success(res.message);
            },
            error: function (xhr) {
                let errors = xhr.responseJSON?.errors || {};
                if (errors.duplicate) toastr.error(errors.duplicate[0]);
                if (errors.products_id) $('#error_products_id').text(errors.products_id[0]);
                if (errors.categories_id) $('#error_categories_id').text(errors.categories_id[0]);
            }
        });
    });

    $(document).on('click', '.editMappingBtn', function () {
        let pid = $(this).data('product');
        let cid = $(this).data('category');

        $.get(`{{ url('admin/product-category') }}/${pid}/${cid}`, function (data) {
            $('#old_products_id').val(data.products_id);
            $('#old_categories_id').val(data.categories_id);
            $('#products_id').val(data.products_id);
            $('#categories_id').val(data.categories_id);
            $('#saveMappingBtn').text('Update');
            mappingModal.show();
        });
    });

    $(document).on('click', '.deleteMappingBtn', function () {
        $('#deleteProductId').val($(this).data('product'));
        $('#deleteCategoryId').val($(this).data('category'));
        deleteModal.show();
    });

    $('#confirmDelete').on('click', function () {
        const pid = $('#deleteProductId').val();
        const cid = $('#deleteCategoryId').val();
        if (!pid || !cid) return;

        $.ajax({
            url: `/admin/product-category/${pid}/${cid}`,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                deleteModal.hide();
                mappingTable.ajax.reload();
                toastr.success(res.message);
            },
            error: function () {
                deleteModal.hide();
                toastr.error('Failed to delete the mapping!');
            }
        });
    });
});
</script>
@endsection
