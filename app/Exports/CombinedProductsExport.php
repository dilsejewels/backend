<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\ProductVariation;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class CombinedProductsExport implements WithMultipleSheets
{
    use Exportable;
    
    protected $products;
    protected $variations;
    
    public function __construct()
    {
        // Load data with chunking for memory efficiency
        $this->products = Product::with(['productcategory', 'vendor'])
            ->orderBy('products_id', 'desc')
            ->get();
            
        $this->variations = ProductVariation::with(['product', 'shape', 'metalColor', 'diamondQualityGroup'])
            ->orderBy('id', 'desc')
            ->get();
    }
    
    public function sheets(): array
    {
        $sheets = [];
        
        // Products Sheet
        $sheets[] = new ProductsSheet($this->products);
        
        // Variations Sheet
        $sheets[] = new VariationsSheet($this->variations);
        
        return $sheets;
    }
}

class ProductsSheet implements \Maatwebsite\Excel\Concerns\FromCollection, 
                              \Maatwebsite\Excel\Concerns\WithTitle,
                              \Maatwebsite\Excel\Concerns\WithHeadings,
                              \Maatwebsite\Excel\Concerns\WithMapping,
                              \Maatwebsite\Excel\Concerns\WithStyles,
                              \Maatwebsite\Excel\Concerns\WithEvents
{
    protected $products;
    
    public function __construct($products)
    {
        $this->products = $products;
    }
    
    public function collection()
    {
        return $this->products;
    }
    
    public function title(): string
    {
        return 'Products';
    }
    
    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Description',
            'Short Description',
            'Gender',
            'Bond',
            'Available',
            'Quantity',
            'Model',
            'Weight',
            'Status',
            'Slug',
            'Category Name',
            'Parent Category ID',
            'Style Category ID',
            'Collection ID',
            'Style Group ID',
            'Country of Origin',
            'Tax Class ID',
            'Tax',
            'Is Bestseller',
            'Is Featured',
            'Ready to Ship',
            'Is Collection',
            'Diamond Weight Group ID',
            'Diamond Quality ID',
            'Diamond Clarity ID',
            'Diamond Color ID',
            'Diamond Cut ID',
            'Center Stone Type ID',
            'Stone Type ID',
            'Metal Type ID',
            'Metal Color ID',
            'Metal Weight',
            'Is Build Product',
            'Shape IDs',
            'Build Product Type',
            'Certified Lab',
            'Certificate Number',
            'Meta Title',
            'Meta Description',
            'Meta Keyword',
            'Delivery Days',
            'Deleted',
            'Sort Order',
            'Shop Zone ID',
            'Is Sale',
            'Is Gift',
            'Date Added',
            'Date Updated',
        ];
    }
    
    public function map($product): array
    {
        $dateAdded = $this->formatDate($product->date_added);
        $dateUpdated = $this->formatDate($product->date_updated);
        
        // Truncate description to avoid excel cell limits
        $description = $product->products_description ?? '';
        if (strlen($description) > 30000) {
            $description = substr($description, 0, 30000) . '...';
        }
        
        return [
            $product->products_id,
            $product->products_name,
            $description,
            $product->products_short_description,
            $product->gender == '0' ? 'Man' : 'Woman',
            $product->bond == '0' ? 'Metal' : 'Diamond',
            $product->available,
            $product->products_quantity,
            $product->products_model,
            $product->products_weight,
            $product->products_status ? 'Active' : 'Inactive',
            $product->products_slug,
            $product->productcategory->category_name ?? 'N/A',
            $product->parent_category_id,
            $product->psc_id,
            $product->product_collection_id,
            $product->product_style_group_id,
            $product->country_of_origin,
            $product->products_tax_class_id,
            $product->products_tax,
            $product->is_bestseller ? 'Yes' : 'No',
            $product->is_featured ? 'Yes' : 'No',
            $product->ready_to_ship ? 'Yes' : 'No',
            $product->is_collection ? 'Yes' : 'No',
            $product->diamond_weight_group_id,
            $product->diamond_quality_id,
            $product->diamond_clarity_id,
            $product->diamond_color_id,
            $product->diamond_cut_id,
            $product->center_stone_type_id,
            $product->stone_type_id,
            $product->metal_type_id,
            $product->metal_color_id, // ✅ Changed from metalColor->dmt_name to metal_color_id
            $product->metal_weight,
            $product->is_build_product ? 'Yes' : 'No',
            $product->shape_ids,
            $product->build_product_type,
            $product->certified_lab,
            $product->certificate_number,
            $product->products_meta_title,
            $product->products_meta_description,
            $product->products_meta_keyword,
            $product->delivery_days,
            $product->deleted ? 'Yes' : 'No',
            $product->sort_order,
            $product->shop_zone_id,
            $product->is_sale ? 'Yes' : 'No',
            $product->is_gift ? 'Yes' : 'No',
            $dateAdded,
            $dateUpdated,
        ];
    }
    
    private function formatDate($date)
    {
        if (empty($date)) {
            return '';
        }
        
        // If it's already a Carbon instance
        if ($date instanceof \Carbon\Carbon) {
            return $date->format('Y-m-d H:i:s');
        }
        
        // If it's a string, try to parse it
        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
    
    public function styles($sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD']
                ]
            ],
        ];
    }
    
    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                // Get the highest column
                $highestColumn = $event->sheet->getHighestColumn();
                
                // Calculate the number of columns
                $columnCount = $this->columnLetterToNumber($highestColumn);
                
                // Auto-size all columns
                for ($i = 1; $i <= $columnCount; $i++) {
                    $columnLetter = $this->numberToColumnLetter($i);
                    $event->sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }
                
                // Add borders to all cells
                $event->sheet->getStyle('A1:' . $highestColumn . $event->sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                    
                // Set text wrap for description column (C column)
                $event->sheet->getStyle('C2:C' . $event->sheet->getHighestRow())
                    ->getAlignment()
                    ->setWrapText(true);
            },
        ];
    }
    
    private function columnLetterToNumber($columnLetter)
    {
        $columnLetter = strtoupper($columnLetter);
        $length = strlen($columnLetter);
        $number = 0;
        
        for ($i = 0; $i < $length; $i++) {
            $number = $number * 26 + (ord($columnLetter[$i]) - ord('A') + 1);
        }
        
        return $number;
    }
    
    private function numberToColumnLetter($number)
    {
        $columnLetter = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $columnLetter = chr(ord('A') + $remainder) . $columnLetter;
            $number = intval(($number - $remainder) / 26);
        }
        
        return $columnLetter;
    }
}

