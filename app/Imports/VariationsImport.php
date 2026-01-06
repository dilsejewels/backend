<?php

namespace App\Imports;

use App\Models\ProductVariation;
use App\Models\Product;
use App\Models\DiamondShape;
use App\Models\MetalType;
use App\Models\DiamondQualityGroup;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VariationsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $shapes;
    private $metalColors;
    private $diamondQualities;
    private $categories;
    private $importedCount = 0;
    
    public function __construct()
    {
        $this->shapes = DiamondShape::pluck('id', 'name')->toArray();
        $this->metalColors = MetalType::pluck('dmt_id', 'dmt_name')->toArray();
        $this->diamondQualities = DiamondQualityGroup::pluck('dqg_id', 'dqg_name')->toArray();
        $this->categories = Category::pluck('category_id', 'category_name')->toArray();
    }
    
    public function model(array $row)
    {
        try {
            DB::beginTransaction();
            
            // Find product by SKU
            $productSku = $row['product_sku'] ?? null;
            if (empty($productSku)) {
                throw new \Exception("Product SKU is required.");
            }
            
            $product = Product::where('products_sku', $productSku)
                ->orWhere('master_sku', $productSku)
                ->first();
            
            if (!$product) {
                throw new \Exception("Product with SKU '{$productSku}' not found.");
            }
            
            // Get shape
            $shapeId = null;
            if (!empty($row['shape_name'])) {
                $shapeName = trim($row['shape_name']);
                $shapeId = $this->shapes[$shapeName] ?? null;
            }
            
            // Get metal color
            $metalColorId = null;
            if (!empty($row['metal_color_name'])) {
                $metalColorName = trim($row['metal_color_name']);
                $metalColorId = $this->metalColors[$metalColorName] ?? null;
            }
            
            // Get diamond quality
            $diamondQualityId = null;
            if (!empty($row['diamond_quality_name'])) {
                $diamondQualityName = trim($row['diamond_quality_name']);
                $diamondQualityId = $this->diamondQualities[$diamondQualityName] ?? null;
            }
            
            // Generate SKU for variation
            $weight = (float)($row['weight'] ?? 0);
            $weightStr = str_replace('.', '', number_format($weight, 2, '.', ''));
            $shapeCode = $shapeId ? strtoupper(substr(DiamondShape::find($shapeId)->name ?? 'XX', 0, 2)) : 'XX';
            $baseSku = 'PRD-' . $product->products_id . '-' . $shapeCode . '-' . $weightStr;
            $sku = $baseSku;
            
            // Check for duplicates
            $counter = 1;
            while (ProductVariation::where('sku', $sku)->exists()) {
                $sku = $baseSku . '-' . $counter;
                $counter++;
            }
            
            // Create variation
            $variationData = [
                'product_id' => $product->products_id,
                'price' => (float)($row['price'] ?? 0),
                'regular_price' => (float)($row['regular_price'] ?? $row['price'] ?? 0),
                'sku' => $sku,
                'stock' => (int)($row['stock'] ?? 0),
                'is_best_selling' => $this->parseBoolean($row['is_best_selling'] ?? '0'),
                'weight' => $weight,
                'shape_id' => $shapeId,
                'diamond_weight' => (float)($row['diamond_weight'] ?? 0),
                'diamond_quality_id' => $diamondQualityId,
                'metal_color_id' => $metalColorId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $variation = ProductVariation::create($variationData);
            
            DB::commit();
            $this->importedCount++;
            
            return $variation;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Variation import error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function rules(): array
    {
        return [
            'product_sku' => 'required',
            'price' => 'required|numeric|min:0',
            'regular_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'required|numeric|min:0',
        ];
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