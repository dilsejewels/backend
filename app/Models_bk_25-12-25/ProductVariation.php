<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductVariation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'product_id', 
        'carat',  
        'price', 
        'regular_price',
        'sku', 
        'images',
        'video',
        'master_sku', 
        'stock', 
        'weight', 
        'shape_id', 
        'diamond_weight',
        'diamond_quality_id', 
        'category_id', 
        'metal_color_id', 
        'vendor_id',
        'parent_category_id',
        'is_best_selling',
    ];
    
    protected $casts = [
        'images' => 'array',
    ];

    public function getCurrentMetalPrice()
    {
        if (!$this->metalColor) {
            return null;
        }

        $metalType = $this->metalColor->dmt_name;
        $metalQuality = $this->metalColor->metal_quality ?? '22K';

        return MetalPrice::where('metal_type', $metalType)
            ->where('metal_quality', $metalQuality)
            ->latest('date')
            ->first();
    }

    public function calculatePriceBasedOnMetal($latestMetalPrice = null)
    {
        if (!$latestMetalPrice) {
            $latestMetalPrice = $this->getCurrentMetalPrice();
        }

        if (!$latestMetalPrice || !$this->weight) {
            return null;
        }

        $newBasePrice = $this->weight * $latestMetalPrice->price_per_gram;

        if ($this->price > 0) {
            $ratio = $this->regular_price / $this->price;
            $newRegularPrice = $newBasePrice * $ratio;
        } else {
            $newRegularPrice = $newBasePrice;
        }

        return [
            'price' => $newBasePrice,
            'regular_price' => $newRegularPrice
        ];
    }

    public function updatePriceFromMetal($latestMetalPrice = null)
    {
        $prices = $this->calculatePriceBasedOnMetal($latestMetalPrice);

        if ($prices) {
            $this->update([
                'price' => $prices['price'],
                'regular_price' => $prices['regular_price']
            ]);
            return true;
        }

        return false;
    }

    public function getImagesAttribute($value)
    {
        $images = is_array($value) ? $value : json_decode($value, true);

        // fallback if it's still not an array
        if (!is_array($images)) {
            return [];
        }

        return array_map(function ($image) {
            return asset('storage/variation_images/' . $image);
        }, $images);
    }
    
    // Accessor for video URL
    public function getVideoUrlAttribute()
    {
        if (!empty($this->video)) {
            return asset('storage/variation_videos/' . $this->video);
        }
        return null;
    }
    
    protected static function booted()
    {
        static::deleted(function ($variation) {
            // Delete associated images
            if ($variation->images) {
                foreach ($variation->images as $imagePath) {
                    $filename = basename($imagePath);
                    if (Storage::disk('public')->exists("variation_images/$filename")) {
                        Storage::disk('public')->delete("variation_images/$filename");
                    }
                }
            }
        });

        static::updating(function ($variation) {
        $originalVideo = $variation->getOriginal('video');
        $newVideo = $variation->video;
        
        // If video is being changed or removed, delete the old video
        if ($originalVideo && $originalVideo !== $newVideo) {
            $videoPath = "variation_videos/" . $originalVideo;
            if (Storage::disk('public')->exists($videoPath)) {
                Storage::disk('public')->delete($videoPath);
            }
        }
    });
    }

    public function getImageFilenames()
    {
        $images = $this->getRawOriginal('images');

        if (is_string($images)) {
            $decoded = json_decode($images, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $images = $decoded;
            } else {
                $images = [$images];
            }
        }

        $filenames = [];
        foreach ((array)$images as $path) {
            $filenames[] = basename($path);
        }
        
        return $filenames;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'products_id');
    }

    public function shape()
    {
        return $this->belongsTo(DiamondShape::class, 'shape_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function metalColor()
    {
        return $this->belongsTo(MetalType::class, 'metal_color_id', 'dmt_id');
    }

    public function diamondQualityGroup()
    {
        return $this->belongsTo(DiamondQualityGroup::class, 'diamond_quality_id', 'dqg_id');
    }
}