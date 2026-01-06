<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\DiamondVendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $categories;
    private $vendors;
    private $importedCount = 0;
    
    public function __construct()
    {
        $this->categories = Category::pluck('category_id', 'category_name')->toArray();
        $this->vendors = DiamondVendor::pluck('vendorid', 'vendor_name')->toArray();
    }
    
    public function model(array $row)
    {
        try {
            DB::beginTransaction();
            
            // Try to find existing product by SKU
            $productSku = $row['products_sku'] ?? null;
            $existingProduct = null;
            
            if (!empty($productSku)) {
                $existingProduct = Product::where('products_sku', $productSku)
                    ->orWhere('master_sku', $productSku)
                    ->first();
            }
            
            if ($existingProduct) {
                // Update existing product
                $product = $this->updateProduct($existingProduct, $row);
            } else {
                // Create new product
                $product = $this->createProduct($row);
            }
            
            DB::commit();
            $this->importedCount++;
            
            return $product;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product import error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function createProduct(array $row)
    {
        // Handle category
        $categoryId = null;
        if (!empty($row['category_name'])) {
            $categoryName = trim($row['category_name']);
            $categoryId = $this->categories[$categoryName] ?? null;
        }
        
        // Handle vendor
        $vendorId = null;
        if (!empty($row['vendor_name'])) {
            $vendorName = trim($row['vendor_name']);
            $vendorId = $this->vendors[$vendorName] ?? null;
        }
        
        // Generate SKU if not provided
        $productSku = $row['products_sku'] ?? null;
        if (empty($productSku)) {
            $productName = $row['products_name'] ?? 'Product';
            $productSku = 'IMPORT-' . Str::slug($productName) . '-' . time();
        }
        
        // Generate slug if not provided
        $slug = $row['products_slug'] ?? null;
        if (empty($slug)) {
            $productName = $row['products_name'] ?? 'product';
            $slug = Str::slug($productName);
        }
        
        // Prepare product data
        $productData = [
            'products_name' => $row['products_name'] ?? 'Imported Product',
            'products_description' => $row['products_description'] ?? null,
            'products_short_description' => $row['products_short_description'] ?? null,
            'gender' => $this->parseGender($row['gender'] ?? '0'),
            'bond' => $this->parseBond($row['bond'] ?? '0'),
            'available' => $this->parseAvailable($row['available'] ?? 'no'),
            'products_quantity' => (int)($row['products_quantity'] ?? 0),
            'products_model' => $row['products_model'] ?? null,
            'products_sku' => $productSku,
            'master_sku' => $row['master_sku'] ?? null,
            'products_price' => (float)($row['products_price'] ?? 0),
            'products_weight' => (float)($row['products_weight'] ?? 0),
            'products_status' => $this->parseStatus($row['products_status'] ?? 1),
            'products_slug' => $slug,
            'categories_id' => $categoryId,
            'parent_category_id' => $row['parent_category_id'] ?? null,
            'vendor_id' => $vendorId,
            'vendor_price' => (float)($row['vendor_price'] ?? 0),
            'is_sale' => $this->parseBoolean($row['is_sale'] ?? '0'),
            'is_gift' => $this->parseBoolean($row['is_gift'] ?? '0'),
            'is_build_product' => $row['is_build_product'] ?? '0',
            'delivery_days' => (int)($row['delivery_days'] ?? 0),
            'default_size' => $row['default_size'] ?? null,
            'products_meta_title' => $row['products_meta_title'] ?? null,
            'products_meta_description' => $row['products_meta_description'] ?? null,
            'date_added' => now(),
            'date_updated' => now(),
            'added_by' => auth()->id() ?? 1,
        ];
        
        return Product::create($productData);
    }
    
    private function updateProduct(Product $product, array $row)
    {
        $updateData = [];
        
        if (isset($row['products_name'])) {
            $updateData['products_name'] = $row['products_name'];
        }
        
        if (isset($row['products_price'])) {
            $updateData['products_price'] = (float)$row['products_price'];
        }
        
        if (isset($row['products_status'])) {
            $updateData['products_status'] = $this->parseStatus($row['products_status']);
        }
        
        if (isset($row['is_sale'])) {
            $updateData['is_sale'] = $this->parseBoolean($row['is_sale']);
        }
        
        if (isset($row['is_gift'])) {
            $updateData['is_gift'] = $this->parseBoolean($row['is_gift']);
        }
        
        $updateData['date_updated'] = now();
        $updateData['updated_by'] = auth()->id() ?? 1;
        
        $product->update($updateData);
        return $product;
    }
    
    public function rules(): array
    {
        return [
            'products_name' => 'required',
            'products_sku' => 'required',
            'products_price' => 'required|numeric|min:0',
            'products_status' => 'required',
        ];
    }
    
    private function parseGender($value)
    {
        $value = strtolower(trim($value));
        if (in_array($value, ['man', 'male', '0', 'm'])) {
            return '0';
        }
        return '1';
    }
    
    private function parseBond($value)
    {
        $value = strtolower(trim($value));
        if (in_array($value, ['metal', '0', 'm'])) {
            return '0';
        }
        return '1';
    }
    
    private function parseAvailable($value)
    {
        $value = strtolower(trim($value));
        return in_array($value, ['yes', 'true', '1', 'available']) ? 'yes' : 'no';
    }
    
    private function parseStatus($value)
    {
        if (is_numeric($value)) {
            return (bool)$value;
        }
        
        $value = strtolower(trim($value));
        return in_array($value, ['active', 'true', '1', 'yes']);
    }
    
    private function parseBoolean($value)
    {
        if (is_numeric($value)) {
            return (bool)$value;
        }
        
        $value = strtolower(trim($value));
        return in_array($value, ['yes', 'true', '1']);
    }
    
    public function getImportedCount()
    {
        return $this->importedCount;
    }
}