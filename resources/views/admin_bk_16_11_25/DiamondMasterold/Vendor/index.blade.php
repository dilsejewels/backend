@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Vendor Master</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#vendorModal" id="addvendorBtn">
                    Add New
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="vendorTable">
                    <thead>
                        <tr class="bg-light">
                            <th>ID</th>
                            <th>Company Name</th>
                            <th>Vendor Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
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
    <div class="modal fade" id="vendorModal" tabindex="-1" aria-labelledby="vendorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="vendorForm">
                @csrf
                <input type="hidden" id="record_id" name="id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Vendor Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body row g-3">
                        @php
                            $fields = [
                                ['vendor_company_name', 'Company Name'],
                                ['vendor_name', 'Vendor Name'],
                                ['diamond_prefix', 'Diamond Prefix'],
                                ['vendor_email', 'Email', 'email'],
                                ['vendor_phone', 'Phone'],
                                ['vendor_cell', 'Cell'],
                                ['how_hear_about_us', 'How did you hear about us?'],
                                ['other_manufacturer_value', 'Other Manufacturer Value'],
                                ['vendor_status', 'Status', 'select', ['1' => 'Active', '0' => 'Inactive']],
                                ['auto_status', 'Auto Status', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['price_markup_type', 'Markup Type', 'select', ['1' => 'Percentage', '2' => 'Fixed']],
                                ['price_markup_value', 'Markup Value', 'number'],
                                ['fancy_price_markup_value', 'Fancy Markup', 'number'],
                                ['extra_markup', 'Extra Markup', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['extra_markup_value', 'Extra Markup Value', 'number'],
                                [
                                    'fancy_extra_markup',
                                    'Fancy Fancy Extra Markup',
                                    'select',
                                    ['1' => 'Yes', '0' => 'No'],
                                ],
                                ['fancy_extra_markup_value', 'Fancy Extra Markup Value', 'number'],
                                ['delivery_days', 'Delivery Days'],
                                ['additional_shipping_day', 'Additional Shipping Day'],
                                ['additional_rap_discount', 'Additional RAP Discount'],
                                ['notification_email', 'Notification Email', 'email'],
                                ['data_path', 'Data Path'],
                                ['media_path', 'Media Path'],
                                ['external_image', 'Use External Image', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['external_image_path', 'External Image Path'],
                                ['external_image_formula', 'External Image Formula', 'textarea'],
                                ['external_video', 'Use External Video', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['external_video_path', 'External Video Path'],
                                ['external_video_formula', 'External Video Formula', 'textarea'],
                                [
                                    'external_certificate',
                                    'Use External Certificate',
                                    'select',
                                    ['1' => 'Yes', '0' => 'No'],
                                ],
                                ['external_certificate_path', 'Certificate Path'],
                                [
                                    'if_display_vendor_stock_no',
                                    'Display Vendor Stock No',
                                    'select',
                                    ['1' => 'Yes', '0' => 'No'],
                                ],
                                ['vm_diamond_type', 'Diamond Type'],
                                ['show_price', 'Show Price', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['duplicate_feed', 'Duplicate Feed', 'select', ['1' => 'Yes', '0' => 'No']],
                                [
                                    'display_invtry_before_login',
                                    'Display Inventory Before Login',
                                    'select',
                                    ['1' => 'Yes', '0' => 'No'],
                                ],
                                ['vendor_product_group', 'Vendor Product Group', 'textarea'],
                                ['vendor_customer_group', 'Vendor Customer Group', 'textarea'],
                                ['deleted', 'Deleted', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['rank', 'Rank', 'number'],
                                ['buying', 'Buying', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['buy_email', 'Buy Email', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['price_grid', 'Price Grid', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['display_certificate', 'Display Certificate', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['change_status_days', 'Change Status Days'],
                                ['diamond_size_from', 'Diamond Size From', 'number'],
                                ['diamond_size_to', 'Diamond Size To', 'number'],
                                ['allow_color', 'Allow Color'],
                                ['location', 'Location'],
                                ['offer_days', 'Offer Days'],
                                ['keep_price_same_ab', 'Keep Price Same for AB', 'select', ['1' => 'Yes', '0' => 'No']],
                                ['cc_fee', 'CC Fee', 'select', ['1' => 'Yes', '0' => 'No']],                          
                            ];
                        @endphp

                        @foreach ($fields as $field)
                            @php
                                $name = $field[0];
                                $label = $field[1];
                                $type = $field[2] ?? 'text';
                                $options = $field[3] ?? [];
                            @endphp

                            <div class="col-md-4">
                                <label for="{{ $name }}" class="form-label">{{ $label }}</label>

                                @if ($type === 'select')
                                    <select class="form-select" id="{{ $name }}" name="{{ $name }}">
                                        <option value="">-- Select --</option>
                                        @foreach ($options as $val => $option)
                                            <option value="{{ $val }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $type }}" class="form-control" id="{{ $name }}"
                                        name="{{ $name }}" value="{{ old($name) }}">
                                @endif

                                @error($name)
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        @endforeach

                        <div id="formError" class="col-12 text-danger mt-2"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="savevendorBtn">Save</button>
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
                $.get("{{ route('vendor.index') }}", function(response) {
                    let rows = '';
                    response.forEach(r => {
                        rows += `
                            <tr>
                                <td>${r.vendorid}</td>
                                <td>${r.vendor_company_name ?? ''}</td>
                                <td>${r.vendor_name ?? ''}</td>
                                 <td>${r.vendor_email ?? ''}</td>
                                  <td>${r.vendor_phone ?? ''}</td>
                                <td>
                        <input type="checkbox" ${r.vendor_status == 1 ? 'checked' : ''} class="status" data-id="${r.vendorid}">
                    </td>
                            <td>${r.date_addded ? r.date_addded.substring(0, 10) : ''}</td>
                                <td>${r.date_updated ? r.date_updated.substring(0, 10) : ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-info editBtn" data-id="${r.vendorid}"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.vendorid}"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    renderDataTable('vendorTable', rows);
                });
            }

            $('#addvendorBtn').on('click', function() {
                $('#vendorForm')[0].reset();
                $('#record_id').val('');
                $('#formError').text('');
                $('#savevendorBtn').text('Save');
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                console.log(id);
                $.get("{{ url('admin/vendor') }}/" + id, function(data) {
                    $('#vendorForm')[0].reset();
                    $('#formError').text('');
                    $('#record_id').val(data.vendorid);

                    @foreach ($fields as $field)
                        @php
                            $name = $field[0];
                            $type = $field[2] ?? 'text';
                        @endphp

                        @if ($type === 'datetime-local')
                            if (data['{{ $name }}']) {
                                $('#{{ $name }}').val(formatDateForInput(data[
                                    '{{ $name }}']));
                            }
                        @else
                            $('#{{ $name }}').val(data['{{ $name }}'] ?? '');
                        @endif
                    @endforeach

                    $('#vendorModal').modal('show');
                    $('#savevendorBtn').text('Update');
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                if (confirm("Are you sure you want to delete this record?")) {
                    $.ajax({
                        url: `/admin/vendor/${id}`,
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

            $('#vendorForm').submit(function(e) {
                e.preventDefault();
                const id = $('#record_id').val();

                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/vendor') }}/${id}` :
                    "{{ route('vendor.store') }}";
                let formData = $(this).serialize();
                formData += `&_method=${method}`;
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function() {
                        $('#vendorModal').modal('hide');
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
                    url: `{{ url('admin/vendor') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT',
                        vendor_status: status
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
                    url: `{{ url('admin/vendor') }}/${id}`,
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
