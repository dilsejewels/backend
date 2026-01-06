<?php

namespace App\Exports;

use App\Models\DiamondMaster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class DiamondMasterExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $diamonds;

    public function __construct($diamonds = null)
    {
        if ($diamonds === null) {
            // Load ALL diamonds without relationships first to check
            $this->diamonds = DiamondMaster::limit(1000)->get();
            Log::info("Exporting {$this->diamonds->count()} diamonds");
        } else {
            $this->diamonds = $diamonds;
        }
    }

    public function collection()
    {
        return $this->diamonds;
    }

    public function headings(): array
    {
        return [
            'Diamond ID',
            'Diamond Type',
            'Quantity',
            'Vendor ID',
            'Vendor Stock Number',
            'Stock Number',
            'Shape ID',
            'Carat Weight',
            'Color ID',
            'Clarity ID',
            'Cut ID',
            'Polish ID',
            'Symmetry ID',
            'Fluorescence ID',
            'Price',
            'MSRP Price',
            'Price Per Carat',
            'Certificate Company ID',
            'Certificate Number',
            'Certificate Date',
            'Measurements',
            'Measurement L',
            'Measurement W',
            'Measurement H',
            'Depth',
            'Table',
            'Vendor RAP Disc',
            'Is Superdeal',
            'Availability',
            'Status',
            'Date Added',
        ];
    }

    public function map($diamond): array
    {
        // Use direct column values, not relationships
        return [
            $diamond->diamondid,
            $diamond->diamond_type,
            $diamond->quantity,
            $diamond->vendor_id,
            $diamond->vendor_stock_number,
            $diamond->stock_number,
            $diamond->shape,
            $diamond->carat_weight,
            $diamond->color,
            $diamond->clarity,
            $diamond->cut,
            $diamond->polish,
            $diamond->symmetry,
            $diamond->fluorescence,
            $diamond->price,
            $diamond->msrp_price,
            $diamond->price_per_carat,
            $diamond->certificate_company,
            $diamond->certificate_number,
            $diamond->certificate_date,
            $diamond->measurements,
            $diamond->measurement_l,
            $diamond->measurement_w,
            $diamond->measurement_h,
            $diamond->depth,
            $diamond->table_diamond,
            $diamond->vendor_rap_disc,
            $diamond->is_superdeal,
            $diamond->availability,
            $diamond->status,
            $diamond->date_added,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}