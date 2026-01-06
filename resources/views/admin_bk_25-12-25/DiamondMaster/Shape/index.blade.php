@extends('admin.layouts.master')

@section('main_section')
<style>
  table.dataTable td.dt-control:before {
    height: 1em;
    width: 1em;
    margin-top: -9px;
    display: inline-block;
    color: white;
    border: .15em solid white;
    border-radius: 1em;
    box-shadow: 0 0 .2em #444;
    box-sizing: content-box;
    text-align: center;
    text-indent: 0 !important;
    font-family: "Courier New", Courier, monospace;
    line-height: 1em;
    content: "+";
    background-color: #337ab7;
}
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4 class="mb-3">Diamond Shape Master</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shapeModal" id="addshapeBtn">
                Add New
            </button> 
        </div>

        <div class="table-responsive text-nowrap card-body">
            <table class="table table-hover" id="shapeTable">
                <thead>
                    <tr class="bg-light">
                        <th></th> <!-- Expand/collapse column -->
                        <th>ID</th>
                        <th>Name</th>
                        <th>Alias</th>
                        <th>Short Name</th>
                        <th>Image</th>
                        <th>Display In Front</th>
                        <th>Display In Stud</th>
                        <th>Sort Order</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Records will be loaded by JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="shapeModal" tabindex="-1" aria-labelledby="shapeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="shapeForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="record_id" name="id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Shapes Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-2">
                    <div class="col-12">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-6">
                        <label>Alias</label>
                        <input type="text" class="form-control" id="alise" name="ALIAS">
                    </div>
                    <div class="col-6">
                        <label>Short Name</label>
                        <input type="text" class="form-control" id="shortname" name="shortname">
                    </div>
                    
                    <!-- Image Upload Fields -->
                    <div class="col-6">
                        <label>Image</label>
                        <input type="file" class="form-control" id="image" name="image">
                        <div id="image_preview" class="mt-2"></div>
                        <input type="hidden" id="existing_image" name="existing_image">
                    </div>
                    
                    <div class="col-6">
                        <label>Image2</label>
                        <input type="file" class="form-control" id="image2" name="image2">
                        <div id="image2_preview" class="mt-2"></div>
                        <input type="hidden" id="existing_image2" name="existing_image2">
                    </div>
                    
                    <div class="col-6">
                        <label>Image3</label>
                        <input type="file" class="form-control" id="image3" name="image3">
                        <div id="image3_preview" class="mt-2"></div>
                        <input type="hidden" id="existing_image3" name="existing_image3">
                    </div>
                    
                    <div class="col-6">
                        <label>Image4</label>
                        <input type="file" class="form-control" id="image4" name="image4">
                        <div id="image4_preview" class="mt-2"></div>
                        <input type="hidden" id="existing_image4" name="existing_image4">
                    </div>
                    <!-- End Image Upload Fields -->
                    
                    <div class="col-12">
                        <label>SVG Image</label>
                        <textarea class="form-control" id="svg_image" name="svg_image" rows="2"></textarea>
                    </div>
                    <div class="col-6">
                        <label>Remark</label>
                        <input type="number" class="form-control" id="remark" name="remark">
                    </div>
                    <div class="col-6">
                        <label>Display In Front <span class="text-danger">*</span></label>
                        <select class="form-control" id="display_in_front" name="display_in_front" required>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label>Display In Stud</label>
                        <input type="number" class="form-control" id="display_in_stud" name="display_in_stud" value="0">
                    </div>
                    <div class="col-6">
                        <label>Sort Order</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                    </div>
                    <div id="formError" class="text-danger mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary me-2" id="saveShapeBtn">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    const shapeModal = new bootstrap.Modal('#shapeModal');
    let table;

    function initDataTable() {
        table = $('#shapeTable').DataTable({
            ajax: "{{ route('shapes.index') }}",
            columns: [
                {
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: ''
                },
                { data: 'id' },
                { data: 'name' },
                { 
                    data: 'ALIAS',
                    render: data => data ? data.substring(0, 10) + (data.length > 10 ? '...' : '') : ''
                },
                { data: 'shortname' },
                {
                    data: 'image',
                    render: data => data ? `<img src="{{ url('storage/shapes/${data}') }}" width="50">` : ''
                },
                {
                    data: 'display_in_front',
                    render: data => `<input type="checkbox" class="display-in-front" ${data == 1 ? 'checked' : ''}>`
                },
                {
                    data: 'display_in_stud',
                    render: data => `<input type="checkbox" class="display-in-stud" ${data == 1 ? 'checked' : ''}>`
                },
                {
                    data: 'sort_order',
                    render: data => `<input type="number" class="sort-order form-control" value="${data || 0}" style="width:80px">`
                },
            ],
            order: [[1, 'asc']]
        });
    }

    // Add event listener for opening and closing details
    $('#shapeTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = table.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            const data = row.data();
            row.child(`
                <div class="detail-row p-2 bg-light">
                    <div class="text-left small text-muted">
                    <div><strong>Date Added:</strong> ${data.date_added || 'N/A'}</div>
                    <div><strong>Date Modified:</strong> ${data.date_modify || 'N/A'}</div>
                    </div>
                    <div class="d-flex gap-2 justify-flex-start">
                        <button class="btn btn-sm btn-info editBtn" data-id="${data.id}">
                            <i class="fa fa-edit me-1"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.id}">
                            <i class="fa fa-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            `).show();
            tr.addClass('shown');
        }
    });

    // Fetch records and initialize DataTable
    function fetchRecords() {
        if (!$.fn.DataTable.isDataTable('#shapeTable')) {
            initDataTable();
        } else {
            table.ajax.reload();
        }
    }

    // Initialize on page load
    fetchRecords();

    // Function to preview image
    function previewImage(input, previewId, existingId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(previewId).html(`
                    <div class="d-flex align-items-center">
                        <img src="${e.target.result}" class="img-thumbnail mt-2" width="100">
                        <button type="button" class="btn btn-danger btn-sm ms-2 remove-preview">Remove</button>
                    </div>
                `);
            }
            reader.readAsDataURL(input.files[0]);
            $(existingId).val(''); // Clear existing image value
        }
    }

    // Initialize image previews
    $("#image").change(function() { previewImage(this, '#image_preview', '#existing_image'); });
    $("#image2").change(function() { previewImage(this, '#image2_preview', '#existing_image2'); });
    $("#image3").change(function() { previewImage(this, '#image3_preview', '#existing_image3'); });
    $("#image4").change(function() { previewImage(this, '#image4_preview', '#existing_image4'); });

    // Remove image preview
    $(document).on('click', '.remove-preview', function() {
        $(this).closest('.d-flex').remove();
        $(this).siblings('input[type="file"]').val('');
    });

    // Add new button
    $('#addshapeBtn').on('click', function() {
        $('#shapeForm')[0].reset();
        $('[id$="_preview"]').empty();
        $('#record_id').val('');
        $('#formError').text('');
        $('#saveShapeBtn').text('Save');
        shapeModal.show();
    });

    // Edit button
    $(document).on('click', '.editBtn', function() {
        const id = $(this).data('id');
        $.get("{{ url('admin/shapes') }}/" + id, function(data) {
            $('#shapeForm')[0].reset();
            $('[id$="_preview"]').empty();
            $('#formError').text('');
            
            $('#record_id').val(data.id);
            $('#name').val(data.name);
            $('#alise').val(data.ALIAS);
            $('#shortname').val(data.shortname);
            $('#svg_image').val(data.svg_image);
            $('#remark').val(data.remark);
            $('#display_in_front').val(data.display_in_front);
            $('#display_in_stud').val(data.display_in_stud);
            $('#sort_order').val(data.sort_order);
            
            // Set existing images
            const setImagePreview = (field, value) => {
                if (value) {
                    $(`#${field}_preview`).html(`
                        <div class="d-flex align-items-center">
                            <img src="{{ url('storage/shapes/${value}') }}" class="img-thumbnail mt-2" width="100">
                            <button type="button" class="btn btn-danger btn-sm ms-2 remove-existing" data-field="${field}">Remove</button>
                        </div>
                    `);
                    $(`#existing_${field}`).val(value);
                }
            };
            
            setImagePreview('image', data.image);
            setImagePreview('image2', data.image2);
            setImagePreview('image3', data.image3);
            setImagePreview('image4', data.image4);
            
            $('#saveShapeBtn').text('Update');
            shapeModal.show();
        });
    });

    // Remove existing image
    $(document).on('click', '.remove-existing', function() {
        const field = $(this).data('field');
        $(`#${field}_preview`).html('<div class="text-danger">Image will be removed on save</div>');
        $(`#existing_${field}`).val(''); // Clear existing value
    });

    // Form submission
    $('#shapeForm').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = $('#record_id').val();
        const url = id ? `{{ url('admin/shapes') }}/${id}` : "{{ route('shapes.store') }}";
        const $btn = $('#saveShapeBtn');
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');
        formData.append('_method', id ? 'PUT' : 'POST');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                shapeModal.hide();
                fetchRecords();
                toastr.success("Record saved successfully!");
                $btn.prop('disabled', false).html('Save');
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                let errorMsg = '';
                
                // Collect all error messages
                Object.keys(errors).forEach(key => {
                    errorMsg += errors[key].join('<br>') + '<br>';
                });
                
                $('#formError').html(errorMsg || 'An error occurred');
                toastr.error("Failed to save record!");
                $btn.prop('disabled', false).html('Save');
            }
        });
    });
    
    // Delete record
    $(document).on('click', '.deleteBtn', function() {
        if (!confirm('Are you sure you want to delete this record?')) return;
        
        const id = $(this).data('id');
        $.ajax({
            url: `{{ url('admin/shapes') }}/${id}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function(response) {
                fetchRecords();
                toastr.success(response.message || "Record deleted successfully!");
            },
            error: function() {
                toastr.error("Failed to delete record!");
            }
        });
    });
    
    // Update display_in_front status
    $(document).on('change', '.display-in-front', function() {
        const id = $(this).closest('tr').find('.editBtn').data('id');
        const status = $(this).prop('checked') ? 1 : 0;

        $.ajax({
            url: `{{ url('admin/shapes') }}/${id}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                display_in_front: status
            },
            success: function() {
                toastr.success('Status updated successfully!');
            },
            error: function() {
                toastr.error('Failed to update status!');
            }
        });
    });

    // Update display_in_stud status
    $(document).on('change', '.display-in-stud', function() {
        const id = $(this).closest('tr').find('.editBtn').data('id');
        const status = $(this).prop('checked') ? 1 : 0;

        $.ajax({
            url: `{{ url('admin/shapes') }}/${id}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                display_in_stud: status
            },
            success: function() {
                toastr.success('Status updated successfully!');
            },
            error: function() {
                toastr.error('Failed to update status!');
            }
        });
    });

    // Update sort order
    $(document).on('blur', '.sort-order', function() {
        const id = $(this).closest('tr').find('.editBtn').data('id');
        const sortOrder = $(this).val();

        $.ajax({
            url: `{{ url('admin/shapes') }}/${id}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                sort_order: sortOrder
            },
            success: function() {
                toastr.success('Sort order updated successfully!');
            },
            error: function() {
                toastr.error('Failed to update sort order!');
            }
        });
    });
});
</script>
@endsection