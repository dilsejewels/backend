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
                <h4 class="mb-3">Country Management</h4>
                <button class="btn btn-primary btn-sm"id="addCountryBtn"> Add New</button>
            </div>
            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="countryTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Actions</th>
                            <th>ID</th>
                            <th>Country Name</th>
                            <th>ISO Code 2</th>
                            <th>ISO Code 3</th>
                            <th>Phone Code</th>
                            <th>Is Active?</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <!-- Modal for Add/Edit -->
                <div class="modal fade" id="countryModal" tabindex="-1" aria-labelledby="countryModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="countryForm">
                            @csrf
                            <input type="hidden" id="country_id" name="country_id" />
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="countryModalLabel">Add New Country</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="country_name" class="form-label">Country Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="country_name" name="country_name" />
                                        <div class="invalid-feedback" id="error-country_name"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="iso_code_2" class="form-label">ISO Code 2</label>
                                        <input type="text" class="form-control" id="iso_code_2" name="iso_code_2" maxlength="2" />
                                        <div class="invalid-feedback" id="error-iso_code_2"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="iso_code_3" class="form-label">ISO Code 3</label>
                                        <input type="text" class="form-control" id="iso_code_3" name="iso_code_3" maxlength="3" />
                                        <div class="invalid-feedback" id="error-iso_code_3"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone_code" class="form-label">Phone Code</label>
                                        <input type="text" class="form-control" id="phone_code" name="phone_code" maxlength="10" />
                                        <div class="invalid-feedback" id="error-phone_code"></div>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked />
                                        <label for="is_active" class="form-check-label">Is Active?</label>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    let countryModal = new bootstrap.Modal(document.getElementById('countryModal'));
    let form = document.getElementById('countryForm');

    let table = $('#countryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('countries.index') }}",
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '<i class=toggle-icon" style="cursor:pointer;"></i>',
                width: "20px"
            },
            { data: 'country_id', name: 'country_id' },
            { data: 'country_name', name: 'country_name' },
            { data: 'iso_code_2', name: 'iso_code_2' },
            { data: 'iso_code_3', name: 'iso_code_3' },
            { data: 'phone_code', name: 'phone_code' },
            { 
                data: 'is_active', 
                name: 'is_active',
                render: function (data) {
                    return data ? '<span class="badge bg-primary">Yes</span>' : '<span class="badge bg-danger">No</span>';
                }
            },
        ],
        order: [[1, 'asc']]
    });

    // Toggle expand row
    $('#countryTable tbody').on('click', 'td.dt-control', function () {
        let tr = $(this).closest('tr');
        let row = table.row(tr);
        let icon = $(this).find('.toggle-icon');

        if (row.child.isShown()) {
            row.child.hide();
            icon.removeClass('fa-minus').addClass('fa-plus');
        } else {
            let data = row.data();
            let childContent = `
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-info editBtn" data-id="${data.country_id}">Edit</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.country_id}">Delete</button>
                </div>
            `;
            row.child(childContent).show();
            icon.removeClass('fa-plus').addClass('fa-minus');
        }
    });

    function clearValidationErrors() {
        ['country_name', 'iso_code_2', 'iso_code_3', 'phone_code'].forEach(function (field) {
            let input = document.getElementById(field);
            input.classList.remove('is-invalid');
            document.getElementById('error-' + field).innerText = '';
        });
    }

    document.getElementById('addCountryBtn').addEventListener('click', function () {
        form.reset();
        clearValidationErrors();
        document.getElementById('country_id').value = '';
        document.getElementById('countryModalLabel').innerText = 'Add New Country';
        countryModal.show();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearValidationErrors();

        let formData = new FormData(form);
        let url = "{{ route('countries.store') }}";

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(async res => {
            if (res.status === 422) {
                let data = await res.json();
                if(data.errors) {
                    for (const [key, messages] of Object.entries(data.errors)) {
                        const input = document.getElementById(key);
                        const errorDiv = document.getElementById('error-' + key);
                        if(input) input.classList.add('is-invalid');
                        if(errorDiv) errorDiv.innerText = messages[0];
                    }
                }
                throw new Error('Validation error');
            }
            return res.json();
        })
        .then(data => {
            alert(data.message);
            countryModal.hide();
            table.ajax.reload();
        });
    });

    // Edit
    $('#countryTable').on('click', '.editBtn', function () {
        const id = this.getAttribute('data-id');
        clearValidationErrors();

        fetch("{{ url('admin/countries') }}/" + id + "/edit")
        .then(res => res.json())
        .then(data => {
            document.getElementById('country_id').value = data.country_id;
            document.getElementById('country_name').value = data.country_name;
            document.getElementById('iso_code_2').value = data.iso_code_2;
            document.getElementById('iso_code_3').value = data.iso_code_3;
            document.getElementById('phone_code').value = data.phone_code;
            document.getElementById('is_active').checked = data.is_active;
            document.getElementById('countryModalLabel').innerText = 'Edit Country';
            countryModal.show();
        });
    });

    // Delete
    $('#countryTable').on('click', '.deleteBtn', function () {
        if (!confirm('Are you sure you want to delete this country?')) return;
        const id = this.getAttribute('data-id');

        fetch("{{ url('admin/countries') }}/" + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            table.ajax.reload();
        });
    });
});
 </script>
@endsection
