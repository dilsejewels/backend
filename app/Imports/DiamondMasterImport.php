<?php

namespace App\Imports;

use App\Models\DiamondMaster;
use App\Models\DiamondVendor;
use App\Models\DiamondShape;
use App\Models\DiamondColor;
use App\Models\DiamondClarityMaster;
use App\Models\DiamondCut;
use App\Models\DiamondCulet;
use App\Models\DiamondFancyColorIntensity;
use App\Models\DiamondPolish;
use App\Models\DiamondFancyColor;
use App\Models\DiamondSymmetry;
use App\Models\DiamondFlourescence;
use App\Models\DiamondLab;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiamondMasterImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use SkipsFailures;

    private $vendors;
    private $shapes;
    private $colors;
    private $clarities;
    private $cuts;
    private $polishes;
    private $symmetries;
    private $fluorescences;
    private $labs;
    private $importedCount = 0;
    private $updatedCount = 0;
    private $errors = [];

    public function __construct()
    {
        $this->vendors = DiamondVendor::pluck('vendorid', 'vendor_name')->toArray();
        $this->shapes = DiamondShape::pluck('id', 'name')->toArray();
        $this->colors = DiamondColor::pluck('id', 'name')->toArray();
        $this->clarities = DiamondClarityMaster::pluck('id', 'name')->toArray();
        $this->cuts = DiamondCut::pluck('id', 'name')->toArray();
        $this->polishes = DiamondPolish::pluck('id', 'name')->toArray();
        $this->symmetries = DiamondSymmetry::pluck('id', 'name')->toArray();
        $this->fluorescences = DiamondFlourescence::pluck('id', 'name')->toArray();
        $this->labs = DiamondLab::pluck('dl_id', 'dl_name')->toArray();
    }

    public function model(array $row)
    {
        Log::info('Processing row:', $row);
        
        // Handle both export and import formats
        // Check if it's export format (has Diamond ID) or import format
        if (isset($row['diamond_id'])) {
            // This is export format, remap to import format
            $row = $this->remapExportToImport($row);
        }
        
        // Skip if required fields are missing
        if (empty($row['stock_number']) || empty($row['carat_weight'])) {
            Log::warning('Skipping row due to missing required fields:', $row);
            return null;
        }

        try {
            // Check if diamond exists
            $existingDiamond = DiamondMaster::where('stock_number', $row['stock_number'])
                ->orWhere('certificate_number', $row['certificate_number'] ?? null)
                ->first();

            $diamondData = [
                'diamond_type' => $this->parseDiamondType($row['diamond_type'] ?? 'Natural'),
                'quantity' => (int)($row['quantity'] ?? 1),
                'vendor_id' => $this->getVendorId($row['vendor_name'] ?? null),
                'vendor_stock_number' => $row['vendor_stock_number'] ?? null,
                'stock_number' => $row['stock_number'] ?? null,
                'shape' => $this->getShapeId($row['shape'] ?? null),
                'carat_weight' => (float)($row['carat_weight'] ?? 0),
                'color' => $this->getColorId($row['color'] ?? null),
                'clarity' => $this->getClarityId($row['clarity'] ?? null),
                'cut' => $this->getCutId($row['cut'] ?? null),
                'polish' => $this->getPolishId($row['polish'] ?? null),
                'symmetry' => $this->getSymmetryId($row['symmetry'] ?? null),
                'fluorescence' => $this->getFluorescenceId($row['fluorescence'] ?? null),
                'fancy_color_intensity' => $this->getFancyColorIntensityId($row['fancy_color_intensity'] ?? null),
                'fancy_color_overtone' => $this->getFancyColorId($row['fancy_color_overtone'] ?? null),
                'price' => (float)($row['price'] ?? 0),
                'msrp_price' => (float)($row['msrp_price'] ?? 0),
                'price_per_carat' => (float)($row['price_per_carat'] ?? 0),
                'certificate_company' => $this->getLabId($row['certificate_company'] ?? null),
                'certificate_number' => $row['certificate_number'] ?? null,
                'certificate_date' => $this->parseDate($row['certificate_date'] ?? null),
                'measurements' => $row['measurements'] ?? null,
                'measurement_l' => (float)($row['measurement_l'] ?? 0),
                'measurement_w' => (float)($row['measurement_w'] ?? 0),
                'measurement_h' => (float)($row['measurement_h'] ?? 0),
                'depth' => (float)($row['depth'] ?? 0),
                'table_diamond' => (float)($row['table'] ?? 0),
                'vendor_rap_disc' => (float)($row['vendor_rap_disc'] ?? 0),
                'culet' => $this->getCuletId($row['culet'] ?? null),
                'is_superdeal' => $this->parseBoolean($row['is_superdeal'] ?? 'No'),
                'availability' => $this->parseAvailability($row['availability'] ?? 'Available'),
                'status' => $this->parseBoolean($row['status'] ?? 'Active'),
                'image_link' => $row['image_link'] ?? null,
                'cert_link' => $row['cert_link'] ?? null,
                'video_link' => $row['video_link'] ?? null,
                'date_added' => now(),
                'date_updated' => now(),
                'added_by' => auth()->id() ?? 1,
                'updated_by' => auth()->id() ?? 1,
            ];

            // Auto-calculate measurements
            if (empty($diamondData['measurements']) && 
                ($diamondData['measurement_l'] || $diamondData['measurement_w'] || $diamondData['measurement_h'])) {
                $measurements = [];
                if ($diamondData['measurement_l']) $measurements[] = $diamondData['measurement_l'];
                if ($diamondData['measurement_w']) $measurements[] = $diamondData['measurement_w'];
                if ($diamondData['measurement_h']) $measurements[] = $diamondData['measurement_h'];
                $diamondData['measurements'] = implode(' x ', $measurements);
            }

            if ($existingDiamond) {
                $existingDiamond->update($diamondData);
                $this->updatedCount++;
                Log::info("Updated diamond: {$existingDiamond->diamondid}");
                return $existingDiamond;
            } else {
                $diamond = DiamondMaster::create($diamondData);
                $this->importedCount++;
                Log::info("Created diamond: {$diamond->diamondid}");
                return $diamond;
            }

        } catch (\Exception $e) {
            Log::error('Error importing row: ' . $e->getMessage());
            Log::error('Row data: ', $row);
            return null;
        }
    }

    private function remapExportToImport($row)
    {
        // Map export column names to import column names
        $mapping = [
            'diamond_id' => null, // Skip this
            'diamond_type' => 'diamond_type',
            'quantity' => 'quantity',
            'vendor_name' => 'vendor_name',
            'vendor_stock_number' => 'vendor_stock_number',
            'stock_number' => 'stock_number',
            'shape' => 'shape',
            'carat_weight' => 'carat_weight',
            'color' => 'color',
            'clarity' => 'clarity',
            'cut' => 'cut',
            'polish' => 'polish',
            'symmetry' => 'symmetry',
            'fluorescence' => 'fluorescence',
            'price' => 'price',
            'msrp_price' => 'msrp_price',
            'price_per_carat' => 'price_per_carat',
            'certificate_company' => 'certificate_company',
            'certificate_number' => 'certificate_number',
            'certificate_date' => 'certificate_date',
            'measurements' => 'measurements',
            'measurement_l' => 'measurement_l',
            'measurement_w' => 'measurement_w',
            'measurement_h' => 'measurement_h',
            'depth' => 'depth',
            'table' => 'table',
            'vendor_rap_disc' => 'vendor_rap_disc',
            'is_superdeal' => 'is_superdeal',
            'availability' => 'availability',
            'status' => 'status',
            'date_added' => null, // Skip this
        ];

        $importRow = [];
        foreach ($mapping as $exportKey => $importKey) {
            if (isset($row[$exportKey]) && $importKey) {
                $importRow[$importKey] = $row[$exportKey];
            }
        }
        
        // Set default values for missing columns in export
        $importRow['fancy_color_intensity'] = $row['fancy_color_intensity'] ?? '';
        $importRow['fancy_color_overtone'] = $row['fancy_color_overtone'] ?? '';
        $importRow['culet'] = $row['culet'] ?? '';
        $importRow['image_link'] = $row['image_link'] ?? '';
        $importRow['cert_link'] = $row['cert_link'] ?? '';
        $importRow['video_link'] = $row['video_link'] ?? '';

        return $importRow;
    }

 // Helper methods (keep your existing helper methods but add this new one)
    private function getVendorId($vendorName)
    {
        if (empty($vendorName)) return null;
        
        // Try exact match
        if (isset($this->vendors[$vendorName])) {
            return $this->vendors[$vendorName];
        }
        
        // Try case-insensitive match
        foreach ($this->vendors as $name => $id) {
            if (strcasecmp($name, $vendorName) === 0) {
                return $id;
            }
        }
        
        Log::warning("Vendor '{$vendorName}' not found. Available vendors: " . implode(', ', array_keys($this->vendors)));
        return null;
    }


    private function getShapeId($shapeValue)
    {
        if (empty($shapeValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($shapeValue)) {
            // Check if shape exists with this ID
            $shape = DiamondShape::find($shapeValue);
            if ($shape) return $shapeValue;
        }
        
        // Try to find by name (case-insensitive)
        foreach ($this->shapes as $name => $id) {
            if (strtolower($name) === strtolower($shapeValue)) {
                return $id;
            }
        }
        
        // Try partial match
        foreach ($this->shapes as $name => $id) {
            if (strpos(strtolower($name), strtolower($shapeValue)) !== false) {
                Log::info("Partial match found for shape: {$shapeValue} -> {$name}");
                return $id;
            }
        }
        
        Log::warning("Shape '{$shapeValue}' not found in database. Available shapes: " . implode(', ', array_keys($this->shapes)));
        return null;
    }

    private function getColorId($colorValue)
    {
        if (empty($colorValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($colorValue)) {
            $color = DiamondColor::find($colorValue);
            if ($color) return $colorValue;
        }
        
        // Try to find by name
        foreach ($this->colors as $name => $id) {
            if (strtolower($name) === strtolower($colorValue)) {
                return $id;
            }
        }
        
        Log::warning("Color '{$colorValue}' not found in database");
        return null;
    }

    private function getClarityId($clarityValue)
    {
        if (empty($clarityValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($clarityValue)) {
            $clarity = DiamondClarityMaster::find($clarityValue);
            if ($clarity) return $clarityValue;
        }
        
        // Try to find by name
        foreach ($this->clarities as $name => $id) {
            if (strtolower($name) === strtolower($clarityValue)) {
                return $id;
            }
        }
        
        Log::warning("Clarity '{$clarityValue}' not found in database");
        return null;
    }

    private function getCutId($cutValue)
    {
        if (empty($cutValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($cutValue)) {
            $cut = DiamondCut::find($cutValue);
            if ($cut) return $cutValue;
        }
        
        // Try to find by name
        foreach ($this->cuts as $name => $id) {
            if (strtolower($name) === strtolower($cutValue)) {
                return $id;
            }
        }
        
        Log::warning("Cut '{$cutValue}' not found in database");
        return null;
    }

    private function getPolishId($polishValue)
    {
        if (empty($polishValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($polishValue)) {
            $polish = DiamondPolish::find($polishValue);
            if ($polish) return $polishValue;
        }
        
        // Try to find by name
        foreach ($this->polishes as $name => $id) {
            if (strtolower($name) === strtolower($polishValue)) {
                return $id;
            }
        }
        
        Log::warning("Polish '{$polishValue}' not found in database");
        return null;
    }

    private function getSymmetryId($symmetryValue)
    {
        if (empty($symmetryValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($symmetryValue)) {
            $symmetry = DiamondSymmetry::find($symmetryValue);
            if ($symmetry) return $symmetryValue;
        }
        
        // Try to find by name
        foreach ($this->symmetries as $name => $id) {
            if (strtolower($name) === strtolower($symmetryValue)) {
                return $id;
            }
        }
        
        Log::warning("Symmetry '{$symmetryValue}' not found in database");
        return null;
    }

    private function getFluorescenceId($fluorescenceValue)
    {
        if (empty($fluorescenceValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($fluorescenceValue)) {
            $fluorescence = DiamondFlourescence::find($fluorescenceValue);
            if ($fluorescence) return $fluorescenceValue;
        }
        
        // Try to find by name
        foreach ($this->fluorescences as $name => $id) {
            if (strtolower($name) === strtolower($fluorescenceValue)) {
                return $id;
            }
        }
        
        Log::warning("Fluorescence '{$fluorescenceValue}' not found in database");
        return null;
    }

    private function getLabId($labValue)
    {
        if (empty($labValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($labValue)) {
            $lab = DiamondLab::find($labValue);
            if ($lab) return $labValue;
        }
        
        // Try to find by name
        foreach ($this->labs as $name => $id) {
            if (strtolower($name) === strtolower($labValue)) {
                return $id;
            }
        }
        
        Log::warning("Lab '{$labValue}' not found in database");
        return null;
    }

    private function getCuletId($culetValue)
    {
        if (empty($culetValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($culetValue)) {
            $culet = DiamondCulet::find($culetValue);
            if ($culet) return $culetValue;
        }
        
        // Try to find by name
        foreach ($this->cullets as $name => $id) {
            if (strtolower($name) === strtolower($culetValue)) {
                return $id;
            }
        }
        
        Log::warning("Culet '{$culetValue}' not found in database");
        return null;
    }

    private function getFancyColorIntensityId($intensityValue)
    {
        if (empty($intensityValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($intensityValue)) {
            $intensity = DiamondFancyColorIntensity::find($intensityValue);
            if ($intensity) return $intensityValue;
        }
        
        // Try to find by name
        foreach ($this->fancyColorIntensities as $name => $id) {
            if (strtolower($name) === strtolower($intensityValue)) {
                return $id;
            }
        }
        
        Log::warning("Fancy Color Intensity '{$intensityValue}' not found in database");
        return null;
    }

    private function getFancyColorId($colorValue)
    {
        if (empty($colorValue)) return null;
        
        // If numeric, assume it's an ID
        if (is_numeric($colorValue)) {
            $color = DiamondFancyColor::find($colorValue);
            if ($color) return $colorValue;
        }
        
        // Try to find by name
        foreach ($this->fancyColors as $name => $id) {
            if (strtolower($name) === strtolower($colorValue)) {
                return $id;
            }
        }
        
        Log::warning("Fancy Color '{$colorValue}' not found in database");
        return null;
    }

    private function parseDiamondType($type)
    {
        if ($type === null || $type === '') return 1;
        
        if (is_numeric($type)) {
            return (int)$type;
        }
        
        $type = strtolower(trim($type));
        if (in_array($type, ['lab created', 'lab', '2', 'lab-grown', 'synthetic'])) {
            return 2;
        }
        return 1; // Default to Natural
    }

    private function parseBoolean($value)
    {
        if ($value === null || $value === '') return 0;
        
        if (is_numeric($value)) {
            return (int)$value ? 1 : 0;
        }
        
        $value = strtolower(trim($value));
        $trueValues = ['yes', 'true', 'y', '1', 'active', 'on', 'enabled'];
        
        return in_array($value, $trueValues) ? 1 : 0;
    }

    private function parseAvailability($value)
    {
        if ($value === null || $value === '') return 0;
        
        $value = strtolower(trim($value));
        switch ($value) {
            case 'sold': return 1;
            case 'on hold': return 2;
            default: return 0; // Available
        }
    }

    private function parseDate($date)
    {
        if (empty($date)) return null;
        
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Invalid date format: {$date}");
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'stock_number' => 'required|string',
            'carat_weight' => 'required|numeric|min:0.01',
            'shape' => 'required|string',
            'color' => 'required|string',
            'clarity' => 'required|string',
            'cut' => 'required|string',
            'price' => 'required|numeric|min:0',
            'price_per_carat' => 'required|numeric|min:0',
        ];
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getUpdatedCount()
    {
        return $this->updatedCount;
    }

    public function customValidationMessages()
    {
        return [
            'stock_number.required' => 'Stock Number is required',
            'carat_weight.required' => 'Carat Weight is required',
            'shape.required' => 'Shape is required',
            'color.required' => 'Color is required',
            'clarity.required' => 'Clarity is required',
            'cut.required' => 'Cut is required',
            'price.required' => 'Price is required',
            'price_per_carat.required' => 'Price Per Carat is required',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    
}
