@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond lab Master</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#labModal" id="addlabBtn">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="labTable">
                    <thead>
                        <th>DL ID</th>
                        <th>Name</th>
                        <th>Display In Front</th>
                        <th>Sort Order</th>
                        <th>Image</th>
                        <th>Cert URL</th>
                        <th>Date Added</th>
                        <th>Date Modified</th>
                        <th>Actions</th>
                    </thead>
                    <tbody>
                        {{-- Records loaded via JS --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="labModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="labForm">
                @csrf
                <input type="hidden" id="record_id" name="dl_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Lab Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Form Fields -->
                        <div class="mb-3">
                            <label for="dl_name">Name</label>
                            <input type="text" class="form-control" id="dl_name" name="dl_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="dl_display_in_front">Display In Front</label>
                            <input type="number" class="form-control" id="dl_display_in_front" name="dl_display_in_front"
                                value="0">
                        </div>
                        <div class="mb-3">
                            <label for="dl_sort_order">Sort Order</label>
                            <input type="number" class="form-control" id="dl_sort_order" name="dl_sort_order">
                        </div>
                        <div class="mb-3">
                            <label for="image">Image</label>
                            <input type="text" class="form-control" id="image" name="image"
                                placeholder="Image path or URL" required>
                        </div>
                        <div class="mb-3">
                            <label for="cert_url">Cert URL</label>
                            <input type="text" class="form-control" id="cert_url" name="cert_url">
                        </div>
                        <div id="formError" class="text-danger"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="saveLabBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
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
            fetchlabs();

            function fetchlabs() {
                $.get("{{ route('diamondlab.index') }}", function(response) {
                    let rows = '';
                    response.forEach(record => {
                        rows += `
                    <tr>
                         <td>${record.dl_id}</td>
            <td>${record.dl_name ?? ''}</td>
            <td><input type="checkbox" ${record.dl_display_in_front == 1 ? 'checked' : ''} class="display_in_front" data-id="${record.dl_id}">
</td>
            <td><input type="number" value="${record.dl_sort_order}" class="sort-order" data-id="${record.dl_id}" style="width: 60px;"></td>
            <td>${record.image ?? ''}</td>
              <td>${record.cert_url ?? ''}</td>
              <td>${record.date_added ? record.date_added : ''}</td>
              <td>${record.date_modify ? record.date_modify: ''}</td>
               <td>
              <button class="btn btn-sm btn-info editBtn" data-id="${record.dl_id}"><i class="fa fa-edit"></i></button>
              <button class="btn btn-sm btn-danger deleteBtn" data-id="${record.dl_id}"><i class="fa fa-trash"></i></button>
               </td>
                    </tr>
                `;
                    });
                    renderDataTable('labTable', rows);
                });
            }

            $('#addlabBtn').click(function() {
                $('#labForm')[0].reset();
                $('#record_id').val('');
                $('#saveLabBtn').text('Save');
                $('#formError').text('');
            });

            $(document).on('click', '.editBtn', function() {
                const id = $(this).data('id');
                $.get("{{ url('admin/lab') }}/" + id, function(data) {
                    $('#labForm')[0].reset();
                    $('#record_id').val(data.dl_id);
                    $('#dl_name').val(data.dl_name);
                    $('#dl_display_in_front').val(data.dl_display_in_front);
                    $('#dl_sort_order').val(data.dl_sort_order);
                    $('#image').val(data.image);
                    $('#cert_url').val(data.cert_url);
                    $('#saveLabBtn').text('Update');
                    $('#labModal').modal('show');
                });
            });

            $('#labForm').submit(function(e) {
                e.preventDefault();
                const id = $('#record_id').val();
                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/lab') }}/${id}` :
                    `{{ route('diamondlab.store') }}`;

                // Create FormData object
                const formData = new FormData(this);
                formData.append('_method', method);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#labModal').modal('hide');
                        fetchlabs();
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

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                if (confirm("Are you sure?")) {
                    $.ajax({
                        url: `{{ url('admin/lab') }}/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function() {
                            row.remove();
                            toastr.success("Record deleted successfully!")
                            fetchlabs();
                        },
                        error: function() {
                            toastr.error("Failed to delete the record.");
                        }
                    });
                }
            });
        });
        $(document).on('blur', '.sort-order', function() {
            const id = $(this).data('id');
            const sortOrder = $(this).val();

            $.ajax({
                url: `{{ url('admin/lab') }}/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    dl_sort_order: sortOrder
                },
                success: function() {
                    toastr.success('Sort order updated successfully!');
                },
                error: function() {
                    toastr.error('Failed to update sort order!');
                }
            });
        });


        $(document).on('change', '.display_in_front', function() {
            const id = $(this).data('id');
            const status = $(this).prop('checked') ? 1 : 0;

            $.ajax({
                url: `{{ url('admin/lab') }}/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    dl_display_in_front: status
                },
                success: function() {
                    toastr.success('Status updated successfully!');
                },
                error: function() {
                    toastr.error('Failed to update Status!');
                }
            });
        });
    </script>
@endsection
