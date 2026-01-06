@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond shape Master</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shapeModal" id="addshapeBtn">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="shapeTable">
                    <thead>
                        <tr class="bg-light">
                            <th>ID</th>
                            <th>Name</th>
                            <th>Alise</th>
                            <th>Short Name</th>
                            {{-- <th>Rap Shape</th>
                            <th>Image</th>
                            <th>Image2</th>
                            <th>Image3</th>
                            <th>Image4</th>
                            <th>Svg image</th>
                            <th>Remark</th> --}}
                            <th>Display In Front</th>
                            <th>Display In Stud</th>
                            <th>Sort Order</th>
                            <th>Date Added</th>
                            <th>Date Modify</th>
                            <th>Actions</th>
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
            <form id="shapeForm">
                @csrf
                <input type="hidden" id="record_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Shapes Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-2">
                        <div class="col-12">
                            <label>Name</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                        <div class="col-6">
                            <label>Alise</label>
                            <input type="text" class="form-control" id="alise" name="ALIAS">
                        </div>
                        <div class="col-6">
                            <label>Short Name</label>
                            <input type="text" class="form-control" id="shortname" name="shortname">
                        </div>
                        <div class="col-6">
                            <label>Rap Shape</label>
                            <input type="text" class="form-control" id="rap_shape" name="rap_shape">
                        </div>
                        <div class="col-6">
                            <label>Image</label>
                            <input type="text" class="form-control" id="image" name="image">
                        </div>
                        <div class="col-6">
                            <label>Image2</label>
                            <input type="text" class="form-control" id="image2" name="image2">
                        </div>
                        <div class="col-6">
                            <label>Image3</label>
                            <input type="text" class="form-control" id="image3" name="image3">
                        </div>
                        <div class="col-6">
                            <label>Image4</label>
                            <input type="text" class="form-control" id="image4" name="image4">
                        </div>
                        <div class="col-12">
                            <label>SVG Image</label>
                            <textarea class="form-control" id="svg_image" name="svg_image" rows="2"></textarea>
                        </div>
                        <div class="col-6">
                            <label>Remark</label>
                            <input type="number" class="form-control" id="remark" name="remark">
                        </div>
                        <div class="col-6">
                            <label for="">Display In Front</label>
                            <select class="form-control" id="display_in_front" name="display_in_front">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Display In Stud</label>
                            <input type="number" class="form-control" id="display_in_stud" name="display_in_stud">
                        </div>
                        <div class="col-6">
                            <label>Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order">
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
        function formatDateForInput(dateString) {
            if (!dateString) return "";
            let dt = new Date(dateString);
            let year = dt.getFullYear();
            let month = ("0" + (dt.getMonth() + 1)).slice(-2);
            let day = ("0" + dt.getDate()).slice(-2);
            let hours = ("0" + dt.getHours()).slice(-2);
            let minutes = ("0" + dt.getMinutes()).slice(-2);
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        $(document).ready(function() {
            fetchRecords();

            function fetchRecords() {
                $.get("{{ route('shapes.index') }}", function(response) {
                    let rows = '';
                    response.forEach(r => {
                        rows += `
                            <tr>
                                <td>${r.id}</td>
                                <td>${r.name ?? ''}</td>
                               <td>${r.ALIAS ? r.ALIAS.substring(0, 10) + (r.ALIAS.length > 10 ? '...' : '') : ''}</td>
                                <td>${r.shortname ?? ''}</td>
                                <td>
                        <input type="checkbox" ${r.display_in_front == 1 ? 'checked' : ''} class="display-in-front" data-id="${r.id}">
                    </td>
                    <td>
                        <input type="checkbox" ${r.display_in_stud == 1 ? 'checked' : ''} class="display-in-stud" data-id="${r.id}">
                    </td>
                   <td>
                     <input type="number" value="${r.sort_order}" class="sort-order" data-id="${r.id}" style="width: 60px;">
                    </td>

                                <td>${r.date_added ? r.date_added : ''}</td>
                                <td>${r.date_modify ? r.date_modify: ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editBtn" data-id="${r.id}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.id}"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    renderDataTable('shapeTable', rows);
                  
                });
            }

            $('#addshapeBtn').on('click', function() {
                $('#shapeForm')[0].reset();
                $('#record_id').val('');
                $('#formError').text('');
                $('##saveShapeBtn').text('Save');
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                $.get("{{ url('admin/shapes') }}/" + id, function(data) {
                    $('#shapeForm')[0].reset();
                    $('#formError').text('');
                    $('#record_id').val(data.id);
                    $('#name').val(data.name);
                    $('#alise').val(data.ALIAS);
                    $('#shortname').val(data.shortname);
                    $('#rap_shape').val(data.rap_shape);
                    $('#image').val(data.image);
                    $('#image2').val(data.image2);
                    $('#image3').val(data.image3);
                    $('#image4').val(data.image4);
                    $('#svg_image').val(data.svg_image);
                    $('#remark').val(data.remark);
                    $('#display_in_front').val(data.display_in_front);
                    $('#display_in_stud').val(data.display_in_stud);
                    $('#sort_order').val(data.sort_order);
                    $('#shapeModal').modal('show');
                    $('#saveShapeBtn').text('Update');
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                if (confirm("Are you sure you want to delete this record?")) {
                    $.ajax({
                        url: `/admin/shapes/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            row.remove();
                            toastr.success("Record deleted successfully!");
                            fetchRecords();
                        },
                        error: function() {
                            toastr.error("Failed to delete the record.");
                        }
                    });
                }
            });

            $('#shapeForm').submit(function(e) {
                e.preventDefault();
                const id = $('#record_id').val();

                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/shapes') }}/${id}` :
                    "{{ route('shapes.store') }}";
                let formData = $(this).serialize();
                formData += `&_method=${method}`;
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function() {
                        $('#shapeModal').modal('hide');
                        fetchRecords();
                        toastr.success("Record saved successfully!");
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors || {};
                        let msg = Object.values(errors).join('<br>');
                        $('#formError').html(msg || 'An error occurred');
                        toastr.error("Failed to save record!");
                    }
                });
            });
            $(document).on('change', '.display-in-front', function() {
                const id = $(this).data('id');
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
                        toastr.success('Display in Front updated successfully!');
                    },
                    error: function() {
                        toastr.error('Failed to update Display in Front!');
                    }
                });
            });

            $(document).on('change', '.display-in-stud', function() {
                const id = $(this).data('id');
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
                        toastr.success('Display in Stu updated successfully!');
                    },
                    error: function() {
                        toastr.error('Failed to update Display in Stu!');
                    }
                });
            });

            $(document).on('blur', '.sort-order', function() {
                const id = $(this).data('id');
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
