<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'products_id';
    public $timestamps = true;

    protected $fillable = [
        'products_id',
        'products_name',
        'products_description',
        'products_short_description',
        'gender',
        'bond',
        'available',
        'products_quantity',
        'products_model',
        'products_sku',
        'master_sku',
        'products_price',
        'products_price1',
        'products_price2',
        'products_price3',
        'products_price4',
        'products_weight',
        'products_status',
        'engraving_status',
        'products_slug',
        'catelog_no',
        'vendor_id',
        'vendor_stock_no',
        'vendor_price',
        'categories_id',
        'parent_category_id',
        'psc_id',
        'product_collection_id',
        'product_style_group_id',
        'country_of_origin',
        'products_tax_class_id',
        'products_tax',
        'is_bestseller',
        'is_featured',
        'ready_to_ship',
        'is_collection',
        'is_new',
        'is_superdeals',
        'diamond_weight_group_id',
        'diamond_quality_id',
        'diamond_clarity_id',
        'diamond_color_id',
        'diamond_cut_id',
        'diamond_pics',
        'side_diamond_quality_id',
        'side_diamond_breakdown',
        'semi_mount_ct_wt',
        'total_carat_weight',
        'semi_mount_price',
        'center_stone_price',
        'center_stone_weight',
        'center_stone_type_id',
        'stone_type_id',
        'metal_type_id',
        'metal_color_id',
        'metal_weight',
        'is_build_product',
        'shape_ids',
        'build_product_type',
        'is_matching_set',
        'product_keywords',
        'product_promotion',
        'certified_lab',
        'certificate_number',
        'products_related_items',
        'related_master_sku',
        'products_meta_title',
        'products_meta_description',
        'products_meta_keyword',
        'delivery_days',
        'default_size',
        'deleted',
        'sort_order',
        'added_by',
        'updated_by',
        'date_added',
        'date_updated',
        'shop_zone_id',
        'is_sale',
        'is_gift',
    ];

    const BUILD_PRODUCT_JEWELRY = '0';
    const BUILD_PRODUCT_BUILD = '1';
    const BUILD_PRODUCT_WEDDING = '2';
    const BUILD_PRODUCT_GIFTS = '3';
    const BUILD_PRODUCT_SALE = '4';

    // Add these constants for gender and bond
    const GENDER_MAN = '0';
    const GENDER_WOMAN = '1';

    const BOND_METAL = '0';
    const BOND_DIAMOND = '1';

    public static function getGenderOptions()
    {
        return [
            self::GENDER_MAN => 'Man',
            self::GENDER_WOMAN => 'Woman',
        ];
    }

    public static function getBondOptions()
    {
        return [
            self::BOND_METAL => 'Metal',
            self::BOND_DIAMOND => 'Diamond',
        ];
    }

    public static function getBuildProductOptions()
    {
        return [
            self::BUILD_PRODUCT_JEWELRY => 'Jewelry',
            self::BUILD_PRODUCT_BUILD => 'Engagement', 
            self::BUILD_PRODUCT_WEDDING => 'Wedding',
            self::BUILD_PRODUCT_GIFTS => 'Gifts',
            self::BUILD_PRODUCT_SALE => 'Sale',
        ];
    }

    public function isOnSale()
    {
        return $this->is_sale == true;
    }

    public function isGift()
    {
        return $this->is_gift == true;
    }


    public function isBuildProduct()
    {
        return $this->is_build_product === self::BUILD_PRODUCT_BUILD;
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id', 'products_id');
    }

    public function metalType()
    {
        return $this->hasOne(ProductsToMetalType::class, 'sptmt_products_id', 'products_id');
    }

    public function category()
    {
        return $this->hasOne(ProductToCategory::class, 'products_id', 'products_id');
    }

    public function option()
    {
        return $this->hasOne(ProductToOption::class, 'products_id', 'products_id');
    }

    public function shape()
    {
        return $this->hasOne(ProductToShape::class, 'products_id', 'products_id');
    }

    public function stoneType()
    {
        return $this->hasOne(ProductToStoneType::class, 'sptst_products_id', 'products_id');
    }

    public function styleCategory()
    {
        return $this->hasOne(ProductToStyleCategory::class, 'sptsc_products_id', 'products_id');
    }

    public function styleGroup()
    {
        return $this->hasOne(ProductToStyleGroup::class, 'sptsg_products_id', 'products_id');
    }

    public function shopZoneGeoZone()
    {
        return $this->hasOne(ShopZonesToGeoZone::class, 'products_id', 'products_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'products_id', 'products_id');
    }

    public function productcategory()
    {
        return $this->belongsTo(Category::class, 'categories_id', 'category_id');
    }

    public function collection()
    {
        return $this->belongsTo(ProductCollection::class, 'product_collection_id');
    }

    public function diamondCut()
    {
        return $this->belongsTo(ProductsCutMaster::class, 'diamond_cut_id', 'id');
    }

    public function productClarity()
    {
        return $this->belongsTo(ProductClarityMaster::class, 'diamond_clarity_id', 'id');
    }

    public function stone()
    {
        return $this->belongsTo(ProductStoneType::class, 'stone_type_id', 'pst_id');
    }


}
