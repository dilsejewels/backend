<?php

namespace App\Models;

use App\Models\DiamondShape;
use App\Models\DiamondVendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiamondMaster extends Model
{
    use HasFactory;

    protected $table = 'diamond_master';
    protected $primaryKey = 'diamondid';
    public $timestamps = false;

    // Image URL Accessor
    public function getImageUrlAttribute()
    {
        if ($this->image_link) {
            return asset('storage/diamonds/images/' . $this->image_link);
        }
        return null;
    }

    // Video URL Accessor
    public function getVideoUrlAttribute()
    {
        if ($this->video_link) {
            return asset('storage/diamonds/videos/' . $this->video_link);
        }
        return null;
    }

    public function vendor()
    {
        return $this->belongsTo(DiamondVendor::class, 'vendor_id');
    }

    // CORRECTED RELATIONSHIPS:
    public function shape()
    {
        return $this->belongsTo(DiamondShape::class, 'shape', 'id');
    }

    public function color()
    {
        return $this->belongsTo(DiamondColor::class, 'color', 'id');
    }

    public function cut()
    {
        return $this->belongsTo(DiamondCut::class, 'cut', 'id');
    }

    public function clarity()
    {
        return $this->belongsTo(DiamondClarityMaster::class, 'clarity', 'id');
    }

    public function polish()
    {
        return $this->belongsTo(DiamondPolish::class, 'polish', 'id');
    }

    public function symmetry()
    {
        return $this->belongsTo(DiamondSymmetry::class, 'symmetry', 'id');
    }

    public function fluorescence()
    {
        return $this->belongsTo(DiamondFlourescence::class, 'fluorescence', 'id');
    }

    public function certificateCompany()
    {
        return $this->belongsTo(DiamondLab::class, 'certificate_company', 'dl_id');
    }
    // ADD THESE MISSING RELATIONSHIPS:
    public function culet()
    {
        return $this->belongsTo(DiamondCulet::class, 'culet', 'dc_id');
    }

    public function fancyColorIntensity()
    {
        return $this->belongsTo(DiamondFancyColorIntensity::class, 'fancy_color_intensity', 'fci_id');
    }

    public function fancyColorOvertone()
    {
        return $this->belongsTo(DiamondFancyColor::class, 'fancy_color_overtone', 'fco_id');
    }

    protected $fillable = [
        'diamond_type',
        'quantity',
        'vendor_id',
        'vendor_stock_number',
        'stock_number',
        'same_diamond_stock_number',
        'shape',
        'color',
        'clarity',
        'cut',
        'carat_weight',
        'delivery_days',
        'price_per_carat',
        'msrp_price',
        'vendor_price',
        'vendor_rap_disc',
        'vendor_amount',
        'price',
        'diamond_price1',
        'diamond_price2',
        'diamond_price3',
        'diamond_price4',
        'rap_percentage',
        'memo_price_per_carat',
        'memo_rap_disc',
        'memo_price',
        'certificate_company',
        'certificate_number',
        'certificate_name',
        'certificate_date',
        'is_fancy_color',
        'fancy_color',
        'fancy_color_id',
        'fancy_color_intensity',
        'fancy_color_overtone',
        'fancy_color_overtone2',
        'measurements',
        'measurement_h',
        'measurement_w',
        'measurement_l',
        'depth',
        'table_diamond',
        'crown_height',
        'crown_angle',
        'pavillion_depth',
        'pavillion_angle',
        'girdle',
        'girdle_thin',
        'girdle_thick',
        'girdle_condition',
        'cut_grade',
        'on_hand',
        'status',
        'milky',
        'black',
        'image_link',
        'cert_link',
        'video_link',
        'sort_order',
        'availability',
        'is_superdeal',
        'locationid',
        'sales_price',
        'key_to_symbol',
        'key_to_symbol_id',
        'eye_clean',
        'base_color',
        'shade',
        'is_hearts_arrows',
        'center_black',
        'is_new_diamond',
        'additional_field',
        'user_field1',
        'user_field2',
        'user_field3',
        'user_field4',
        'user_field5',
        'user_field6',
        'diamond_seo',
        'giacheck',
        'customer_group',
        'stone_offer',
        'is_offer_stone',
        'fancy_cut_grade',
        'polish',
        'symmetry',
        'fluorescence',
        'culet',
        'date_added',
        'added_by', 
        'date_updated',
        'updated_by'
    ];
}
