<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Category;
use App\Models\DiamondVendor;
use App\Models\DiamondShape;
use App\Models\DiamondQualityGroup;
use App\Models\MetalType;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CombinedProductsImport implements WithMultipleSheets, SkipsUnknownSheets
{
    use Importable;

    private $importedProducts = [];
    private $importedVariations = [];
    private $errors = [];

    public function sheets(): array
    {
        Log::info('Sheets method called');
        return [
            'Products' => new ProductsSheetImport($this),
            'Variations' => new VariationsSheetImport($this),
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        Log::warning("Sheet {$sheetName} was skipped");
        $this->addError([
            'sheet' => 'Unknown',
            'row' => 'N/A',
            'error' => "Sheet '{$sheetName}' was skipped. Only 'Products' and 'Variations' sheets are allowed."
        ]);
    }

    public function setImportedProduct($productId, $excelProductId = null, $productSku = null)
    {
        if ($excelProductId !== null) {
            $this->importedProducts[$excelProductId] = $productId;
        }
        if ($productSku !== null && $productSku !== '') {
            $this->importedProducts[$productSku] = $productId;
        }
    }

    public function getImportedProducts()
    {
        return $this->importedProducts;
    }

    public function setImportedVariations($variations)
    {
        $this->importedVariations = $variations;
    }

    public function getImportedVariations()
    {
        return $this->importedVariations;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSummary()
    {
        return [
            'products' => count(array_unique(array_values($this->importedProducts))),
            'variations' => count($this->importedVariations),
            'errors' => count($this->errors)
        ];
    }
}

class ProductsSheetImport implements ToModel, WithHeadingRow
{
    private $combinedImport;
    private $categories;
    private $vendors;
    private $rowNumber = 0;

    public function __construct(CombinedProductsImport $combinedImport)
    {
        $this->combinedImport = $combinedImport;
        $this->categories = Category::pluck('category_id', 'category_name')->toArray();
        $this->vendors = DiamondVendor::pluck('vendorid', 'vendor_name')->toArray();
    }

    public function headingRow(): int
    {
        Log::info('Setting heading row to 1');
        return 1;
    }

    public function startRow(): int
    {
        Log::info('Setting start row to 2');
        return 2;
    }

    public function model(array $row)
    {
        $this->rowNumber++;

        Log::info("=== START Processing Products Row {$this->rowNumber} ===");
        Log::info("Available keys in row:", array_keys($row));
        Log::info("Row data:", $row);

        try {
            // Check if this row has data
            $isEmptyRow = true;
            foreach ($row as $value) {
                if (!empty($value) && trim($value) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }

            if ($isEmptyRow) {
                Log::info("Row {$this->rowNumber} is empty, skipping");
                return null;
            }

            // Check for product name using multiple possible column names
            $productName = null;
            $excelProductId = null;

            // Try different column name variations
            $possibleNameColumns = [
                'Product Name',
                'product_name',
                'product name',
                'ProductName',
                'productname',
                'Product'
            ];

            $possibleIdColumns = [
                'Product ID',
                'product_id',
                'product id',
                'ProductID',
                'productid',
                'ID'
            ];

            foreach ($possibleNameColumns as $col) {
                if (isset($row[$col]) && !empty(trim($row[$col]))) {
                    $productName = trim($row[$col]);
                    Log::info("Found Product Name in column '{$col}': {$productName}");
                    break;
                }
            }

            foreach ($possibleIdColumns as $col) {
                if (isset($row[$col]) && !empty(trim($row[$col]))) {
                    $excelProductId = trim($row[$col]);
                    Log::info("Found Product ID in column '{$col}': {$excelProductId}");
                    break;
                }
            }

            // If no product name found, try to generate one
            if (empty($productName)) {
                if (!empty($excelProductId)) {
                    $productName = "Product-" . $excelProductId;
                    Log::info("Generated Product Name: {$productName}");
                } else {
                    Log::warning("No Product Name or Product ID found in row {$this->rowNumber}");
                    $this->combinedImport->addError([
                        'sheet' => 'Products',
                        'row' => $this->rowNumber,
                        'error' => 'No Product Name or Product ID found'
                    ]);
                    return null;
                }
            }

            DB::beginTransaction();

            // Try to find existing product
            $existingProduct = null;

            if (!empty($excelProductId) && is_numeric($excelProductId)) {
                $existingProduct = Product::find($excelProductId);
                Log::info("Looking for product by ID {$excelProductId}: " . ($existingProduct ? 'Found' : 'Not found'));
            }

            // If not found by ID, try by product name
            if (!$existingProduct && $productName) {
                $existingProduct = Product::where('products_name', $productName)->first();
                Log::info("Looking for product by name '{$productName}': " . ($existingProduct ? 'Found' : 'Not found'));
            }

            if ($existingProduct) {
                Log::info('Updating existing product:', ['id' => $existingProduct->products_id]);
                $product = $this->updateProduct($existingProduct, $row);
            } else {
                Log::info('Creating new product');
                $product = $this->createProduct($row, $productName, $excelProductId);
            }

            DB::commit();

            // Store product mapping for variations
            $this->combinedImport->setImportedProduct(
                $product->products_id,
                $excelProductId,
                $productName
            );

            Log::info('Product processed successfully:', [
                'id' => $product->products_id,
                'name' => $product->products_name,
                'row' => $this->rowNumber
            ]);

            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product import error at row ' . $this->rowNumber . ': ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            Log::error('Row data that caused error:', $row);

            $this->combinedImport->addError([
                'sheet' => 'Products',
                'row' => $this->rowNumber,
                'error' => $e->getMessage(),
                'data' => json_encode($row)
            ]);

            return null;
        } finally {
            Log::info("=== END Processing Products Row {$this->rowNumber} ===");
        }
    }

    private function createProduct(array $row, $productName, $excelProductId)
    {
        Log::info('Creating product with name: ' . $productName);

        // Get all values
        $description = $this->getValue($row, ['Description', 'description']);
        $shortDescription = $this->getValue($row, ['Short Description', 'short_description']);
        $gender = $this->getValue($row, ['Gender', 'gender'], 'Man');
        $bond = $this->getValue($row, ['Bond', 'bond'], 'Metal');
        $available = $this->getValue($row, ['Available', 'available'], 'yes');
        $quantity = (int)$this->getValue($row, ['Quantity', 'quantity'], 0);
        $model = $this->getValue($row, ['Model', 'model']);
        $weight = (float)$this->getValue($row, ['Weight', 'weight'], 0);
        $status = $this->getValue($row, ['Status', 'status'], 'Active');
        $slug = $this->getValue($row, ['Slug', 'slug']);
        $categoryName = $this->getValue($row, ['Category Name', 'category_name']);
        $vendorName = $this->getValue($row, ['Vendor Name', 'vendor_name']);

        $categoryId = null;
        if (!empty($categoryName) && $categoryName !== 'N/A') {
            $name = trim($categoryName);
            $categoryId = $this->categories[$name] ?? null;

            if (!$categoryId) {
                Log::info("Creating new category: {$name}");
                $new = Category::create([
                    'category_name' => $name,
                    'parent_id' => $this->getValue($row, ['Parent Category ID', 'parent_category_id']),
                ]);

                $categoryId = $new->category_id;
                $this->categories[$name] = $categoryId;
            }
        }

        $vendorId = null;
        if (!empty($vendorName) && $vendorName !== 'N/A') {
            $vendorNameTrimmed = trim($vendorName);
            $vendorId = $this->vendors[$vendorNameTrimmed] ?? null;
            if (!$vendorId) {
                Log::warning("Vendor not found: {$vendorNameTrimmed}");
            }
        }

        if (empty($slug) || $slug === 'N/A') {
            $slug = Str::slug($productName) . '-' . time();
        }

        // Make slug unique
        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('products_slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Build product data - only essential fields for now
        $productData = [
            'products_name' => $productName,
            'products_description' => $description,
            'products_short_description' => $shortDescription,
            'gender' => $this->parseGender($gender),
            'bond' => $this->parseBond($bond),
            'available' => $this->parseAvailable($available),
            'products_quantity' => $quantity,
            'products_model' => $model,
            'products_weight' => $weight,
            'products_status' => $this->parseStatus($status),
            'products_slug' => $slug,

            // Essential foreign keys
            'vendor_id' => $vendorId,
            'categories_id' => $categoryId,

            // System fields
            'date_added' => now(),
            'date_updated' => now(),
            'added_by' => auth()->id() ?? 1,
            'updated_by' => auth()->id() ?? 1,
        ];

        // Define boolean fields that need special parsing
        $booleanFields = [
            'is_bestseller',
            'is_featured',
            'ready_to_ship',
            'is_collection',
            'is_sale',
            'is_gift',
            'deleted'
        ];

        // Define float fields
        $floatFields = [
            'products_tax',
            'metal_weight',
            'delivery_days',
            'sort_order'
        ];

        // Add optional fields if they exist
        $optionalFields = [
            'parent_category_id' => ['Parent Category ID', 'parent_category_id'],
            'psc_id' => ['Style Category ID', 'style_category_id'],
            'product_collection_id' => ['Collection ID', 'collection_id'],
            'product_style_group_id' => ['Style Group ID', 'style_group_id'],
            'country_of_origin' => ['Country of Origin', 'country_of_origin'],
            'products_tax_class_id' => ['Tax Class ID', 'tax_class_id'],
            'products_tax' => ['Tax', 'tax'],
            'is_bestseller' => ['Is Bestseller', 'is_bestseller'],
            'is_featured' => ['Is Featured', 'is_featured'],
            'ready_to_ship' => ['Ready to Ship', 'ready_to_ship'],
            'is_collection' => ['Is Collection', 'is_collection'],
            'is_build_product' => ['Is Build Product', 'is_build_product'],
            'is_sale' => ['Is Sale', 'is_sale'],
            'is_gift' => ['Is Gift', 'is_gift'],
            'diamond_weight_group_id' => ['Diamond Weight Group ID', 'diamond_weight_group_id'],
            'diamond_quality_id' => ['Diamond Quality ID', 'diamond_quality_id'],
            'diamond_clarity_id' => ['Diamond Clarity ID', 'diamond_clarity_id'],
            'diamond_color_id' => ['Diamond Color ID', 'diamond_color_id'],
            'diamond_cut_id' => ['Diamond Cut ID', 'diamond_cut_id'],
            'center_stone_type_id' => ['Center Stone Type ID', 'center_stone_type_id'],
            'stone_type_id' => ['Stone Type ID', 'stone_type_id'],
            'metal_type_id' => ['Metal Type ID', 'metal_type_id'],
            'metal_color_id' => ['Metal Color ID', 'metal_color_id'],
            'metal_weight' => ['Metal Weight', 'metal_weight'],
            'build_product_type' => ['Build Product Type', 'build_product_type'],
            'shape_ids' => ['Shape IDs', 'shape_ids'],
            'certified_lab' => ['Certified Lab', 'certified_lab'],
            'certificate_number' => ['Certificate Number', 'certificate_number'],
            'products_meta_title' => ['Meta Title', 'meta_title'],
            'products_meta_description' => ['Meta Description', 'meta_description'],
            'products_meta_keyword' => ['Meta Keyword', 'meta_keyword'],
            'delivery_days' => ['Delivery Days', 'delivery_days'],
            'deleted' => ['Deleted', 'deleted'],
            'sort_order' => ['Sort Order', 'sort_order'],
            'shop_zone_id' => ['Shop Zone ID', 'shop_zone_id'],
        ];

        foreach ($optionalFields as $dbField => $excelFields) {
            $value = $this->getValue($row, $excelFields);

            // Skip if value is null, empty, or 'N/A'
            if ($value === null || $value === '' || $value === 'N/A') {
                continue;
            }

            // Parse boolean fields
            if (in_array($dbField, $booleanFields)) {
                $productData[$dbField] = $this->parseBoolean($value);
            }
            // Parse float fields
            elseif (in_array($dbField, $floatFields)) {
                $productData[$dbField] = is_numeric($value) ? (float)$value : 0;
            }
            // Parse integer fields (IDs)
            elseif (strpos($dbField, '_id') !== false || in_array($dbField, ['country_of_origin', 'shop_zone_id'])) {
                $productData[$dbField] = is_numeric($value) ? (int)$value : null;
            }
            // Handle is_build_product as string (it's actually a string field based on your Product model constants)
            elseif ($dbField === 'is_build_product') {
                // Map string values to your constants
                $valueLower = strtolower(trim($value));
                if ($valueLower === 'jewelry' || $valueLower === '0') {
                    $productData[$dbField] = '0';
                } elseif ($valueLower === 'engagement' || $valueLower === '1') {
                    $productData[$dbField] = '1';
                } elseif ($valueLower === 'wedding' || $valueLower === '2') {
                    $productData[$dbField] = '2';
                } elseif ($valueLower === 'gifts' || $valueLower === '3') {
                    $productData[$dbField] = '3';
                } elseif ($valueLower === 'sale' || $valueLower === '4') {
                    $productData[$dbField] = '4';
                } else {
                    $productData[$dbField] = $value;
                }
            }
            // Everything else
            else {
                $productData[$dbField] = $value;
            }
        }

        Log::info('Creating product with data:', $productData);

        $product = Product::create($productData);

        return $product;
    }

    private function updateProduct(Product $product, array $row)
    {
        $update = [];

        // Define all fields that can be updated
        $updatableFields = [
            'products_name' => ['Product Name', 'product_name'],
            'products_description' => ['Description', 'description'],
            'products_short_description' => ['Short Description', 'short_description'],
            'gender' => ['Gender', 'gender'],
            'bond' => ['Bond', 'bond'],
            'available' => ['Available', 'available'],
            'products_quantity' => ['Quantity', 'quantity'],
            'products_model' => ['Model', 'model'],
            'products_weight' => ['Weight', 'weight'],
            'products_status' => ['Status', 'status'],
            'products_slug' => ['Slug', 'slug'],
            'is_sale' => ['Is Sale', 'is_sale'],
            'is_gift' => ['Is Gift', 'is_gift'],
            'is_bestseller' => ['Is Bestseller', 'is_bestseller'],
            'is_featured' => ['Is Featured', 'is_featured'],
            'ready_to_ship' => ['Ready to Ship', 'ready_to_ship'],
            'is_collection' => ['Is Collection', 'is_collection'],
            'delivery_days' => ['Delivery Days', 'delivery_days'],
            'sort_order' => ['Sort Order', 'sort_order'],
            'shop_zone_id' => ['Shop Zone ID', 'shop_zone_id'],
        ];

        foreach ($updatableFields as $field => $keys) {
            $value = $this->getValue($row, $keys);
            if ($value !== null && $value !== '' && $value !== 'N/A') {
                // Parse special fields
                if ($field === 'gender') {
                    $update[$field] = $this->parseGender($value);
                } elseif ($field === 'bond') {
                    $update[$field] = $this->parseBond($value);
                } elseif ($field === 'available') {
                    $update[$field] = $this->parseAvailable($value);
                } elseif ($field === 'products_status') {
                    $update[$field] = $this->parseStatus($value);
                } elseif (in_array($field, ['is_sale', 'is_gift', 'is_bestseller', 'is_featured', 'ready_to_ship', 'is_collection'])) {
                    $update[$field] = $this->parseBoolean($value);
                } elseif (in_array($field, ['products_quantity', 'delivery_days', 'sort_order', 'shop_zone_id'])) {
                    $update[$field] = (int)$value;
                } elseif (in_array($field, ['products_weight'])) {
                    $update[$field] = (float)$value;
                } else {
                    $update[$field] = $value;
                }
            }
        }

        // Handle category update
        $categoryName = $this->getValue($row, ['Category Name', 'category_name']);
        if ($categoryName && $categoryName !== 'N/A') {
            $name = trim($categoryName);
            $categoryId = $this->categories[$name] ?? null;

            if (!$categoryId) {
                Log::info("Creating new category: {$name}");
                $new = Category::create([
                    'category_name' => $name,
                    'parent_id' => $this->getValue($row, ['Parent Category ID', 'parent_category_id']),
                ]);
                $categoryId = $new->category_id;
                $this->categories[$name] = $categoryId;
            }

            if ($categoryId && $product->categories_id !== $categoryId) {
                $update['categories_id'] = $categoryId;
            }
        }

        // Handle vendor update
        $vendorName = $this->getValue($row, ['Vendor Name', 'vendor_name']);
        if ($vendorName && $vendorName !== 'N/A') {
            $vendorNameTrimmed = trim($vendorName);
            $vendorId = $this->vendors[$vendorNameTrimmed] ?? null;
            if ($vendorId && $product->vendor_id !== $vendorId) {
                $update['vendor_id'] = $vendorId;
            }
        }

        if (!empty($update)) {
            $update['date_updated'] = now();
            $update['updated_by'] = auth()->id() ?? 1;

            Log::info('Updating product with:', $update);
            $product->update($update);
        }

        return $product;
    }

    private function getValue($row, $keys, $default = null)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                $value = trim($row[$key]);
                return $value !== '' ? $value : $default;
            }
        }

        return $default;
    }

    private function parseGender($v)
    {
        if ($v === null || $v === '') return '0';

        $v = strtolower(trim($v));
        if (in_array($v, ['woman', 'female', '1', 'w', 'f', 'women'])) {
            return '1';
        }
        return '0';
    }

    private function parseBond($v)
    {
        if ($v === null || $v === '') return '0';

        $v = strtolower(trim($v));
        if (in_array($v, ['diamond', '1', 'd'])) {
            return '1';
        }
        return '0';
    }

    private function parseAvailable($v)
    {
        if ($v === null || $v === '') return 'no';

        $v = strtolower(trim($v));
        return in_array($v, ['yes', '1', 'true', 'y', 'available']) ? 'yes' : 'no';
    }

    private function parseStatus($v)
    {
        if ($v === null || $v === '') return 1;

        $v = strtolower(trim($v));
        return in_array($v, ['1', 'yes', 'true', 'active', 'a', 'enabled']) ? 1 : 0;
    }

    private function parseBoolean($v)
    {
        if ($v === null || $v === '') return 0;

        if (is_numeric($v)) {
            return (int)$v ? 1 : 0;
        }

        $v = strtolower(trim($v));
        $trueValues = ['yes', 'true', 'y', '1', 'active', 'on', 'enabled'];

        return in_array($v, $trueValues) ? 1 : 0;
    }
}

class VariationsSheetImport implements ToModel, WithHeadingRow
{
    private $combinedImport;
    private $shapes;
    private $metalColors;
    private $diamondQualities;
    private $rowNumber = 0;

    public function __construct(CombinedProductsImport $combinedImport)
    {
        $this->combinedImport = $combinedImport;
        $this->shapes = DiamondShape::pluck('id', 'name')->toArray();
        $this->metalColors = MetalType::pluck('dmt_id', 'dmt_name')->toArray();
        $this->diamondQualities = DiamondQualityGroup::pluck('dqg_id', 'dqg_name')->toArray();
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $this->rowNumber++;

        Log::info("=== START Processing Variations Row {$this->rowNumber} ===");
        Log::info("Available keys in row:", array_keys($row));

        try {
            // Check if this row has data
            $isEmptyRow = true;
            foreach ($row as $value) {
                if (!empty($value) && trim($value) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }

            if ($isEmptyRow) {
                Log::info("Row {$this->rowNumber} is empty, skipping");
                return null;
            }

            // Get product identifiers
            $excelProductId = null;
            $productName = null;

            $possibleIdColumns = ['Product ID', 'product_id', 'product id', 'ProductID', 'productid', 'ID'];
            $possibleNameColumns = ['Product Name', 'product_name', 'product name', 'ProductName', 'productname'];

            foreach ($possibleIdColumns as $col) {
                if (isset($row[$col]) && !empty(trim($row[$col]))) {
                    $excelProductId = trim($row[$col]);
                    break;
                }
            }

            foreach ($possibleNameColumns as $col) {
                if (isset($row[$col]) && !empty(trim($row[$col]))) {
                    $productName = trim($row[$col]);
                    break;
                }
            }

            Log::info("Looking for product - Excel ID: '{$excelProductId}', Name: '{$productName}'");

            $productId = null;

            // Try to find product
            if (!empty($excelProductId)) {
                $map = $this->combinedImport->getImportedProducts();
                if (isset($map[$excelProductId])) {
                    $productId = $map[$excelProductId];
                    Log::info("Found product by Excel ID {$excelProductId} -> DB ID {$productId}");
                }
            }

            if (!$productId && !empty($productName)) {
                $map = $this->combinedImport->getImportedProducts();
                if (isset($map[$productName])) {
                    $productId = $map[$productName];
                    Log::info("Found product by Name '{$productName}' -> DB ID {$productId}");
                }
            }

            if (!$productId && !empty($productName)) {
                $product = Product::where('products_name', $productName)->first();
                if ($product) {
                    $productId = $product->products_id;
                    Log::info("Found product in DB by Name '{$productName}' -> ID {$productId}");
                }
            }

            if (!$productId && !empty($excelProductId) && is_numeric($excelProductId)) {
                $product = Product::find($excelProductId);
                if ($product) {
                    $productId = $product->products_id;
                    Log::info("Found product in DB by ID {$excelProductId}");
                }
            }

            if (!$productId) {
                throw new \Exception("Product not found. Excel ID: {$excelProductId}, Name: {$productName}");
            }

            DB::beginTransaction();

            // Get SKU - try to use provided SKU first
            $providedSku = $this->getValue($row, ['Variation SKU', 'variation_sku', 'SKU', 'sku']);

            // Get other identifying fields
            $shapeId = $this->getValue($row, ['Shape ID', 'shape_id']);
            $weight = (float)$this->getValue($row, ['Weight', 'weight'], 0);
            $diamondWeight = (float)$this->getValue($row, ['Diamond Weight', 'diamond_weight'], 0);
            $metalColorId = $this->getValue($row, ['Metal Color ID', 'metal_color_id']);
            $carat = $this->getValue($row, ['Carat', 'carat']);

            // Try to find existing variation
            $existingVariation = null;

            // First, try by provided SKU if available
            if ($providedSku && $providedSku !== 'N/A') {
                $existingVariation = ProductVariation::where('sku', $providedSku)
                    ->where('product_id', $productId)
                    ->first();
                if ($existingVariation) {
                    Log::info("Found existing variation by provided SKU: {$providedSku}");
                }
            }

            // If not found by SKU, try by combination of unique fields
            if (!$existingVariation) {
                $existingVariation = ProductVariation::where('product_id', $productId)
                    ->where('shape_id', $shapeId)
                    ->where('weight', $weight)
                    ->where('diamond_weight', $diamondWeight)
                    ->where('metal_color_id', $metalColorId)
                    ->first();

                if ($existingVariation) {
                    Log::info("Found existing variation by combination of attributes");
                }
            }

            // Generate SKU if needed
            $sku = $providedSku;
            if (!$sku || $sku === 'N/A') {
                $product = Product::find($productId);
                $shapeCode = 'XX';
                if ($shapeId) {
                    $shape = DiamondShape::find($shapeId);
                    if ($shape) {
                        $shapeCode = strtoupper(substr($shape->name, 0, 2));
                    }
                }
                $weightStr = str_replace('.', '', number_format($weight, 2, '.', ''));
                $baseSku = 'PRD-' . $productId . '-' . $shapeCode . '-' . $weightStr;
                $sku = $baseSku;

                $counter = 1;
                while (ProductVariation::where('sku', $sku)->exists()) {
                    $sku = $baseSku . '-' . $counter;
                    $counter++;
                }
            }

            // Build variation data
            $variationData = [
                'product_id' => $productId,
                'price' => (float)$this->getValue($row, ['Price', 'price'], 0),
                'regular_price' => (float)$this->getValue($row, ['Regular Price', 'regular_price', 'Price', 'price'], 0),
                'sku' => $sku,
                'stock' => (int)$this->getValue($row, ['Stock', 'stock'], 0),
                'weight' => $weight,
                'shape_id' => $shapeId,
                'diamond_weight' => $diamondWeight,
                'diamond_quality_id' => $this->getValue($row, ['Diamond Quality ID', 'diamond_quality_id']),
                'metal_color_id' => $metalColorId,
                'is_best_selling' => $this->parseBoolean($this->getValue($row, ['Is Best Selling', 'is_best_selling'], '0')),
                'updated_at' => now(),
            ];

            // Add carat if provided
            if ($carat && $carat !== 'N/A') {
                $variationData['carat'] = (float)$carat;
            }

            // Clean empty values
            foreach ($variationData as $key => $value) {
                if ($value === '' || $value === 'N/A') {
                    $variationData[$key] = null;
                }
            }

            if ($existingVariation) {
                // Update existing variation
                Log::info('Updating existing variation with data:', $variationData);
                $existingVariation->update($variationData);
                $variation = $existingVariation;
            } else {
                // Create new variation
                $variationData['created_at'] = now();
                Log::info('Creating new variation with data:', $variationData);
                $variation = ProductVariation::create($variationData);
            }

            DB::commit();

            // Store variation reference
            $importedVariations = $this->combinedImport->getImportedVariations();
            $importedVariations[] = $variation->id;
            $this->combinedImport->setImportedVariations($importedVariations);

            Log::info('Variation processed successfully:', ['id' => $variation->id]);

            return $variation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Variation import error at row ' . $this->rowNumber . ': ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            $this->combinedImport->addError([
                'sheet' => 'Variations',
                'row' => $this->rowNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        } finally {
            Log::info("=== END Processing Variations Row {$this->rowNumber} ===");
        }
    }

    private function getValue($row, $keys, $default = null)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                $value = trim($row[$key]);
                return $value !== '' ? $value : $default;
            }
        }

        return $default;
    }

    private function parseBoolean($v)
    {
        if ($v === null || $v === '') return 0;

        $v = strtolower(trim($v));
        return in_array($v, ['yes', '1', 'true', 'y']) ? 1 : 0;
    }
}
