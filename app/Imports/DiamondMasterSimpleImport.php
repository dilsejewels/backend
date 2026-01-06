<?php

namespace App\Imports;

use App\Models\DiamondMaster;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DiamondMasterSimpleImport implements ToCollection, WithHeadingRow
{
    private $importedCount = 0;
    private $updatedCount = 0;
    private $errors = [];

    public function collection(Collection $rows)
    {
        Log::info('Starting import with ' . $rows->count() . ' rows');
        
        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if (empty($row['stock_number']) || empty($row['carat_weight'])) {
                    continue;
                }

                // Prepare data for validation
                $data = [
                    'stock_number' => $row['stock_number'] ?? null,
                    'carat_weight' => $row['carat_weight'] ?? 0,
                    'shape' => $row['shape'] ?? null,
                    'color' => $row['color'] ?? null,
                    'clarity' => $row['clarity'] ?? null,
                    'cut' => $row['cut'] ?? null,
                    'price' => $row['price'] ?? 0,
                    'price_per_carat' => $row['price_per_carat'] ?? 0,
                ];

                // Validate required fields
                $validator = Validator::make($data, [
                    'stock_number' => 'required|string',
                    'carat_weight' => 'required|numeric|min:0.01',
                    'shape' => 'required',
                    'color' => 'required',
                    'clarity' => 'required',
                    'cut' => 'required',
                    'price' => 'required|numeric|min:0',
                    'price_per_carat' => 'required|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = [
                        'row' => $index + 2, // +2 because of header and 0-index
                        'errors' => $validator->errors()->all(),
                        'data' => $data
                    ];
                    continue;
                }

                // Check if diamond exists
                $existingDiamond = DiamondMaster::where('stock_number', $row['stock_number'])->first();

                $diamondData = [
                    'diamond_type' => $this->parseDiamondType($row['diamond_type'] ?? '2'),
                    'quantity' => (int)($row['quantity'] ?? 1),
                    'vendor_id' => $this->parseVendorId($row['vendor_name'] ?? null),
                    'vendor_stock_number' => $row['vendor_stock_number'] ?? null,
                    'stock_number' => $row['stock_number'] ?? null,
                    'shape' => $this->parseShapeId($row['shape'] ?? null),
                    'carat_weight' => (float)$row['carat_weight'],
                    'color' => $this->parseColorId($row['color'] ?? null),
                    'clarity' => $this->parseClarityId($row['clarity'] ?? null),
                    'cut' => $this->parseCutId($row['cut'] ?? null),
                    'polish' => $this->parsePolishId($row['polish'] ?? null),
                    'symmetry' => $this->parseSymmetryId($row['symmetry'] ?? null),
                    'fluorescence' => $this->parseFluorescenceId($row['fluorescence'] ?? null),
                    'price' => (float)$row['price'],
                    'msrp_price' => (float)($row['msrp_price'] ?? 0),
                    'price_per_carat' => (float)$row['price_per_carat'],
                    'certificate_company' => $this->parseLabId($row['certificate_company'] ?? null),
                    'certificate_number' => $row['certificate_number'] ?? null,
                    'certificate_date' => $this->parseDate($row['certificate_date'] ?? null),
                    'measurements' => $row['measurements'] ?? null,
                    'measurement_l' => (float)($row['measurement_l'] ?? 0),
                    'measurement_w' => (float)($row['measurement_w'] ?? 0),
                    'measurement_h' => (float)($row['measurement_h'] ?? 0),
                    'depth' => (float)($row['depth'] ?? 0),
                    'table_diamond' => (float)($row['table'] ?? 0),
                    'vendor_rap_disc' => (float)($row['vendor_rap_disc'] ?? 0),
                    'is_superdeal' => $this->parseBoolean($row['is_superdeal'] ?? 'No'),
                    'availability' => $this->parseAvailability($row['availability'] ?? 'Available'),
                    'status' => $this->parseBoolean($row['status'] ?? 'Active'),
                    'date_added' => now(),
                    'date_updated' => now(),
                    'added_by' => auth()->id() ?? 1,
                    'updated_by' => auth()->id() ?? 1,
                ];

                if ($existingDiamond) {
                    $existingDiamond->update($diamondData);
                    $this->updatedCount++;
                    Log::info("Updated diamond: {$existingDiamond->diamondid}");
                } else {
                    DiamondMaster::create($diamondData);
                    $this->importedCount++;
                    Log::info("Created new diamond with stock: {$row['stock_number']}");
                }

            } catch (\Exception $e) {
                Log::error("Error on row {$index}: " . $e->getMessage());
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => [$e->getMessage()],
                    'data' => $row->toArray()
                ];
            }
        }
    }

    // Simple parsing methods
    private function parseDiamondType($value)
    {
        if (is_numeric($value)) return (int)$value;
        $value = strtolower(trim($value));
        return (strpos($value, 'lab') !== false) ? 2 : 1;
    }

    private function parseVendorId($vendorName)
    {
        if (empty($vendorName)) return null;
        
        // Try to find vendor by name
        $vendor = \App\Models\DiamondVendor::where('vendor_name', 'like', "%{$vendorName}%")->first();
        return $vendor ? $vendor->vendorid : null;
    }

    private function parseShapeId($shapeValue)
    {
        if (empty($shapeValue)) return null;
        
        if (is_numeric($shapeValue)) return (int)$shapeValue;
        
        $shape = \App\Models\DiamondShape::where('name', 'like', "%{$shapeValue}%")->first();
        return $shape ? $shape->id : null;
    }

    private function parseColorId($colorValue)
    {
        if (empty($colorValue)) return null;
        
        if (is_numeric($colorValue)) return (int)$colorValue;
        
        $color = \App\Models\DiamondColor::where('name', 'like', "%{$colorValue}%")->first();
        return $color ? $color->id : null;
    }

    private function parseClarityId($clarityValue)
    {
        if (empty($clarityValue)) return null;
        
        if (is_numeric($clarityValue)) return (int)$clarityValue;
        
        $clarity = \App\Models\DiamondClarityMaster::where('name', 'like', "%{$clarityValue}%")->first();
        return $clarity ? $clarity->id : null;
    }

    private function parseCutId($cutValue)
    {
        if (empty($cutValue)) return null;
        
        if (is_numeric($cutValue)) return (int)$cutValue;
        
        $cut = \App\Models\DiamondCut::where('name', 'like', "%{$cutValue}%")->first();
        return $cut ? $cut->id : null;
    }

    private function parsePolishId($polishValue)
    {
        if (empty($polishValue)) return null;
        
        if (is_numeric($polishValue)) return (int)$polishValue;
        
        $polish = \App\Models\DiamondPolish::where('name', 'like', "%{$polishValue}%")->first();
        return $polish ? $polish->id : null;
    }

    private function parseSymmetryId($symmetryValue)
    {
        if (empty($symmetryValue)) return null;
        
        if (is_numeric($symmetryValue)) return (int)$symmetryValue;
        
        $symmetry = \App\Models\DiamondSymmetry::where('name', 'like', "%{$symmetryValue}%")->first();
        return $symmetry ? $symmetry->id : null;
    }

    private function parseFluorescenceId($fluorescenceValue)
    {
        if (empty($fluorescenceValue)) return null;
        
        if (is_numeric($fluorescenceValue)) return (int)$fluorescenceValue;
        
        $fluorescence = \App\Models\DiamondFlourescence::where('name', 'like', "%{$fluorescenceValue}%")->first();
        return $fluorescence ? $fluorescence->id : null;
    }

    private function parseLabId($labValue)
    {
        if (empty($labValue)) return null;
        
        if (is_numeric($labValue)) return (int)$labValue;
        
        $lab = \App\Models\DiamondLab::where('dl_name', 'like', "%{$labValue}%")->first();
        return $lab ? $lab->dl_id : null;
    }

    private function parseBoolean($value)
    {
        if (is_numeric($value)) return (int)$value ? 1 : 0;
        
        $value = strtolower(trim($value));
        return in_array($value, ['yes', 'true', 'y', '1', 'active']) ? 1 : 0;
    }

    private function parseAvailability($value)
    {
        if (is_numeric($value)) return (int)$value;
        
        $value = strtolower(trim($value));
        if ($value === 'sold') return 1;
        if ($value === 'on hold') return 2;
        return 0;
    }

    private function parseDate($date)
    {
        if (empty($date)) return null;
        
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getUpdatedCount()
    {
        return $this->updatedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}