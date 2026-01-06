@extends('admin.layouts.master')
@section('main_section')
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            box-sizing: border-box;
            list-style: none;
            margin: 0;
            padding: 0 5px;
            width: 505px !important;
        }
        
        .container-xxl {
            background-color: #f1f1f1;
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            background: #fff;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #555;
        }

        .custom-pr {
            padding-right: 30px;
        }
        
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #333;
        }
        
        .form-control, .form-select {
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 0.9rem;
            border: 1px solid #ddd;
            height: auto;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
            border-color: #007bff;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            color: #6c757d;
            border-radius: 0;
        }
        
        .nav-tabs .nav-link.active {
            color: #007bff;
            background: transparent;
            border-bottom: 3px solid #007bff;
        }
        
        .sticky-variations {
            position: sticky;
            top: 20px;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .submit-section {
            background: #fff;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #eaeef2;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .image-preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-preview-item .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.7);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .error-message {
            color: red;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .is-invalid {
            border-color: red !important;
        }
        
        .woocommerce-layout {
            display: contents;
            grid-template-columns: 1fr 300px;
            gap: 20px;
        }
        
        .woocommerce-main {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
        }
        
        .woocommerce-sidebar {
            background: #fff;
        }
        
        .woocommerce-section {
            margin-bottom: 30px;
        }
        
        .woocommerce-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        /* Variations Grid Styles */
        .variations-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .variation-row {
            background: #f8f9fa;
            border: 1px solid #e9ecef !important;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .variation-row:hover {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .variation-row .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #495057;
        }

        /* Remove button styles */
        .remove-variation-row {
            margin-top: 24px;
        }

        /* Image preview styles for variations */
        .variation-image-preview {
            position: relative;
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }

        .variation-image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-variation-image {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .video-preview {
            margin-top: 10px;
        }

        .video-preview video {
            max-width: 200px;
            border-radius: 4px;
        }
        
        @media (max-width: 992px) {
            .woocommerce-layout {
                grid-template-columns: 1fr;
            }
            
            .sticky-variations {
                position: static;
            }
        }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-cube me-2"></i>Edit Product</h4>
            </div>
            
            <div class="card-body">
                <form id="productForm" action="{{ route('product.update', $product->products_id) }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <div class="woocommerce-layout">
                        <!-- Main Content Area -->
                        <div class="woocommerce-main">
                            <ul class="nav nav-tabs" id="productTabs">
                                <li class="nav-item">
                                    <button type="button" class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general">General</button>
                                </li>

                                <li class="nav-item">
                                    <button type="button" class="nav-link" id="variations-tab" data-bs-toggle="tab" data-bs-target="#variations">Variations</button>
                                </li>

                                <li class="nav-item">
                                    <button type="button" class="nav-link" id="information-tab" data-bs-toggle="tab" data-bs-target="#information">Product Information</button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping">Shipping</button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced">Advanced</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content">
                                <!-- General Tab -->
                                <div class="tab-pane fade show active" id="general">
                                    <div class="woocommerce-section">
                                        <div class="woocommerce-section-title">Product Details</div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Product Name *</label>
                                                <input type="text" id="products_name" name="products_name" class="form-control"
                                                    value="{{ old('products_name', $product->products_name ?? '') }}" required>
                                                <div class="error-message" id="error-products_name"></div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Slug *</label>
                                                <input type="text" id="products_slug" name="products_slug" class="form-control"
                                                    value="{{ old('products_slug', $product->products_slug ?? '') }}" readonly>
                                                <div class="error-message" id="error-products_slug"></div>
                                            </div>

                                            <!-- Build Product Section Update -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Product Type *</label>
                                                <div>
                                                    <select name="is_build_product" class="form-select" required id="is_build_product">
                                                        <option value="" selected disabled>Select Build Product Type</option>
                                                        @foreach($buildProductOptions as $value => $label)
                                                            <option value="{{ $value }}" {{ old('is_build_product', $product->is_build_product ?? '') == $value ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="error-message" id="error-is_build_product"></div>
                                                </div>
                                            </div>

                                            <!-- Edit view mein same fields add karen -->
                                            <div class="col-md-6 mb-3 wedding-field" style="display: none;">
                                                <label class="form-label">Gender *</label>
                                                <select name="gender" class="form-select">
                                                    <option value="0" {{ old('gender', $product->gender ?? '0') == '0' ? 'selected' : '' }}>Man</option>
                                                    <option value="1" {{ old('gender', $product->gender ?? '0') == '1' ? 'selected' : '' }}>Woman</option>
                                                </select>
                                                <div class="error-message" id="error-gender"></div>
                                            </div>

                                            <div class="col-md-6 mb-3 wedding-field" style="display: none;">
                                                <label class="form-label">Bond *</label>
                                                <select name="bond" class="form-select">
                                                    <option value="0" {{ old('bond', $product->bond ?? '0') == '0' ? 'selected' : '' }}>Metal</option>
                                                    <option value="1" {{ old('bond', $product->bond ?? '0') == '1' ? 'selected' : '' }}>Diamond</option>
                                                </select>
                                                <div class="error-message" id="error-bond"></div>
                                            </div>

                                            <!-- Product Category -->
                                            <div class="col-md-6 mb-3 non-build-field">
                                                <label class="form-label">Product Category *</label>
                                                <select id="categories_id" name="categories_id" class="form-select">
                                                    <option value="">Select Category</option>
                                                    @foreach($categories as $parent)
                                                        <optgroup label="{{ $parent->category_name }}" data-parent-id="{{ $parent->category_id }}">
                                                            <option value="parent_{{ $parent->category_id }}"
                                                                {{ old('categories_id', $selectedCategoryValue ?? '') == 'parent_' . $parent->category_id ? 'selected' : '' }}>
                                                                {{ $parent->category_name }} (Parent)
                                                            </option>
                                                            @foreach($parent->children as $child)
                                                                <option value="{{ $child->category_id }}"
                                                                    {{ old('categories_id', $selectedCategoryValue ?? '') == $child->category_id ? 'selected' : '' }}>
                                                                    {{ $child->category_name }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-categories_id"></div>
                                            </div>

                                            <!-- Style Category -->
                                            <div class="col-md-6 mb-3 build-field" id="styleCategoryWrapper" style="{{ old('is_build_product', $product->is_build_product) == 1 ? '' : 'display:none;' }}">
                                                <label class="form-label">Style Category *</label>
                                                <select id="psc_id" name="psc_id" class="form-select">
                                                    <option value="">Select Style Category</option>
                                                    @foreach($styleCategories as $key => $name)
                                                        <option value="{{ $key }}" {{ $key == $initialPscId ? 'selected' : '' }}>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Product Collection -->
                                            <div class="col-md-6 mb-3 non-build-field">
                                                <label class="form-label">Product Collection</label>
                                                <select name="product_collection_id" id="collection_select" class="form-select">
                                                    <option value="">Select Collection</option>
                                                    @foreach($collections as $id => $name)
                                                        <option value="{{ $id }}" {{ old('product_collection_id', $product->product_collection_id ?? '') == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Style Group -->
                                            <div class="col-md-6 mb-3 non-build-field">
                                                <label class="form-label">Style Group</label>
                                                <select name="product_style_group_id" id="product_style_group_id" class="form-select">
                                                    <option value="">Select Style Group</option>
                                                    @foreach($styleGroups as $id => $name)
                                                        <option value="{{ $id }}" {{ old('product_style_group_id', $product->product_style_group_id ?? '') == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>


                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Vendor *</label>
                                                <select name="vendor_id" class="form-select" required>
                                                    <option value="">Select Vendor</option>
                                                    @foreach($vendors as $vendor)
                                                        <option value="{{ $vendor->vendorid }}" {{ old('vendor_id', $product->vendor_id) == $vendor->vendorid ? 'selected' : '' }}>
                                                            {{ $vendor->vendor_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-vendor_id"></div>
                                            </div>

                                            {{-- <div class="col-md-6 mb-3">
                                                <label class="form-label">Quality</label>
                                                <select name="diamond_quality_id" class="form-select">
                                                    <option value="">Select Diamond Quality</option>
                                                    @foreach($diamond_qualities as $id => $name)
                                                        <option value="{{ $id }}" {{ old('diamond_quality_id', $product->diamond_quality_id) == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-diamond_quality_id"></div>
                                            </div> --}}
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Clarity</label>
                                                <select name="diamond_clarity_id" class="form-select">
                                                    <option value="">Select Clarity</option>
                                                    @foreach($diamond_clarities as $id => $name)
                                                        <option value="{{ $id }}" {{ old('diamond_clarity_id', $product->diamond_clarity_id) == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-diamond_clarity_id"></div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Color</label>
                                                <select name="diamond_color_id" class="form-select">
                                                    <option value="">Select Color</option>
                                                    @foreach($diamond_colors as $id => $name)
                                                        <option value="{{ $id }}" {{ old('diamond_color_id', $product->diamond_color_id) == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-diamond_color_id"></div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Cut</label>
                                                <select name="diamond_cut_id" class="form-select">
                                                    <option value="">Select Cut</option>
                                                    @foreach($diamond_cuts as $id => $name)
                                                        <option value="{{ $id }}" {{ old('diamond_cut_id', $product->diamond_cut_id) == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-diamond_cut_id"></div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Diamond Weight Group</label>
                                                <select name="diamond_weight_group_id" class="form-select">
                                                    <option value="">Select Group</option>
                                                    @foreach($weightGroups as $id => $name)
                                                        <option value="{{ $id }}" {{ old('diamond_weight_group_id', $product->diamond_weight_group_id) == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-diamond_weight_group_id"></div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Country of Origin</label>
                                                <select name="country_of_origin" class="form-select">
                                                    <option value="">Select Country</option>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->country_id }}" {{ old('country_of_origin', $product->country_of_origin) == $country->country_id ? 'selected' : '' }}>
                                                            {{ $country->country_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-country_of_origin"></div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Shipping Zone</label>
                                                <select name="shop_zone_id" class="form-select">
                                                    <option value="">Select Zone</option>
                                                    @foreach($shopZones as $id => $name)
                                                        <option value="{{ $id }}" {{ old('shop_zone_id', $product->shop_zone_id ?? '') == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-shop_zone_id"></div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Stone Type</label>
                                                <select name="stone_type_id" class="form-select">
                                                    <option value="">Select Stone Type</option>
                                                    @foreach($stone_types as $id => $name)
                                                        <option value="{{ $id }}" {{ old('stone_type_id', $product->stone_type_id) == $id ? 'selected' : '' }}>
                                                            {{ $name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="error-message" id="error-stone_type_id"></div>
                                            </div>
                                            

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Status</label>
                                                <div>
                                                    <select name="products_status" class="form-select" required>
                                                        <option value="1" {{ old('products_status', $product->products_status) == 1 ? 'selected' : '' }}>Active</option>
                                                        <option value="0" {{ old('products_status', $product->products_status) == 0 ? 'selected' : '' }}>Inactive</option>
                                                    </select>
                                                    <div class="error-message" id="error-products_status"></div>
                                                </div>
                                            </div>
                                            <!-- Additional Flags Section -->
                                            <div class="col-md-6 mb-3 pt-2">
                                                <label class="form-label">ADDITIONAL FLAGS</label><br>
                                                <input class="form-check-input" type="checkbox" name="is_sale" value="1" id="is_sale" {{ old('is_sale', $product->is_sale ?? '') ? 'checked' : '' }}>
                                                <label class="form-check-label custom-pr" for="is_sale">💎 On Sale</label>
                                                <input class="form-check-input" type="checkbox" name="is_gift" value="1" id="is_gift" {{ old('is_gift', $product->is_gift ?? '') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_gift">🎁 Gift Product</label>
                                            </div>
                                        </div>             
                                    </div>
                                </div>
 
                                <!-- Variations Tab -->
                                <div class="tab-pane fade" id="variations">
                                    <div class="woocommerce-section">
                                        <div class="woocommerce-section-title">
                                            <i class="fas fa-layer-group me-2"></i>Product Variations
                                        </div>
                                        
                                        <div class="variations-container" id="variationsContainer">
                                            @foreach($product->variations as $i => $variation)
                                            <div class="variation-row row g-3 mb-4 p-3 border rounded" data-index="{{ $i }}">
                                                <!-- Hidden variation ID field -->
                                                <input type="hidden" name="variations[{{ $i }}][id]" value="{{ $variation->id }}">

                                                <div class="col-md-3">
                                                    <label class="form-label">Metal Type</label>
                                                    <select name="variations[{{ $i }}][metal_color_id]" class="form-select">
                                                        <option value="">Select Metal Type</option>
                                                        @foreach($metal_types_colors as $id => $name)
                                                            <option value="{{ $id }}"
                                                                {{ old("variations.$i.metal_color_id", $variation->metal_color_id) == $id ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="error-message" id="error-variations-{{ $i }}-metal_color_id"></div>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label class="form-label">Shape</label>
                                                    <select name="variations[{{ $i }}][shape_id]" class="form-select">
                                                        <option value="">Select Shape</option>
                                                        @foreach($shapes as $shape)
                                                            <option value="{{ $shape->id }}"
                                                                {{ old("variations.$i.shape_id", $variation->shape_id) == $shape->id ? 'selected' : '' }}>
                                                                {{ $shape->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="error-message" id="error-variations-{{ $i }}-shape_id"></div>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label">Diamond Weight (ct)</label>
                                                    <input type="number" step="0.01" name="variations[{{ $i }}][diamond_weight]" 
                                                        class="form-control" placeholder="0.00" 
                                                        value="{{ old("variations.$i.diamond_weight", $variation->diamond_weight) }}">
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label class="form-label">Metal Weight </label>
                                                    <input type="number" step="0.01" 
                                                        name="variations[{{ $i }}][weight]" 
                                                        class="form-control" 
                                                        placeholder="0.00"
                                                        value="{{ old("variations.$i.weight", $variation->weight) }}"
                                                        required>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label class="form-label">Price *</label>
                                                    <input type="number" step="0.01" 
                                                        name="variations[{{ $i }}][price]" 
                                                        class="form-control" 
                                                        value="{{ old("variations.$i.price", $variation->price) }}"
                                                        required>
                                                    <div class="error-message" id="error-variations-{{ $i }}-price"></div>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label class="form-label">Regular Price</label>
                                                    <input type="number" step="0.01" name="variations[{{ $i }}][regular_price]" 
                                                        value="{{ old("variations.$i.regular_price", $variation->regular_price) }}"
                                                        class="form-control" required placeholder="0.00">
                                                    <div class="error-message" id="error-variations-{{ $i }}-regular_price"></div>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label class="form-label">Stock</label>
                                                    <input type="number" 
                                                        name="variations[{{ $i }}][stock]" 
                                                        class="form-control" 
                                                        value="{{ old("variations.$i.stock", $variation->stock) }}"
                                                        placeholder="0">
                                                    <div class="error-message" id="error-variations-{{ $i }}-stock"></div>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label class="form-label">Best Selling</label>
                                                    <div class="form-check mt-2">
                                                        <input type="checkbox" name="variations[{{ $i }}][is_best_selling]" value="1" 
                                                            {{ $variation->is_best_selling ? 'checked' : '' }} class="form-check-input">
                                                        <label class="form-check-label">Mark as Best Selling</label>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label class="form-label">Actions</label>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-danger remove-variation-row">
                                                            <i class="fas fa-trash me-1"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <!-- Images Section - Full Width -->
                                                <div class="col-12 mt-3">
                                                    <label class="form-label">Variation Images</label>
                                                    <div class="d-flex flex-wrap gap-2 mb-2" id="variation-images-{{ $i }}">
                                                        @if($variation->images)
                                                            @foreach ($variation->images as $filename)
                                                                @if(!empty($filename) && $filename !== '[]' && $filename !== '[""]')
                                                                    <div class="position-relative d-inline-block" style="width: 60px; height: 60px; margin: 2px;">
                                                                        @php $cleanFilename = ltrim(basename($filename), '/'); @endphp

                                                                        <img src="{{ url('storage/variation_images/' . $cleanFilename) }}" 
                                                                            class="img-thumbnail w-100 h-100" 
                                                                            style="object-fit: cover;">
                                                                                
                                                                        <button type="button" 
                                                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 remove-existing-image"
                                                                            data-variation-index="{{ $i }}"
                                                                            data-image-path="{{ $filename }}"
                                                                            style="width: 20px; height: 20px; font-size: 12px; line-height: 1;">
                                                                            &times;
                                                                        </button>
                                                                                
                                                                        <input type="hidden" 
                                                                        name="variations[{{ $i }}][existing_images][]" 
                                                                        value="{{ $filename }}">
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    
                                                    <input type="hidden" 
                                                                name="variations[{{ $i }}][removed_images]" 
                                                                id="removed-images-{{ $i }}" 
                                                                value="">
                                                        
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-variation-image" data-index="{{ $i }}">
                                                        <i class="fas fa-plus me-1"></i> Add Images
                                                    </button>
                                                    <input type="file" name="variations[{{ $i }}][images][]" multiple class="d-none variation-image-input" data-index="{{ $i }}">
                                                    <div class="error-message" id="error-variations-{{ $i }}-images"></div>
                                                </div>
                                                
                                                <!-- Video Section - Full Width -->
                                                <div class="col-12 mt-3">
                                                    <label class="form-label">Variation Video</label>
                                                    
                                                    <!-- Existing Video Display -->
                                                    @if($variation->video)
                                                        <div class="existing-video mb-2">
                                                            <video width="200" height="120" controls class="mb-2">
                                                                <source src="{{ url('storage/variation_videos/'.$variation->video) }}" type="video/mp4">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                            <br>
                                                            <button type="button" class="btn btn-sm btn-danger remove-existing-video mt-1" 
                                                                    data-variation-index="{{ $i }}">
                                                                <i class="fas fa-trash me-1"></i> Remove Video
                                                            </button>
                                                            <input type="hidden" name="variations[{{ $i }}][existing_video]" 
                                                                value="{{ $variation->video }}" id="existing-video-{{ $i }}">
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- New Video Upload -->
                                                    <input type="file" name="variations[{{ $i }}][video]" 
                                                        class="form-control variation-video-input" 
                                                        accept="video/*"
                                                        id="video-input-{{ $i }}">
                                                    
                                                    <!-- Remove Video Flag -->
                                                    <input type="hidden" name="variations[{{ $i }}][remove_video]" 
                                                        value="0" id="remove-video-flag-{{ $i }}">
                                                    
                                                    <!-- New Video Preview -->
                                                    <div class="video-preview mt-2" id="video-preview-{{ $i }}" style="display: none;">
                                                        <video width="200" height="120" controls class="mb-2">
                                                            <source src="" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-danger remove-new-video-btn" 
                                                                data-index="{{ $i }}">
                                                            <i class="fas fa-trash me-1"></i> Remove New Video
                                                        </button>
                                                    </div>
                                                    
                                                    <small class="text-muted">Maximum file size: 50 MB. Supported formats: MP4, AVI, MOV.</small>
                                                    <div class="error-message" id="error-variations-{{ $i }}-video"></div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                            
                                        <div class="text-end mt-3">
                                            <button type="button" class="btn btn-primary" id="addVariationRow">
                                                <i class="fas fa-plus me-2"></i>Add New Variation
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Information Tab -->
                                <div class="tab-pane fade" id="information">
                                    <div class="woocommerce-section">
                                        <div class="woocommerce-section-title">Product Information</div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Product Model</label>
                                                    <input type="text" name="products_model" class="form-control" value="{{ old('products_model', $product->products_model) }}">
                                                    <div class="error-message" id="error-products_model"></div>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Build Type</label>
                                                    <select name="build_product_type" id="build_product_type" class="form-select">
                                                        <option value="yes" {{ old('build_product_type', $product->build_product_type) == 'yes' ? 'selected' : '' }}>Yes</option>
                                                        <option value="no" {{ old('build_product_type', $product->build_product_type) == 'no' ? 'selected' : '' }}>No</option>
                                                    </select>
                                                    <div class="error-message" id="error-build_product_type"></div>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Related Products</label>
                                                    <input type="text" name="products_related_items" class="form-control" value="{{ old('products_related_items', $product->products_related_items) }}">
                                                    <div class="error-message" id="error-products_related_items"></div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Master SKU Reference</label>
                                                    <input type="text" name="related_master_sku" class="form-control" value="{{ old('related_master_sku', $product->related_master_sku) }}">
                                                    <div class="error-message" id="error-related_master_sku"></div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Certified Lab</label>
                                                    <select name="certified_lab" class="form-select">
                                                        <option value="">Select Certified Lab</option>
                                                        @foreach($diamondLabs as $diamondLab)
                                                            <option value="{{ $diamondLab->dl_id }}" {{ old('certified_lab', $product->certified_lab) == $diamondLab->dl_id ? 'selected' : '' }}>
                                                                {{ $diamondLab->dl_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="error-message" id="error-certified_lab"></div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Certificate Number</label>
                                                    <input type="text" name="certificate_number" class="form-control" value="{{ old('certificate_number', $product->certificate_number) }}">
                                                    <div class="error-message" id="error-certificate_number"></div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Default Size</label>
                                                    <input type="text" name="default_size" class="form-control" value="{{ old('default_size', $product->default_size) }}">
                                                    <div class="error-message" id="error-default_size"></div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Display Order</label>
                                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $product->sort_order) }}">
                                                    <div class="error-message" id="error-sort_order"></div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Catalog Number</label>
                                                    <input type="text" name="catelog_no" class="form-control" value="{{ old('catelog_no', $product->catelog_no) }}" required>
                                                    <div class="error-message" id="error-catelog_no"></div>
                                                </div>

                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Short Description</label>
                                                    <textarea name="products_short_description" class="form-control" rows="3" placeholder="Brief product description">{{ old('products_short_description', $product->products_short_description) }}</textarea>
                                                    <div class="error-message" id="error-products_short_description"></div>
                                                </div>
                                                
                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Full Description</label>
                                                    <textarea name="products_description" class="form-control" rows="5" placeholder="Detailed product description">{{ old('products_description', $product->products_description) }}</textarea>
                                                    <div class="error-message" id="error-products_description"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    <div class="woocommerce-section">
                                        <div class="woocommerce-section-title">Inventory Management</div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Stock Quantity</label>
                                                <input type="number" name="products_quantity" class="form-control" value="{{ old('products_quantity', $product->products_quantity) }}">
                                                <div class="error-message" id="error-products_quantity"></div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Stock Status</label>
                                                <select name="available" class="form-select">
                                                    <option value="yes" {{ old('available', $product->available) == 'yes' ? 'selected' : '' }}>In Stock</option>
                                                    <option value="no" {{ old('available', $product->available) == 'no' ? 'selected' : '' }}>Out of Stock</option>
                                                </select>
                                                <div class="error-message" id="error-available"></div>
                                            </div>
                                            {{-- <div class="col-md-4 mb-3">
                                                <label class="form-label">Product Weight (g) *</label>
                                                <input type="number" step="0.01" name="products_weight" class="form-control" value="{{ old('products_weight', $product->products_weight) }}" required>
                                                <div class="error-message" id="error-products_weight"></div>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Shipping Tab -->
                                <div class="tab-pane fade" id="shipping">
                                    <div class="woocommerce-section">
                                        <div class="woocommerce-section-title">Shipping Details</div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Delivery Days</label>
                                                <input type="text" name="delivery_days" class="form-control" value="{{ old('delivery_days', $product->delivery_days) }}">
                                                <div class="error-message" id="error-delivery_days"></div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Ready to Ship</label>
                                                <select name="ready_to_ship" class="form-select">
                                                    <option value="">Select Option</option>
                                                    <option value="1" {{ old('ready_to_ship', $product->ready_to_ship) == '1' ? 'selected' : '' }}>Yes</option>
                                                    <option value="0" {{ old('ready_to_ship', $product->ready_to_ship) == '0' ? 'selected' : '' }}>No</option>
                                                </select>
                                                <div class="error-message" id="error-ready_to_ship"></div>
                                            </div>                                      
                                        </div>
                                    </div>
                                </div>

                                <!-- Advanced Tab -->
                                <div class="tab-pane fade" id="advanced">
                                    <div class="woocommerce-section">
                                        <div class="woocommerce-section-title">Advanced Settings</div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Product Status</label>
                                                <select name="products_status" class="form-select">
                                                    <option value="1" {{ old('products_status', $product->products_status) == 1 ? 'selected' : '' }}>Published</option>
                                                    <option value="0" {{ old('products_status', $product->products_status) == 0 ? 'selected' : '' }}>Draft</option>
                                                </select>
                                                <div class="error-message" id="error-products_status"></div>
                                            </div>
                                            
                        
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Is Featured?</label>
                                                <select name="is_featured" class="form-select">
                                                    <option value="1" {{ old('is_featured', $product->is_featured) == 1 ? 'selected' : '' }}>Yes</option>
                                                    <option value="0" {{ old('is_featured', $product->is_featured) == 0 ? 'selected' : '' }}>No</option>
                                                </select>
                                                <div class="error-message" id="error-is_featured"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="woocommerce-section">
                                        <div class="woocommerce-section-title">SEO Settings</div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">SEO Title</label>
                                                <input type="text" name="products_meta_title" class="form-control" value="{{ old('products_meta_title', $product->products_meta_title) }}">
                                                <div class="error-message" id="error-products_meta_title"></div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">SEO Keywords</label>
                                                <input type="text" name="products_meta_keyword" class="form-control" value="{{ old('products_meta_keyword', $product->products_meta_keyword) }}">
                                                <div class="error-message" id="error-products_meta_keyword"></div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Product Keywords</label>
                                                <input type="text" name="product_keywords" class="form-control" value="{{ old('product_keywords', $product->product_keywords) }}">
                                                <div class="error-message" id="error-product_keywords"></div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Product Promotion</label>
                                                <input type="text" name="product_promotion" class="form-control" value="{{ old('product_promotion', $product->product_promotion) }}">
                                                <div class="error-message" id="error-product_promotion"></div>
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">SEO Description</label>
                                                <textarea name="products_meta_description" class="form-control" rows="3">{{ old('products_meta_description', $product->products_meta_description) }}</textarea>
                                                <div class="error-message" id="error-products_meta_description"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Form submit button -->
                    <div class="submit-section text-end mt-4">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('product.index') }}'">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generateSlug(str) {
            return str.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')  
                    .trim()                
                    .replace(/\s+/g, '-') 
                    .replace(/-+/g, '-');       
        }

        document.getElementById('products_name').addEventListener('input', function () {
            const name = this.value;
            const slug = generateSlug(name);
            document.getElementById('products_slug').value = slug; 
        });

        let variationCount = {{ count($product->variations) }};
        let isSubmitting = false;
        let removedImageIndexes = {};             
        let removedExistingImages = {};

        // Add new variation row
        $('#addVariationRow').click(function (e) {
            e.preventDefault();
            const newRow = `
                <div class="variation-row row g-3 mb-4 p-3 border rounded" data-index="${variationCount}">
                    <input type="hidden" name="variations[${variationCount}][id]" value="new">

                    <div class="col-md-3">
                        <label class="form-label">Metal Type</label>
                        <select name="variations[${variationCount}][metal_color_id]" class="form-select">
                            {!! collect($metal_types_colors)->map(function($name, $id) {
                                return "<option value='{$id}'>{$name}</option>";
                            })->implode('') !!}
                        </select>
                    </div>

                    <div class="col-md-3">
                         <label class="form-label">Shape</label>
                         <select name="variations[${variationCount}][shape_id]" class="form-select">
                            <option value="">Select Shape</option>
                            @foreach($shapes as $shape)
                                <option value="{{ $shape->id }}">{{ $shape->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Diamond Weight (ct)</label>
                        <input type="number" step="0.01" name="variations[${variationCount}][diamond_weight]" 
                            class="form-control" placeholder="0.00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Metal Weight </label>
                        <input type="number" step="0.01" name="variations[${variationCount}][weight]" class="form-control" placeholder="0.00" >
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price *</label>
                        <input type="number" step="0.01" name="variations[${variationCount}][price]" class="form-control" required>
                        <div class="error-message" id="error-variations-${variationCount}-price"></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Regular Price</label>
                        <input type="number" step="0.01" name="variations[${variationCount}][regular_price]" class="form-control" required placeholder="0.00">
                        <div class="error-message" id="error-variations-${variationCount}-regular_price"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="variations[${variationCount}][stock]" class="form-control" value="0" placeholder="0">
                        <div class="error-message" id="error-variations-${variationCount}-stock"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Best Selling</label>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="variations[${variationCount}][is_best_selling]" value="1" class="form-check-input">
                            <label class="form-check-label">Mark as Best Selling</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Actions</label>
                        <div>
                            <button type="button" class="btn btn-sm btn-danger remove-variation-row">
                                <i class="fas fa-trash me-1"></i> Remove
                            </button>
                        </div>
                    </div>
                    
                    <!-- Images Section -->
                    <div class="col-12 mt-3">
                        <label class="form-label">Variation Images</label>
                        <div class="d-flex flex-wrap gap-2 mb-2" id="variation-images-${variationCount}"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary add-variation-image" data-index="${variationCount}">
                            <i class="fas fa-plus me-1"></i> Add Images
                        </button>
                        <input type="file" name="variations[${variationCount}][images][]" multiple class="d-none variation-image-input" data-index="${variationCount}">
                        <div class="error-message" id="error-variations-${variationCount}-images"></div>
                    </div>
                    
                    <!-- Video Section -->
                    <div class="col-12 mt-3">
                        <label class="form-label">Variation Video</label>
                        <input type="file" name="variations[${variationCount}][video]" 
                            class="form-control variation-video-input" 
                            accept="video/*">
                        
                        <div class="video-preview mt-2" style="display: none;">
                            <video width="200" height="120" controls class="mb-2">
                                <source src="" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <br>
                            <button type="button" class="btn btn-sm btn-danger remove-video-btn">
                                <i class="fas fa-trash me-1"></i> Remove Video
                            </button>
                        </div>
                        
                        <input type="hidden" name="variations[${variationCount}][remove_video]" value="0">
                        <small class="text-muted">Max file size: 50MB. Supported formats: MP4, AVI, MOV.</small>
                    </div>
                </div>
            `;
            $('#variationsContainer').append(newRow);
            variationCount++;
        });

        // Remove variation row
        $(document).on('click', '.remove-variation-row', function () {
            $(this).closest('.variation-row').remove();            
        });

        // Remove existing image
        $(document).on('click', '.remove-existing-image', function(e) {
            e.preventDefault();
                
            const variationIndex = $(this).data('variation-index');
            const imagePath = $(this).data('image-path');
            $(this).closest('.position-relative').remove();

            const removedInput = $(`#removed-images-${variationIndex}`);
            let removedPaths = removedInput.val() ? removedInput.val().split(',') : [];
            if (!removedPaths.includes(imagePath)) {
                removedPaths.push(imagePath);
            }
                
            removedInput.val(removedPaths.join(','));
                
            $(`input[name="variations[${variationIndex}][existing_images][]"][value="${imagePath}"]`).remove();
        });

        // Handle removal of newly uploaded images
        $(document).on('click', '.remove-uploaded-image', function (e) {
            e.preventDefault();
            const index = $(this).data('index');
            const imgIndex = $(this).data('img-index');
                
            if (!removedImageIndexes[index]) {
                removedImageIndexes[index] = [];
            }
            removedImageIndexes[index].push(imgIndex);
            $(this).closest('.position-relative').remove();
        });

        // Add new image button click
        $(document).on('click', '.add-variation-image', function() {
            const index = $(this).data('index');
            $(`.variation-image-input[data-index="${index}"]`).click();
        });

        // Variation image input change
        $(document).on('change', '.variation-image-input', function() {
            const index = $(this).data('index');
            const preview = $(`#variation-images-${index}`);
            
            // Clear removed indexes for this variation
            removedImageIndexes[index] = [];
                
            // Create preview for each file
            Array.from(this.files).forEach((file, i) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const imgDiv = $(`
                        <div class="position-relative d-inline-block" style="width: 60px; height: 60px; margin: 2px;">
                            <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #ddd;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 remove-uploaded-image" data-index="${index}" data-img-index="${i}" style="width: 20px; height: 20px; font-size: 12px; line-height: 1;">&times;</button>
                        </div>
                    `);
                    preview.append(imgDiv);
                };
                reader.readAsDataURL(file);
            });
        });

        // Remove existing video
$(document).on('click', '.remove-existing-video', function() {
    const variationIndex = $(this).data('variation-index');
    
    // Hide existing video
    $(this).closest('.existing-video').hide();
    
    // Set remove flag to 1
    $(`#remove-video-flag-${variationIndex}`).val('1');
    
    // Clear existing video input
    $(`#existing-video-${variationIndex}`).val('');
});

// Remove new video
$(document).on('click', '.remove-new-video-btn', function() {
    const index = $(this).data('index');
    const input = $(`#video-input-${index}`);
    const preview = $(`#video-preview-${index}`);
    
    input.val('');
    preview.hide();
});

// Video preview functionality
$(document).on('change', '.variation-video-input', function() {
    const index = $(this).attr('id').split('-')[2];
    const file = this.files[0];
    const preview = $(`#video-preview-${index}`);
    
    if (file) {
        // Check file size (max 50MB)
        if (file.size > 50 * 1024 * 1024) {
            alert('File size exceeds the maximum allowed limit of 50 MB. Please upload a smaller file.');
            $(this).val('');
            return;
        }
        
        const videoURL = URL.createObjectURL(file);
        preview.find('source').attr('src', videoURL);
        preview.find('video')[0].load();
        preview.show();
        
        // Reset remove flag when new video is selected
        $(`#remove-video-flag-${index}`).val('0');
    }
});

        // Submit Form with AJAX
        $('#productForm').on('submit', function (e) {
            e.preventDefault();
            
            if (isSubmitting) return;
            isSubmitting = true;
            
            // Disable submit button
            const submitBtn = $('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...');
                
            // Clear previous errors
            $('.error-message').text('');
            $('.form-control, .form-select').removeClass('is-invalid');
            let isValid = true;
            let firstErrorElement = null;

            // Validate required fields
            $('#productForm :input[required]:visible').each(function() {
                if (!$(this).val()) {
                    const fieldName = $(this).attr('name');
                    $(this).addClass('is-invalid');
                    $(`#error-${fieldName}`).text('This field is required');
                    isValid = false;
                        
                    // Track first error for scrolling
                    if (!firstErrorElement) {
                        firstErrorElement = this;
                    }
                }
            });
                
            if (!isValid && firstErrorElement) {
                // Scroll to first error
                $('html, body').animate({
                    scrollTop: $(firstErrorElement).offset().top - 100
                }, 500);
                    
                isSubmitting = false;
                submitBtn.prop('disabled', false).html('Update Product');
                return;
            }
                
            // Add removed image indexes to formData
            Object.keys(removedImageIndexes).forEach(index => {
                removedImageIndexes[index].forEach(i => {
                    // Add removed image indexes to form data if needed
                });
            });

            // Create FormData and add all form inputs
            const formData = new FormData(this);
                
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,                    
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    // Show success notification
                    toastr.success(response.message || 'Operation completed successfully!');
                        
                    // Redirect after a short delay
                    setTimeout(() => {
                        window.location.href = response.redirect || "{{ route('product.index') }}";
                    }, 1500);
                },
                error: function (xhr) {
                    isSubmitting = false;
                    submitBtn.prop('disabled', false).html('Update Product');
                    
                    if (xhr.status === 422) {
                        // Handle validation errors
                        const errors = xhr.responseJSON.errors;
                        
                        for (const field in errors) {
                            let fieldName = field;
                            if (field.includes('.')) {
                                fieldName = field.replace(/\./g, '-');
                            }
                                
                            const errorElement = $(`#error-${fieldName}`);
                            const inputElement = $(`[name="${field}"]`);
                                
                            if (errorElement.length) {
                                errorElement.text(errors[field][0]);
                            }
                            
                            if (inputElement.length) {
                                inputElement.addClass('is-invalid');
                            }
                        }
                            
                        // Scroll to first error if any
                        const firstError = $('.is-invalid').first();
                        if (firstError.length) {
                            $('html, body').animate({
                                scrollTop: firstError.offset().top - 100
                            }, 500);
                        }                        
                    } else {
                        // Show error notification
                        const errorMsg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Something went wrong. Please try again.';
                            
                        toastr.error(errorMsg);
                            
                        // Log full error for debugging
                        console.error('AJAX Error:', xhr.responseText);
                    }
                }
            });
        });

        $(document).ready(function() {
            // Set the selected category if exists
            const selectedCategory = "{{ $selectedCategoryValue ?? '' }}";
            if (selectedCategory) {
                $('#categories_id').val(selectedCategory);
            }

            // Define initial values for edit mode
            const initialPscId = "{{ $initialPscId ?? '' }}";
            const initialCollectionId = "{{ $initialCollectionId ?? '' }}";
            const initialStyleGroupId = "{{ $initialStyleGroupId ?? '' }}";

            // When category changes
            $('#categories_id').change(function() {
                const selectedValue = $(this).val();
                if (!selectedValue) {
                    // Clear the dependent dropdowns
                    $('#psc_id').html('<option value="">Select Style Category</option>');
                    $('#collection_select').html('<option value="">Select Collection</option>');
                    $('#product_style_group_id').html('<option value="">Select Style Group</option>');
                    return;
                }

                const isParent = selectedValue.startsWith('parent_');
                const categoryId = isParent ? selectedValue.replace('parent_', '') : selectedValue;

                $.ajax({
                    url: '{{ route("get.category.psc.and.collections") }}',
                    type: 'GET',
                    data: { 
                        category_id: categoryId,
                        is_parent: isParent ? 1 : 0
                    },
                    success: function(response) {
                        // Populate style categories
                        let styleCatOptions = '<option value="">Select Style Category</option>';
                        response.styleCategories.forEach(function(style) {
                            styleCatOptions += `<option value="${style.psc_id}">${style.psc_name}</option>`;
                        });
                        $('#psc_id').html(styleCatOptions);

                        // Populate collections
                        let collectionOptions = '<option value="">Select Collection</option>';
                        response.collections.forEach(function(collection) {
                            collectionOptions += `<option value="${collection.id}">${collection.name}</option>`;
                        });
                        $('#collection_select').html(collectionOptions);

                        // If we are in edit mode and there are initial values, set them
                        if (initialPscId) {
                            $('#psc_id').val(initialPscId);
                        }
                        if (initialCollectionId) {
                            $('#collection_select').val(initialCollectionId);
                            // Trigger the change event to load style groups for this collection
                            $('#collection_select').trigger('change');
                        }
                    },
                    error: function() {
                        console.error('Failed to load style categories and collections.');
                    }
                });
            });

            // When collection changes
            $('#collection_select').change(function() {
                const collectionId = $(this).val();
                if (!collectionId) {
                    $('#product_style_group_id').html('<option value="">Select Style Group</option>');
                    return;
                }

                $.ajax({
                    url: '{{ route("get.style.groups.by.collection") }}',
                    type: 'GET',
                    data: { collection_id: collectionId },
                    success: function(response) {
                        let options = '<option value="">Select Style Group</option>';
                        response.forEach(function(group) {
                            options += `<option value="${group.psg_id}">${group.psg_names}</option>`;
                        });
                        $('#product_style_group_id').html(options);

                        // If we are in edit mode and there is an initial style group, set it
                        if (initialStyleGroupId) {
                            $('#product_style_group_id').val(initialStyleGroupId);
                        }
                    },
                    error: function() {
                        console.error('Failed to load style groups.');
                    }
                });
            });

            // If there is a selected category, trigger the change to load style categories and collections
            if (selectedCategory) {
                $('#categories_id').trigger('change');
            }
        });

        $('select[name="is_build_product"]').on('change', function() {
            if ($(this).val() == '1') {
                $('#styleCategoryWrapper').show();
            } else {
                $('#styleCategoryWrapper').hide();
            }
        });

        function toggleBuildFields() {
            const isBuild = $('select[name="is_build_product"]').val();
            
            if (isBuild === '1') { 
                $('.non-build-field').hide();
                $('.build-field').show();
                $('.wedding-field').hide();
            } else if (isBuild === '2') { 
                $('.non-build-field').show(); 
                $('.build-field').hide();
                $('.wedding-field').show();
            } else { 
                $('.non-build-field').show();
                $('.build-field').hide();
                $('.wedding-field').hide();
            }
        }

        function toggleWeddingFields() {
            const isBuild = $('#is_build_product').val();
            
            if (isBuild === '2') {
                $('.wedding-field').show();
                $('select[name="gender"]').prop('required', true);
                $('select[name="bond"]').prop('required', true);
            } else {
                $('.wedding-field').hide();
                $('select[name="gender"]').prop('required', false);
                $('select[name="bond"]').prop('required', false);
            }
        }

        $(document).ready(function () {
            toggleBuildFields();
            toggleWeddingFields();
            
            $('select[name="is_build_product"]').change(function() {
                toggleBuildFields();
                toggleWeddingFields();
            });
        });

        $(document).ready(function() {
    // Set the selected category if exists
    const selectedCategory = "{{ $selectedCategoryValue ?? '' }}";
    
    // When category changes
    $('#categories_id').change(function() {
        const selectedValue = $(this).val();
        if (!selectedValue) {
            // Clear the dependent dropdowns
            $('#psc_id').html('<option value="">Select Style Category</option>');
            $('#collection_select').html('<option value="">Select Collection</option>');
            $('#product_style_group_id').html('<option value="">Select Style Group</option>');
            return;
        }

        const isParent = selectedValue.startsWith('parent_');
        const categoryId = isParent ? selectedValue.replace('parent_', '') : selectedValue;

        // Get collections for this category
        $.ajax({
            url: '{{ route("get.collections.by.category") }}',
            type: 'GET',
            data: { 
                category_id: categoryId,
                is_parent: isParent ? 1 : 0
            },
            success: function(response) {
                let collectionOptions = '<option value="">Select Collection</option>';
                response.forEach(function(collection) {
                    collectionOptions += `<option value="${collection.id}">${collection.name}</option>`;
                });
                $('#collection_select').html(collectionOptions);

                // If we are in edit mode and there is an initial collection, set it
                @if(isset($initialCollectionId) && !empty($initialCollectionId))
                    $('#collection_select').val("{{ $initialCollectionId }}");
                    // Trigger the change event to load style groups for this collection
                    $('#collection_select').trigger('change');
                @endif
            },
            error: function() {
                console.error('Failed to load collections.');
                $('#collection_select').html('<option value="">Select Collection</option>');
            }
        });

        // Get style categories for this category
        $.ajax({
            url: '{{ route("get.category.psc.and.collections") }}',
            type: 'GET',
            data: { 
                category_id: categoryId,
                is_parent: isParent ? 1 : 0
            },
            success: function(response) {
                // Populate style categories
                let styleCatOptions = '<option value="">Select Style Category</option>';
                response.styleCategories.forEach(function(style) {
                    styleCatOptions += `<option value="${style.psc_id}">${style.psc_name}</option>`;
                });
                $('#psc_id').html(styleCatOptions);

                // If we are in edit mode and there are initial values, set them
                @if(isset($initialPscId) && !empty($initialPscId))
                    $('#psc_id').val("{{ $initialPscId }}");
                @endif
            },
            error: function() {
                console.error('Failed to load style categories.');
            }
        });
    });

    // When collection changes
    $('#collection_select').change(function() {
        const collectionId = $(this).val();
        if (!collectionId) {
            $('#product_style_group_id').html('<option value="">Select Style Group</option>');
            return;
        }

        $.ajax({
            url: '{{ route("get.style.groups.by.collection") }}',
            type: 'GET',
            data: { collection_id: collectionId },
            success: function(response) {
                let options = '<option value="">Select Style Group</option>';
                response.forEach(function(group) {
                    options += `<option value="${group.psg_id}">${group.psg_names}</option>`;
                });
                $('#product_style_group_id').html(options);

                // If we are in edit mode and there is an initial style group, set it
                @if(isset($initialStyleGroupId) && !empty($initialStyleGroupId))
                    $('#product_style_group_id').val("{{ $initialStyleGroupId }}");
                @endif
            },
            error: function() {
                console.error('Failed to load style groups.');
            }
        });
    });

    // If there is a selected category, trigger the change to load style categories and collections
    if (selectedCategory) {
        $('#categories_id').trigger('change');
    }
});
    </script>
@endsection