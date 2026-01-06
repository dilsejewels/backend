@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Flourescence Master</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#flourescenceModal" id="addflourescenceBtn">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="flourescenceTable">
                    <thead>
                        <tr class="bg-light">
                            <th>ID</th>
                            <th>Name</th>
                            <th>Alias</th>
                            <th>Short Name</th>
                            <th>Full Name</th>
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
    <div class="modal fade" id="flourescenceModal" tabindex="-1" aria-labelledby="flourescenceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="flourescenceForm">
                @csrf
                <input type="hidden" id="record_id" name="id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">flourescence Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-2">
                        <div class="col-12">
                            <label>Name</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                    
                        <div class="col-6">
                            <label>Alias</label>
                            <input type="text" class="form-control" id="alias" name="alias">
                        </div>
                    
                        <div class="col-6">
                            <label>Short Name</label>
                            <input type="text" class="form-control" id="short_name" name="short_name">
                        </div>
                    
                        <div class="col-12">
                            <label>Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name">
                        </div>
                    
                        <div class="col-6">
                            <label>Status</label>
                            <select class="form-control" id="fluo_status" name="fluo_status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    
                        <div class="col-6">
                            <label>Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order">
                        </div>
                    
                        <div class="col-6">
                            <label>Date Added</label>
                            <input type="datetime-local" class="form-control" id="date_added" name="date_added">
                        </div>
                    
                        <div class="col-6">
                            <label>Date Modified</label>
                            <input type="datetime-local" class="form-control" id="date_modify" name="date_modify">
                        </div>
                    
                        <div id="formError" class="text-danger mt-2"></div>
                    </div>
                    

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="saveflourescenceBtn">Save</button>
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
                $.get("{{ route('flourescence.index') }}", function(response) {
                    let rows = '';
                    response.forEach(r => {
                        rows += `
                            <tr>
                                <td>${r.id}</td>
                                <td>${r.name ?? ''}</td>
                                <td>${r.alias ?? ''}</td>
                                 <td>${r.short_name ?? ''}</td>
                                  <td>${r.full_name ?? ''}</td>
                                <td>
                        <input type="checkbox" ${r.fluo_status == 1 ? 'checked' : ''} class="status" data-id="${r.id}">
                    </td>
                   
                   <td>
                     <input type="number" value="${r.sort_order}" class="sort-order" data-id="${r.id}" style="width: 60px;">
                    </td>

                                <td>${r.date_added ? r.date_added.substring(0, 10) : ''}</td>
                                <td>${r.date_modify ? r.date_modify.substring(0, 10) : ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editBtn" data-id="${r.id}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.id}"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    renderDataTable('flourescenceTable', rows); 
                });
            }

            $('#addflourescenceBtn').on('click', function() {
                $('#flourescenceForm')[0].reset();
                $('#record_id').val('');
                $('#formError').text('');
                $('#saveflourescenceBtn').text('Save');
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                $.get("{{ url('admin/flourescence') }}/" + id, function(data) {
                    $('#flourescenceForm')[0].reset();
                    $('#formError').text('');
                    $('#record_id').val(data.id);
                    $('#name').val(data.name);
                    $('#alias').val(data.alias);
                    $('#short_name').val(data.short_name);
                    $('#full_name').val(data.full_name);
                    $('#fluo_status').val(data.fluo_status);
                    $('#sort_order').val(data.sort_order);
                    $('#date_added').val(formatDateForInput(data.date_added));
                    $('#date_modify').val(formatDateForInput(data.date_modify));
                    $('#flourescenceModal').modal('show');
                    $('#saveflourescenceBtn').text('Update');
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                if (confirm("Are you sure you want to delete this record?")) {
                    $.ajax({
                        url: `/admin/flourescence/${id}`,
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

            $('#flourescenceForm').submit(function(e) {
                e.preventDefault();
                const id = $('#record_id').val();

                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/flourescence') }}/${id}` :
                    "{{ route('flourescence.store') }}";
                let formData = $(this).serialize();
                formData += `&_method=${method}`;
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function() {
                        $('#flourescenceModal').modal('hide');
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
                    url: `{{ url('admin/flourescence') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        fluo_status: status
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
                    url: `{{ url('admin/flourescence') }}/${id}`,
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