class VariationsSheet implements \Maatwebsite\Excel\Concerns\FromCollection, 
                               \Maatwebsite\Excel\Concerns\WithTitle,
                               \Maatwebsite\Excel\Concerns\WithHeadings,
                               \Maatwebsite\Excel\Concerns\WithMapping,
                               \Maatwebsite\Excel\Concerns\WithStyles,
                               \Maatwebsite\Excel\Concerns\WithEvents
{
    protected $variations;
    
    public function __construct($variations)
    {
        $this->variations = $variations;
    }
    
    public function collection()
    {
        return $this->variations;
    }
    
    public function title(): string
    {
        return 'Variations';
    }
    
    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Variation ID',
            'Variation SKU',
            'Carat',
            'Price',
            'Regular Price',
            'Stock',
            'Weight',
            'Shape ID',
            'Diamond Weight',
            'Diamond Quality ID',
            'Metal Color ID',
            'Is Best Selling',
            'Created At',
            'Updated At',
        ];
    }
    
    public function map($variation): array
    {
        $createdAt = $this->formatDate($variation->created_at);
        $updatedAt = $this->formatDate($variation->updated_at);
        
        return [
            $variation->product_id, 
            $variation->product->products_name ?? 'N/A',
            $variation->id,
            $variation->sku,
            $variation->carat,
            $variation->price,
            $variation->regular_price,
            $variation->stock,
            $variation->weight,
            $variation->shape_id,
            $variation->diamond_weight,
            $variation->diamond_quality_id,
            $variation->metal_color_id,
            $variation->is_best_selling ? 'Yes' : 'No',
            $createdAt,
            $updatedAt,
        ];
    }
    
    private function formatDate($date)
    {
        if (empty($date)) {
            return '';
        }
        
        // If it's already a Carbon instance
        if ($date instanceof \Carbon\Carbon) {
            return $date->format('Y-m-d H:i:s');
        }
        
        // If it's a string, try to parse it
        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
    
    public function styles($sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E75B6']
                ]
            ],
        ];
    }
    
    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                // Get the highest column
                $highestColumn = $event->sheet->getHighestColumn();
                
                // Calculate the number of columns
                $columnCount = $this->columnLetterToNumber($highestColumn);
                
                // Auto-size all columns
                for ($i = 1; $i <= $columnCount; $i++) {
                    $columnLetter = $this->numberToColumnLetter($i);
                    $event->sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }
                
                // Add borders to all cells
                $event->sheet->getStyle('A1:' . $highestColumn . $event->sheet->getHighestRow())
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
    
    private function columnLetterToNumber($columnLetter)
    {
        $columnLetter = strtoupper($columnLetter);
        $length = strlen($columnLetter);
        $number = 0;
        
        for ($i = 0; $i < $length; $i++) {
            $number = $number * 26 + (ord($columnLetter[$i]) - ord('A') + 1);
        }
        
        return $number;
    }
    
    private function numberToColumnLetter($number)
    {
        $columnLetter = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $columnLetter = chr(ord('A') + $remainder) . $columnLetter;
            $number = intval(($number - $remainder) / 26);
        }
        
        return $columnLetter;
    }
}