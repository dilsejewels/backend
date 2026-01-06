@extends('admin.layouts.master')
@section('main_section')
    <div class="container py-4">
        <h4>Edit Diamond</h4>
        <form id="editForm" action="{{ route('diamond-master.update', $diamond->diamondid) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

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

             <!-- Section: Current Media -->
            @if($diamond->image_link || $diamond->video_link)
            <div class="card mb-4">
                <div class="card-header"><strong>Current Media</strong></div>
                <div class="card-body row g-3">
                    @if($diamond->image_link)
                    <div class="col-md-6">
                        <label class="form-label">Current Image</label>
                        <div class="border p-2 rounded">
                            <img src="{{ url($diamond->image_url) }}"
                                 alt="Diamond Image" 
                                 class="img-thumbnail" 
                                 style="max-height: 200px; display: block; margin: 0 auto;">
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                                    <label class="form-check-label text-danger" for="remove_image">
                                        <i class="fa fa-trash"></i> Remove Image
                                    </label>
                                </div>
                                <small class="text-muted">{{ $diamond->image_link }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($diamond->video_link)
                    <div class="col-md-6">
                        <label class="form-label">Current Video</label>
                        <div class="border p-2 rounded">
                            <video controls class="img-thumbnail" style="max-height: 200px; display: block; margin: 0 auto;">
                                <source src="{{ url($diamond->video_url) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_video" id="remove_video" value="1">
                                    <label class="form-check-label text-danger" for="remove_video">
                                        <i class="fa fa-trash"></i> Remove Video
                                    </label>
                                </div>
                                <small class="text-muted">{{ $diamond->video_link }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

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
                                        <option value="{{ $k }}" {{ $diamond->$name == $k ? 'selected' : '' }}>
                                            {{ $v }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
                                    class="form-control" value="{{ old($name, $diamond->$name) }}">
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
                                    <option value="{{ $k }}" {{ $diamond->$name == $k ? 'selected' : '' }}>
                                        {{ $v }}
                                    </option>
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
                            <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
                                class="form-control" value="{{ old($name, $diamond->$name) }}">
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
                            <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
                                class="form-control" value="{{ old($name, $diamond->$name) }}">
                            <span class="text-danger" id="error_{{ $name }}"></span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Section: Certificate Info --}}
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
                                        <option value="{{ $k }}" {{ $diamond->$name == $k ? 'selected' : '' }}>
                                            {{ $v }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
                                    class="form-control" value="{{ old($name, $diamond->$name) }}">
                            @endif
                            <span class="text-danger" id="error_{{ $name }}"></span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Section: New Media Upload with Preview --}}
            <div class="card mb-4">
                <div class="card-header"><strong>Upload New Media</strong></div>
                <div class="card-body row g-3">
                    {{-- New Image Upload --}}
                    <div class="col-md-6">
                        <label for="image_file" class="form-label">New Image File</label>
                        <input type="file" name="image_file" id="image_file" class="form-control" accept="image/*">
                        
                        {{-- New Image Preview --}}
                        <div id="newImagePreview" class="mt-2" style="display: none;">
                            <img id="newPreviewImage" src="#" alt="New Preview" class="img-thumbnail" style="max-height: 150px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeNewImage()">
                                <i class="fa fa-trash"></i> Remove New Image
                            </button>
                        </div>
                    </div>

                    {{-- New Video Upload --}}
                    <div class="col-md-6">
                        <label for="video_file" class="form-label">New Video File</label>
                        <input type="file" name="video_file" id="video_file" class="form-control" accept="video/*">
                        
                        {{-- New Video Preview --}}
                        <div id="newVideoPreview" class="mt-2" style="display: none;">
                            <video id="newPreviewVideo" controls class="img-thumbnail" style="max-height: 150px;">
                                Your browser does not support the video tag.
                            </video>
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeNewVideo()">
                                <i class="fa fa-trash"></i> Remove New Video
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('diamond-master.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    {{-- AJAX Submission Script --}}
    <script>

         // New Image Preview for Edit Form
        document.getElementById('image_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('newPreviewImage').src = e.target.result;
                    document.getElementById('newImagePreview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // New Video Preview for Edit Form
        document.getElementById('video_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const url = URL.createObjectURL(file);
                document.getElementById('newPreviewVideo').src = url;
                document.getElementById('newVideoPreview').style.display = 'block';
            }
        });

        // Remove New Image Function
        function removeNewImage() {
            document.getElementById('image_file').value = '';
            document.getElementById('newImagePreview').style.display = 'none';
            document.getElementById('newPreviewImage').src = '#';
        }

        // Remove New Video Function
        function removeNewVideo() {
            document.getElementById('video_file').value = '';
            document.getElementById('newVideoPreview').style.display = 'none';
            document.getElementById('newPreviewVideo').src = '';
        }
        $(function() {
            function clearErrors() {
                $('.text-danger').text('');
            }

            function showErrors(errors) {
                $.each(errors, function(field, msgs) {
                    $('#error_' + field).text(msgs[0]);
                });
            }

            $('#editForm').submit(function(e) {
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
                        window.location.href = "{{ route('diamond-master.index') }}";
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