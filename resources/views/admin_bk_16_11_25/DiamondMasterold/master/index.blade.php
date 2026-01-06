@extends('admin.layouts.master')

@section('main_section')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-3">Diamond Master</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#diamondModal" id="addDiamondBtn">
                    Add New Diamond
                </button>
            </div>

            <div class="table-responsive text-nowrap card-body">
                <table class="table table-hover" id="diamondTable">
                    <thead>
                        <tr class="bg-light">
                            <th>ID</th>
                            <th>Vendor</th>
                            <th>Shape</th>
                            <th>Color</th>
                            <th>Clarity</th>
                            <th>Carat</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Date Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    <!-- Diamond Modal -->
    <div class="modal fade" id="diamondModal" tabindex="-1" aria-labelledby="diamondModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="diamondForm">
                @csrf
                <input type="hidden" id="diamond_id" name="diamondid">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Diamond Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body row g-3">
                        @php
                            $diamondFields = [
                                [
                                    'diamond_type',
                                    'Diamond Type',
                                    'select',
                                    [1 => 'Natural Diamond', 2 => 'Lab Diamond'],
                                ],
                                ['quantity', 'Quantity', 'number'],
                                ['vendor_id', 'Vendor', 'select', $vendors],
                                ['shape', 'Shape', 'select', $shapes],
                                ['color', 'Color', 'select', $colors],
                                ['clarity', 'Clarity', 'select', $clarities],
                                ['cut', 'Cut', 'select', $cuts],
                                ['certificate_company', 'Certificate Company', 'select', $certificate_companies],
                                ['carat_weight', 'Carat Weight', 'number'],
                                ['price_per_carat', 'Price/Carat', 'number'],
                                ['vendor_price', 'Vendor Price', 'number'],
                                ['certificate_number', 'Certificate Number'],
                                ['certificate_date', 'Certificate Date', 'date'],
                                ['polish', 'Polish', 'select', $polishes],
                                ['symmetry', 'Symmetry', 'select', $symmetries],
                                ['fluorescence', 'Fluorescence', 'select', $fluorescences],
                                [
                                    'availability',
                                    'Availability',
                                    'select',
                                    [
                                        0 => 'Hold',
                                        1 => 'Available',
                                        2 => 'Memo',
                                    ],
                                ],
                                [
                                    'is_superdeal',
                                    'Super Deal',
                                    'select',
                                    [
                                        1 => 'Yes',
                                        0 => 'No',
                                    ],
                                ],
                                [
                                    'status',
                                    'Status',
                                    'select',
                                    [
                                        1 => 'Active',
                                        0 => 'Inactive',
                                    ],
                                ],
                                ['date_added', 'Date Added', 'datetime-local'],
                                ['date_updated', 'Date Updated', 'datetime-local'],
                            ];
                        @endphp

                        @foreach ($diamondFields as $field)
                            @php
                                $name = $field[0];
                                $label = $field[1];
                                $type = $field[2] ?? 'text';
                                $options = $field[3] ?? [];
                            @endphp

                            <div class="col-md-4 mb-3">
                                <label for="{{ $name }}" class="form-label">{{ $label }}</label>

                                @if ($type === 'select')
                                    <select class="form-select" id="{{ $name }}" name="{{ $name }}">
                                        <option value="">-- Select --</option>
                                        @if (!empty($options))
                                            @foreach ($options as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @else
                                    <input type="{{ $type }}" class="form-control" id="{{ $name }}"
                                        name="{{ $name }}">
                                @endif

                                @error($name)
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        @endforeach

                        <div id="diamondFormError" class="col-12 text-danger mt-2"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary me-2" id="saveDiamondBtn">Save</button>
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
                $.get("{{ route('diamond-master.data') }}", function(response) {
                    let rows = '';
                    response.forEach(r => {
                        rows += `
                        <tr>
                            <td>${r.diamondid}</td>
                            <td>${r.vendor?.vendor_name ?? ''}</td>
                            <td>${r.shape?.name ?? ''}</td>
                            <td>${r.color?.name ?? ''}</td>
                            <td>${r.clarity?.name ?? ''}</td>
                            <td>${r.carat_weight?? ''}</td>
                             <td>${r.price?? ''}</td>
                            <td>
                            <input type="checkbox" ${r.status == 1 ? 'checked' : ''} class="status" data-id="${r.diamondid}">
                </td>
                        <td>${r.date_added ? r.date_added.substring(0, 10) : ''}</td>
                            <td>${r.date_updated ? r.date_updated.substring(0, 10) : ''}</td>
                            <td>
                                <button class="btn btn-sm btn-info editBtn" data-id="${r.diamondid}"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.diamondid}"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    });
                    renderDataTable('diamondTable', rows);
                });
            }

            $('#addDiamondBtn').on('click', function() {
                $('#diamondForm')[0].reset();
                $('#diamond_id').val('');
                $('#formError').text('');
                $('#saveDiamondBtn').text('Save');
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                console.log(id);
                $.get("{{ url('admin/diamond-master') }}/" + id, function(data) {
                    $('#diamondForm')[0].reset();
                    $('#formError').text('');
                    $('#diamond_id').val(data.diamondid);
                    $('#diamond_type').val(data.diamond_type);
                    $('#quantity').val(data.quantity);
                    $('#vendor_id').val(data.vendor_id);
                    $('#shape').val(data.shape);
                    $('#color').val(data.color);
                    $('#clarity').val(data.clarity);
                    $('#cut').val(data.cut);
                    $('#certificate_company').val(data.certificate_company);
                    $('#carat_weight').val(data.carat_weight);
                    $('#price_per_carat').val(data.price_per_carat);
                    $('#vendor_price').val(data.vendor_price);
                    $('#certificate_number').val(data.certificate_number);
                    $('#certificate_date').val(data.certificate_date);
                    $('#polish').val(data.polish);
                    $('#symmetry').val(data.symmetry);
                    $('#fluorescence').val(data.fluorescence);
                    $('#availability').val(data.availability);
                    $('#is_superdeal').val(data.is_superdeal);
                    $('#status').val(data.status);
                    $('#date_added').val(formatDateForInput(data.date_added));
                    $('#date_updated').val(formatDateForInput(data.date_updated));
                    $('#diamondModal').modal('show');
                    $('#saveDiamondBtn').text('Update');
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                if (confirm("Are you sure you want to delete this record?")) {
                    $.ajax({
                        url: `/admin/diamond-master/${id}`,
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

            $('#diamondForm').submit(function(e) {
                e.preventDefault();
                const id = $('#diamond_id').val();

                const method = id ? 'PUT' : 'POST';
                const url = id ?
                    `{{ url('admin/diamond-master') }}/${id}` :
                    "{{ route('diamond-master.store') }}";
                let formData = $(this).serialize();
                formData += `&_method=${method}`;
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function() {
                        $('#diamondModal').modal('hide');
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
                    url: `{{ url('admin/diamond-master/update-status') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
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

        });
    </script>
@endsection
