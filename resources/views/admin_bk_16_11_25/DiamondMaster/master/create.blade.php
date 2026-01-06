@extends('admin.layouts.master')
@section('main_section')
<div class="container py-4">
    <h4>Add New Diamond</h4>
    <form id="createForm" action="{{ route('diamond-master.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @php
            $generalFields = [
                ['diamond_type', 'Type', 'select', [1 => 'Natural', 2 => 'Lab']],
                ['quantity', 'Quantity', 'number'],
                ['vendor_id', 'Vendor', 'select', $vendors],
                ['vendor_stock_number', 'Vendor Stock #', 'text'],
                ['stock_number', 'Stock #', 'text'],
                ['carat_weight', 'Carat Weight', 'number'],
                ['is_superdeal', 'Is Superdeal', 'select', [0 => 'No', 1 => 'Yes']],
                ['availability', 'Availability', 'select', [0 => 'Hold', 1 => 'Available', 2 => 'Memo']],
                ['status', 'Status', 'select', [1 => 'Active', 0 => 'Inactive']],
            ];

            $selectAttributeFields = [
                ['shape', 'Shape', $shapes],
                ['color', 'Color', $colors],
                ['clarity', 'Clarity', $clarities],
                ['cut', 'Cut', $cuts],
                ['culet', 'Culet', $culet],
                ['polish', 'Polish', $polish],
                ['symmetry', 'Symmetry', $symmetry],
                ['fluorescence', 'Fluorescence', $fluorescence],
                ['fancy_color_intensity', 'Fancy Color Intensity', $fancyColorIntensity],
                ['fancy_color_overtone', 'Fancy Color Overtones', $fancycolorOvertones],
            ];

            $priceFields = [
                ['price', 'Price', 'number'],
                ['msrp_price', 'MSRP Price', 'number'],
                ['price_per_carat', 'Price/Carat', 'number'],
                ['vendor_rap_disc', 'Vendor RAP Disc', 'number'],
                ['vendor_price', 'Vendor Price', 'number'],
            ];

            $measurementFields = [
                ['measurement_l', 'Measurement L', 'number'],
                ['measurement_h', 'Measurement H', 'number'],
                ['measurement_w', 'Measurement W', 'number'],
                ['depth', 'Depth', 'number'],
                ['table_diamond', 'Table Diamond', 'number'],
            ];

            $certFields = [
                ['certificate_company', 'Cert Company', 'select', $labs],
                ['certificate_number', 'Cert Number', 'text'],
                ['certificate_date', 'Cert Date', 'date'],
            ];

            $fileFields = [
                ['image_file', 'Image File', 'file'],
                ['cert_link', 'Cert URL', 'text'],
                ['video_file', 'Video File', 'file'],
            ];
        @endphp

        {{-- Section: General Information --}}
        <div class="card mb-4">
            <div class="card-header"><strong>General Information</strong></div>
            <div class="card-body row g-3">
                @foreach ($generalFields as $f)
                    @php list($name, $label, $type, $opts) = array_pad($f, 4, []) @endphp
                    <div class="col-md-4">
                        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
                        @if ($type === 'select')
                            <select name="{{ $name }}" id="{{ $name }}" class="form-select">
                                <option value="">-- Select --</option>
                                @foreach ($opts as $k => $v)
                                    <option value="{{ $k }}" {{ old($name) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" class="form-control" value="{{ old($name) }}">
                        @endif
                        <span class="text-danger" id="error_{{ $name }}"></span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section: Diamond Attributes --}}
        <div class="card mb-4">
            <div class="card-header"><strong>Diamond Attributes</strong></div>
            <div class="card-body row g-3">
                @foreach ($selectAttributeFields as $f)
                    @php list($name, $label, $opts) = $f @endphp
                    <div class="col-md-4">
                        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
                        <select name="{{ $name }}" id="{{ $name }}" class="form-select">
                            <option value="">-- Select --</option>
                            @foreach ($opts as $k => $v)
                                <option value="{{ $k }}" {{ old($name) == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger" id="error_{{ $name }}"></span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section: Pricing --}}
        <div class="card mb-4">
            <div class="card-header"><strong>Pricing Information</strong></div>
            <div class="card-body row g-3">
                @foreach ($priceFields as $f)
                    @php list($name, $label, $type) = $f @endphp
                    <div class="col-md-4">
                        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
                        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" class="form-control" value="{{ old($name) }}">
                        <span class="text-danger" id="error_{{ $name }}"></span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section: Measurements --}}
        <div class="card mb-4">
            <div class="card-header"><strong>Measurements</strong></div>
            <div class="card-body row g-3">
                @foreach ($measurementFields as $f)
                    @php list($name, $label, $type) = $f @endphp
                    <div class="col-md-4">
                        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
                        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" class="form-control" value="{{ old($name) }}">
                        <span class="text-danger" id="error_{{ $name }}"></span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section: Certificate --}}
        <div class="card mb-4">
            <div class="card-header"><strong>Certificate Information</strong></div>
            <div class="card-body row g-3">
                @foreach ($certFields as $f)
                    @php list($name, $label, $type, $opts) = array_pad($f, 4, []) @endphp
                    <div class="col-md-4">
                        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
                        @if ($type === 'select')
                            <select name="{{ $name }}" id="{{ $name }}" class="form-select">
                                <option value="">-- Select --</option>
                                @foreach ($opts as $k => $v)
                                    <option value="{{ $k }}" {{ old($name) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" class="form-control" value="{{ old($name) }}">
                        @endif
                        <span class="text-danger" id="error_{{ $name }}"></span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section: Files --}}

         <div class="card mb-4">
            <div class="card-header"><strong>Media & Files</strong></div>
            <div class="card-body row g-3">
                {{-- Image Upload with Preview --}}
                <div class="col-md-4">
                    <label for="image_file" class="form-label">Image File</label>
                    <input type="file" name="image_file" id="image_file" class="form-control" accept="image/*">
                    
                    {{-- Image Preview --}}
                    <div id="imagePreview" class="mt-2" style="display: none;">
                        <img id="previewImage" src="#" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                            <i class="fa fa-trash"></i> Remove Image
                        </button>
                    </div>
                    
                    <span class="text-danger" id="error_image_file"></span>
                </div>

                {{-- Video Upload with Preview --}}
                <div class="col-md-4">
                    <label for="video_file" class="form-label">Video File</label>
                    <input type="file" name="video_file" id="video_file" class="form-control" accept="video/*">
                    
                    {{-- Video Preview --}}
                    <div id="videoPreview" class="mt-2" style="display: none;">
                        <video id="previewVideo" controls class="img-thumbnail" style="max-height: 150px;">
                            Your browser does not support the video tag.
                        </video>
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeVideo()">
                            <i class="fa fa-trash"></i> Remove Video
                        </button>
                    </div>
                    
                    <span class="text-danger" id="error_video_file"></span>
                </div>

                <div class="col-md-4">
                    <label for="cert_link" class="form-label">Cert URL</label>
                    <input type="text" name="cert_link" id="cert_link" class="form-control">
                    <span class="text-danger" id="error_cert_link"></span>
                </div>
            </div>
        </div>
        {{-- <div class="card mb-4">
            <div class="card-header"><strong>Media & Files</strong></div>
            <div class="card-body row g-3">
                @foreach ($fileFields as $f)
                    @php list($name, $label, $type) = $f @endphp
                    <div class="col-md-4">
                        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
                        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" class="form-control">
                        <span class="text-danger" id="error_{{ $name }}"></span>
                    </div>
                @endforeach
            </div>
        </div> --}}

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('diamond-master.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

{{-- AJAX Script --}}
<script>

    // Image Preview Function
    document.getElementById('image_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    // Video Preview Function
    document.getElementById('video_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            document.getElementById('previewVideo').src = url;
            document.getElementById('videoPreview').style.display = 'block';
        }
    });

    // Remove Image Function
    function removeImage() {
        document.getElementById('image_file').value = '';
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('previewImage').src = '#';
    }

    // Remove Video Function
    function removeVideo() {
        document.getElementById('video_file').value = '';
        document.getElementById('videoPreview').style.display = 'none';
        document.getElementById('previewVideo').src = '';
    }
    $(function(){
        function clearErrors() {
            $('.text-danger').text('');
        }

        function showErrors(errors) {
            $.each(errors, function (field, msgs) {
                $('#error_' + field).text(msgs[0]);
            });
        }

        $('#createForm').submit(function(e){
            e.preventDefault();
            clearErrors();
            
            // Create FormData object to handle file uploads
            var formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    toastr.success(res.message);
                    window.location.href = '{{ route('diamond-master.index') }}';
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        showErrors(xhr.responseJSON.errors);
                    } else {
                        alert('Something went wrong.');
                    }
                }
            });
        });
    });
</script>
@endsection