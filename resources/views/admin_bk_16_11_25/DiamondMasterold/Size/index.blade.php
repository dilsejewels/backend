@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Size Master</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sizeModal" id="addsizeBtn">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="sizeTable">
                    <thead>
                        <tr class="bg-light">
                            <th>ID</th>
                            <th>Tittle</th>
                            <th>Size1</th>
                            <th>Size2</th>
                            <th>Retailer Id</th>
                            <th>Status</th>
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
    <div class="modal fade" id="sizeModal" tabindex="-1" aria-labelledby="sizeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="sizeForm">
                @csrf
                <input type="hidden" id="record_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">sizes Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-2">
                        <div class="col-6">
                            <label>Size 1</label>
                            <input type="number" step="0.01" class="form-control" id="size1" name="size1">
                        </div>

                        <div class="col-6">
                            <label>Size 2</label>
                            <input type="number" step="0.01" class="form-control" id="size2" name="size2">
                        </div>

                        <div class="col-6">
                            <label>Retailer ID</label>
                            <input type="number" class="form-control" id="retailer_id" name="retailer_id">
                        </div>

                        <div class="col-6">
                            <label>Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label>Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order">
                        </div>
                        <div id="formError" class="text-danger mt-2"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="savesizeBtn">Save</button>
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
                $.get("{{ route('sizes.index') }}", function(response) {
                    let rows = '';
                    response.forEach(r => {
                        rows += `
                            <tr>
                                <td>${r.id}</td>
                                <td>${r.title ?? ''}</td>
                                <td>${r.size1 ?? ''}</td>
                                 <td>${r.size2 ?? ''}</td>
                                  <td>${r.retailer_id ?? ''}</td>
                                <td>
                        <input type="checkbox" ${r.status == 1 ? 'checked' : ''} class="status" data-id="${r.id}">
                    </td>
                   
                   <td>
                     <input type="number" value="${r.sort_order}" class="sort-order" data-id="${r.id}" style="width: 60px;">
                    </td>

                                <td>${r.date_added ? r.date_added :''}</td>
                                <td>${r.date_updated ? r.date_updated :''}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editBtn" data-id="${r.id}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.id}"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    renderDataTable('sizeTable', rows);
                });
            }

            $('#addsizeBtn').on('click', function() {
                $('#sizeForm')[0].reset();
                $('#record_id').val('');
                $('#formError').text('');
                $('#savesizeBtn').text('Save');
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                $.get("{{ url('admin/sizes') }}/" + id, function(data) {
                    $('#sizeForm')[0].reset();
                    $('#formError').text('');
                    $('#record_id').val(data.id);
                    $('#size1').val(data.size1);
                    $('#size2').val(data.size2);
                    $('#retailer_id').val(data.retailer_id);
                    $('#status').val(data.status);
                    $('#sort_order').val(data.sort_order);
                    $('#sizeModal').modal('show');
                    $('#savesizeBtn').text('Update');
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                if (confirm("Are you sure you want to delete this record?")) {
                    $.ajax({
                        url: `/admin/sizes/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            let table = $('#sizeTable').DataTable();
                            const row = table.row($(this).closest('tr'));
                            table.row(row).remove().draw(false); 
                            toastr.success("Record deleted successfully!");
                        },
                        error: function() {
                            toastr.error("Failed to delete the record.");
                        }
                    });
                }
            });

            $('#sizeForm').submit(function(e) {
                e.preventDefault();
                const id = $('#record_id').val();

                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/sizes') }}/${id}` :
                    "{{ route('sizes.store') }}";
                let formData = $(this).serialize();
                formData += `&_method=${method}`;
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function() {
                        $('#sizeModal').modal('hide');
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
            $(document).on('change', '.status', function() {
                const id = $(this).data('id');
                const status = $(this).prop('checked') ? 1 : 0;

                $.ajax({
                    url: `{{ url('admin/sizes') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        status: status
                    },
                    success: function() {
                        toastr.success('Status updated successfully!');
                    },
                    error: function() {
                        toastr.error('Failed to update Status!');
                    }
                });
            });


            $(document).on('blur', '.sort-order', function() {
                const id = $(this).data('id');
                const sortOrder = $(this).val();

                $.ajax({
                    url: `{{ url('admin/sizes') }}/${id}`,
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
