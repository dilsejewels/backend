<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use \App\Models\Category;
use App\Models\ProductVariation;
use App\Models\ProductStyleCategory;
use App\Models\ProductCollection;
use App\Models\MetalType;
use App\Models\DiamondShape;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{

    public function engagementData(Request $request, $slug = null)
    {
        $filters = [
            'price' => $request->input('price'),
            'style' => $request->input('style'),
            'shape' => $request->input('shape'),
            'ready_to_ship' => $request->input('ready_to_ship'),
            'sort' => $request->input('sort'),
            'metal_color_id' => $request->input('metal_color_id'),
        ];

        // Force build products
        $filters['is_build_product'] = 1;

        if (!empty($filters['price'])) {
            $filters['price'] = str_replace(['\u2013', ' '], ['-', ''], $filters['price']);
        }

        $perPage = (int) $request->input('perPage', 20);
        $page = (int) $request->input('page', 1);

        // -------- Banner assets ----------
        $bannerImage = null;
        $bannerVideo = null;

        // -------- Metal types ----------
        $metalTypes = MetalType::all()->sort(function ($a, $b) {
            $aVal = is_numeric(substr($a->dmt_tooltip, 0, 2)) ? (int) filter_var($a->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            $bVal = is_numeric(substr($b->dmt_tooltip, 0, 2)) ? (int) filter_var($b->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            return $aVal <=> $bVal;
        })->values();

        // -------- Base query from variations ----------
        $variationQuery = ProductVariation::query();

        // Price filter
        if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
            [$min, $max] = explode('-', $filters['price']);
            $variationQuery->whereBetween('price', [(int) $min, (int) $max]);
        }

        // Metal color filter
        if (!empty($filters['metal_color_id'])) {
            $variationQuery->where('metal_color_id', $filters['metal_color_id']);
        }

        // Shape filter
        if (!empty($filters['shape'])) {
            $variationQuery->where('shape_id', $filters['shape']);
        }

        // Product relation filters
        $variationQuery->whereHas('product', function ($query) use ($filters) {
            if (!empty($filters['style']))
                $query->where('psc_id', $filters['style']);
            if (!empty($filters['ready_to_ship']) && $filters['ready_to_ship'] === 'true') {
                $query->where('ready_to_ship', 1);
            }
            // Only build products
            $query->where('is_build_product', 1);
        });

        $filteredProductIds = $variationQuery->pluck('product_id')->unique();


        if ($filteredProductIds->isEmpty()) {
            return response()->json([
                'banner_video' => $bannerVideo,
                'banner_image' => $bannerImage,
                'metal_types' => $metalTypes,
                'data' => [],
                'totalProducts' => 0,
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalPages' => 0,
            ]);
        }

        // -------- Sorting ----------
        $sort = $filters['sort'];
        $isDateSort = in_array($sort, ['date_asc', 'date_desc']);
        $sortDirection = in_array($sort, ['price_desc', 'date_desc']) ? 'desc' : 'asc';
        $priceAggFunc = $sort === 'price_desc' ? 'MAX' : 'MIN';

        if ($isDateSort) {
            $sortedProductIds = Product::whereIn('products_id', $filteredProductIds)
                ->orderBy('created_at', $sortDirection)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('products_id');
        } else {
            $subQuery = ProductVariation::select('product_id', DB::raw("$priceAggFunc(price) as target_price"))
                ->whereIn('product_id', $filteredProductIds)
                ->groupBy('product_id');

            $sortedProductIds = ProductVariation::joinSub($subQuery, 'sorted_prices', function ($join) {
                $join->on('product_variations.product_id', '=', 'sorted_prices.product_id')
                    ->on('product_variations.price', '=', 'sorted_prices.target_price');
            })
                ->select('product_variations.product_id', 'sorted_prices.target_price')
                ->orderBy('sorted_prices.target_price', $sortDirection)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('product_id');
        }

        // -------- Fetch products ----------
        $products = Product::with([
            'productcategory' => function ($q) {
                $q->select('category_id', 'category_name', 'parent_id')->with('parent:category_id,category_name');
            },
            'variations.metalColor'
        ])
            ->whereIn('products_id', $sortedProductIds)
            ->orderByRaw('FIELD(products_id, ' . implode(',', $sortedProductIds->toArray()) . ')')
            ->get();

        $validProducts = [];

        foreach ($products as $product) {
            $variations = $product->variations;

            if (!empty($filters['metal_color_id'])) {
                $hasMatchingMetal = $variations->contains('metal_color_id', $filters['metal_color_id']);
                if (!$hasMatchingMetal)
                    continue;
            }

            if (!empty($filters['shape'])) {
                $variations = $variations->where('shape_id', $filters['shape']);
            }

            if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
                [$min, $max] = explode('-', $filters['price']);
                $variations = $variations->filter(fn($v) => $v->price >= $min && $v->price <= $max);
            }

            if ($variations->isEmpty())
                continue;

            $category = $product->productcategory;
            $parent = $category?->parent;

            // Build-type format: metal_color_id → shape_id → [variations...]
            $groupedByMetal = collect();

            foreach ($variations as $variation) {
                $metalId = $variation->metal_color_id;
                $shapeId = $variation->shape_id;

                $entry = [
                    'id' => $variation->id,
                    'product_id' => $variation->product_id,
                    'carat' => $variation->carat,
                    'price' => $variation->price,
                    'original_price' => $variation->regular_price,
                    'sku' => $variation->sku,
                    'shape_id' => $shapeId,
                    'metal_color_id' => $metalId,
                    'metal_color' => $variation->metalColor ? [
                        'id' => $variation->metalColor->dmt_id,
                        'name' => $variation->metalColor->dmt_name,
                        'quality' => $variation->metalColor->dmt_tooltip,
                        'hex' => $variation->metalColor->color_code ?? null,
                    ] : null,
                    'weight' => $variation->weight,
                    'images' => $variation->images,
                    'category' => $category ? [
                        'id' => $category->category_id,
                        'name' => $category->category_name,
                        'parent' => $parent ? [
                            'id' => $parent->category_id,
                            'name' => $parent->category_name
                        ] : null
                    ] : null,
                ];

                $groupedByMetal = $groupedByMetal->put(
                    $metalId,
                    $groupedByMetal->get($metalId, collect())->put(
                        $shapeId,
                        $groupedByMetal->get($metalId, collect())->get($shapeId, collect())->push($entry)
                    )
                );
            }

            if ($groupedByMetal->isEmpty())
                continue;

            $groupedByMetal = collect($groupedByMetal)->sortBy(function ($group) {
                // group = collection of shapes, so take first shape's first entry
                $first = $group->first()?->first();
                $quality = $first['metal_color']['quality'] ?? null;
                return is_numeric($quality) ? (int) $quality : PHP_INT_MAX . '_' . $quality;
            });

            $validProducts[] = [
                'id' => $product->products_id,
                'product' => [
                    'id' => $product->products_id,
                    'name' => $product->products_name,
                    'master_sku' => $product->master_sku,
                    'description' => $product->products_description,
                    'ready_to_ship' => $product->ready_to_ship,
                    'categories_id' => $product->categories_id,
                    'parent_category_id' => $product->parent_category_id,
                    'psc_id' => $product->psc_id,
                    'is_build' => $product->is_build_product,
                    'product_collection_id' => $product->product_collection_id,
                ],
                'category' => $category ? [
                    'id' => $category->category_id,
                    'name' => $category->category_name,
                    'parent' => $parent ? [
                        'id' => $parent->category_id,
                        'name' => $parent->category_name
                    ] : null
                ] : null,
                'metal_variations' => $groupedByMetal,
            ];
        }

        return response()->json([
            'banner_video' => $bannerVideo,
            'banner_image' => $bannerImage,
            'metal_types' => $metalTypes,
            'data' => $validProducts,
            'totalProducts' => $filteredProductIds->count(),
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($filteredProductIds->count() / $perPage),
        ]);
    }

    public function showBuildProductById ($id)
    {
        $product = Product::with([
            'productcategory' => function ($query) {
                $query->select('category_id', 'category_name', 'parent_id')
                    ->with('parent:category_id,category_name');
            },
            'variations.metalColor' => function ($query) {
                $query->select('dmt_id', 'dmt_name', 'dmt_tooltip', 'color_code');
            },
            'variations.shape' => function ($query) {
                $query->select('id', 'name', 'image');
            },
            'variations.diamondQualityGroup:dqg_id,dqg_name',
            'diamondCut:id,name',
            'stone:pst_id,pst_name',
            'productClarity:id,name',
        ])->where('products_id', $id)->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $isBuild = (int)($product->is_build_product ?? $product->is_build ?? 0);
        if ($isBuild !== 1) {
            return response()->json(['message' => 'Product is not a build type'], 400);
        }

        $category = $product->productcategory;
        $parent = $category?->parent;
        $variations = $product->variations;

        $qualityByMetal = [];
        foreach ($variations as $v) {
            $mid = (string)$v->metal_color_id;
            if (!isset($qualityByMetal[$mid])) {
                $qualityByMetal[$mid] = optional($v->metalColor)->dmt_tooltip;
            }
        }

        $format = function ($variation) use ($category, $parent) {
            return [
                'id' => $variation->id,
                'product_id' => $variation->product_id,
                'carat' => $variation->carat,
                'price' => $variation->price,
                'original_price' => $variation->regular_price,
                'sku' => $variation->sku,
                'shape_id' => $variation->shape_id,
                'metal_color_id' => $variation->metal_color_id,
                'diamond_quality_id' =>$variation->diamond_quality_id,
                'diamond_quality_name' => optional($variation->diamondQualityGroup)->dqg_name,
                'metal_color' => $variation->metalColor ? [
                    'id' => $variation->metalColor->dmt_id,
                    'name' => $variation->metalColor->dmt_name,
                    'quality' => $variation->metalColor->dmt_tooltip,
                    'hex' => $variation->metalColor->color_code ?? null,
                ] : null,
                'shape' => $variation->shape ? [
                    'id' => $variation->shape->id,
                    'name' => $variation->shape->name,
                    'image' => $variation->shape->image ? asset('storage/shapes/' . $variation->shape->image) : null,
                ] : null,
                'weight' => $variation->weight,
                'images' => $variation->images,
                'video' => $variation->video ? asset('storage/variation_videos/' . $variation->video): null,
                'category' => $category ? [
                    'id' => $category->category_id,
                    'name' => $category->category_name,
                    'parent' => $parent ? [
                        'id' => $parent->category_id,
                        'name' => $parent->category_name
                    ] : null
                ] : null,
            ];
        };

        $groupedByMetal = [];
        foreach ($variations as $variation) {
            $metalId = (string)$variation->metal_color_id;
            $shapeId = (string)($variation->shape_id ?? 0);
            $groupedByMetal[$metalId][$shapeId][] = $format($variation);
        }

        foreach ($groupedByMetal as $metalId => $shapes) {
            ksort($groupedByMetal[$metalId], SORT_NUMERIC);
        }

        $metalIds = array_keys($groupedByMetal);
        usort($metalIds, function ($a, $b) use ($qualityByMetal) {
            $qa = trim($qualityByMetal[$a] ?? '');
            $qb = trim($qualityByMetal[$b] ?? '');

            // Extract leading numbers from strings like "14k", "18k"
            preg_match('/\d+/', $qa, $matchesA);
            preg_match('/\d+/', $qb, $matchesB);

            $numA = $matchesA[0] ?? null;
            $numB = $matchesB[0] ?? null;

            $hasNumA = $numA !== null;
            $hasNumB = $numB !== null;

            if ($hasNumA && $hasNumB) {
                return (int)$numA <=> (int)$numB;
            }

            if ($hasNumA) return -1; // numeric first
            if ($hasNumB) return 1;

            // both non-numeric, compare as strings
            return strcasecmp($qa, $qb);
        });


        $sorted = [];
        foreach ($metalIds as $idKey) {
            $sorted[$idKey] = $groupedByMetal[$idKey];
        }

        return response()->json([
            'id' => $product->products_id,
            'product' => [
                'id' => $product->products_id,
                'name' => $product->products_name,
                'master_sku' => $product->master_sku,
                'description' => $product->products_description,
                'ready_to_ship' => $product->ready_to_ship,
                'delivery_days' => $product->delivery_days,
                'product_clarity' => $product->productClarity ? $product->productClarity->name : null,
                'product_modal' => $product->product_model,
                'stone_type' => $product->stone ? $product->stone->pst_name : null,
                'cut' => $product->diamondCut ? $product->diamondCut->name : null,
                'is_build' => $isBuild,
            ],
            'category' => $category ? [
                'id' => $category->category_id,
                'name' => $category->category_name,
                'parent' => $parent ? [
                    'id' => $parent->category_id,
                    'name' => $parent->category_name,
                ] : null
            ] : null,
            'metal_variations' => $sorted,
        ]);
    }

    public function jewelryData(Request $request, $slug = null)
    {
        $filters = [
            'category' => $request->input('category'),
            'subcategory' => $request->input('subcategory'),
            'menucollection' => $request->input('menucollection'),
            'price' => $request->input('price'),
            'style' => $request->input('style'),
            'collection' => $request->input('collection'),
            'ready_to_ship' => $request->input('ready_to_ship'),
            'sort' => $request->input('sort'),
            'metal_color_id' => $request->input('metal_color_id'),
        ];

        // Always set is_build_product to 0
        $filters['is_build_product'] = 0;

        if (!empty($filters['price'])) {
            $filters['price'] = str_replace(['\u2013', ' '], ['-', ''], $filters['price']);
        }

        $perPage = (int) $request->input('perPage', 20);
        $page = (int) $request->input('page', 1);

        // Set Banner Image Based on Category / Subcategory / Collection
        $bannerImage = null;
        $bannerVideo = null;
        if (!empty($filters['subcategory'])) {
            $subcategory = Category::select('category_header_banner')->find($filters['subcategory']);
            $bannerImage = $subcategory?->category_header_banner ?? null;
        } elseif (!empty($filters['category'])) {
            $category = Category::select('category_header_banner')->find($filters['category']);
            $bannerImage = $category?->category_header_banner ?? null;
        } elseif (!empty($filters['menucollection'])) {
            $collection = ProductCollection::select('banner_image', 'banner_video')->find($filters['menucollection']);
            if (!empty($collection->banner_video)) {
                $bannerVideo = $collection->banner_video;
            } else {
                $bannerImage = $collection->banner_image ?? null;
            }
        }

        // Fetch Style Data
        $styleData = !empty($filters['subcategory']) || !empty($filters['category'])
            ? ProductStyleCategory::where('psc_category_id', $filters['subcategory'] ?? $filters['category'])->get()
            : ProductStyleCategory::join(DB::raw('(
                SELECT MIN(psc_id) as psc_id FROM products_style_category
                WHERE parent_category_id IS NULL GROUP BY psc_category_id
            ) as grouped_styles'), 'products_style_category.psc_id', '=', 'grouped_styles.psc_id')
                ->select('products_style_category.*')->get();

        // Fetch Collection Data
        if (!empty($filters['subcategory']) && !empty($filters['category'])) {
            $collectionData = ProductCollection::where('product_category_ids', $filters['subcategory'])->get();
        } elseif (!empty($filters['category'])) {
            $collectionData = ProductCollection::where('parent_category_ids', $filters['category'])->get();
        } else {
            $collectionData = ProductCollection::join(DB::raw('(
                    SELECT MIN(id) as id
                    FROM product_collections
                    WHERE parent_category_ids IS NOT NULL AND product_category_ids IS NULL
                    GROUP BY parent_category_ids
                ) as grouped_collection'), 'product_collections.id', '=', 'grouped_collection.id')
                ->select('product_collections.*')
                ->get();
        }

        // Sort Metal Types
        $metalTypes = MetalType::all()->sort(function ($a, $b) {
            $aVal = is_numeric(substr($a->dmt_tooltip, 0, 2)) ? (int) filter_var($a->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            $bVal = is_numeric(substr($b->dmt_tooltip, 0, 2)) ? (int) filter_var($b->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            return $aVal <=> $bVal;
        })->values();

        // START FROM VARIATION TABLE
        $variationQuery = ProductVariation::query();

        // Filter by price
        if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
            [$min, $max] = explode('-', $filters['price']);
            $variationQuery->whereBetween('price', [(int) $min, (int) $max]);
        }

        // Filter by metal color
        if (!empty($filters['metal_color_id'])) {
            $variationQuery->where('metal_color_id', $filters['metal_color_id']);
        }

        // Join product table for category-based filtering
        $variationQuery->whereHas('product', function ($query) use ($filters) {
            if (!empty($filters['subcategory'])) {
                $query->where('categories_id', $filters['subcategory']);
            } elseif (!empty($filters['category'])) {
                $query->where('categories_id', $filters['category']);
            }

            if (!empty($filters['menucollection'])) {
                $query->where('product_collection_id', $filters['menucollection']);
            }
            if (!empty($filters['style'])) $query->where('psc_id', $filters['style']);
            if (!empty($filters['collection'])) $query->where('product_collection_id', $filters['collection']);
            if (!empty($filters['ready_to_ship']) && $filters['ready_to_ship'] === 'true') $query->where('ready_to_ship', 1);

            // Always enforce is_build_product = 0
            $query->where('is_build_product', 0);
        });

        // Get product IDs from filtered variations
        $filteredProductIds = $variationQuery->pluck('product_id')->unique();

        if ($filteredProductIds->isEmpty()) {
            return response()->json([
                'banner_video' => $bannerVideo,
                'banner_image' => $bannerImage,
                'style_data' => $styleData,
                'collection_data' => $collectionData,
                'metal_types' => $metalTypes,
                'data' => [],
                'totalProducts' => 0,
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalPages' => 0,
            ]);
        }

        // Sorting setup
        $sort = $filters['sort'];
        $isDateSort = in_array($sort, ['date_asc', 'date_desc']);
        $sortDirection = in_array($sort, ['price_desc', 'date_desc']) ? 'desc' : 'asc';
        $priceAggFunc = $sort === 'price_desc' ? 'MAX' : 'MIN';

        if ($isDateSort) {
            $sortedProductIds = Product::whereIn('products_id', $filteredProductIds)
                ->orderBy('created_at', $sortDirection)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('products_id');
        } else {
            $subQuery = ProductVariation::select('product_id', DB::raw("$priceAggFunc(price) as target_price"))
                ->whereIn('product_id', $filteredProductIds)
                ->groupBy('product_id');

            $sortedProductIds = ProductVariation::joinSub($subQuery, 'sorted_prices', function ($join) {
                    $join->on('product_variations.product_id', '=', 'sorted_prices.product_id')
                        ->on('product_variations.price', '=', 'sorted_prices.target_price');
                })
                ->select('product_variations.product_id', 'sorted_prices.target_price')
                ->orderBy('sorted_prices.target_price', $sortDirection)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('product_id');
        }

        // Fetch Products with filtered variations only
        $products = Product::with([
            'productcategory' => function ($q) {
                $q->select('category_id', 'category_name', 'parent_id')->with('parent:category_id,category_name');
            }, 
            'variations.metalColor'])
                ->whereIn('products_id', $sortedProductIds)
                ->orderByRaw('FIELD(products_id, ' . implode(',', $sortedProductIds->toArray()) . ')')
                ->get();

        $validProducts = [];

        foreach ($products as $product) {
            $variations = $product->variations;

            if (!empty($filters['metal_color_id'])) {
                $hasMatchingMetal = $variations->contains('metal_color_id', $filters['metal_color_id']);
                if (!$hasMatchingMetal) continue;
            }

            if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
                [$min, $max] = explode('-', $filters['price']);
                $variations = $variations->filter(fn($v) => $v->price >= $min && $v->price <= $max);
            }

            if ($variations->isEmpty()) continue;

            $category = $product->productcategory;
            $parent = $category?->parent;

            // Only default format now
            $groupedByMetal = $variations->groupBy('metal_color_id')->map(function ($group) use ($category, $parent) {
                return $group->map(function ($variation) use ($category, $parent) {
                    return [
                        'id' => $variation->id,
                        'product_id' => $variation->product_id,
                        'carat' => $variation->carat,
                        'price' => $variation->price,
                        'original_price' => $variation->regular_price,
                        'sku' => $variation->sku,
                        'shape_id' => $variation->shape_id,
                        'metal_color_id' => $variation->metal_color_id,
                        'metal_color' => $variation->metalColor ? [
                            'id' => $variation->metalColor->dmt_id,
                            'name' => $variation->metalColor->dmt_name,
                            'quality' => $variation->metalColor->dmt_tooltip,
                            'hex' => $variation->metalColor->color_code ?? null,
                        ] : null,
                        'weight' => $variation->weight,
                        'images' => $variation->images,
                        'category' => $category ? [
                            'id' => $category->category_id,
                            'name' => $category->category_name,
                            'parent' => $parent ? [
                                'id' => $parent->category_id,
                                'name' => $parent->category_name
                            ] : null
                        ] : null,
                    ];
                });
            })->filter(fn($group) => $group->isNotEmpty());

            $groupedByMetal = collect($groupedByMetal)->sortBy(function ($group) {
                $quality = $group->first()['metal_color']['quality'] ?? null;
                return is_numeric($quality) ? (int) $quality : PHP_INT_MAX . '_' . $quality;
            });

            $validProducts[] = [
                'id' => $product->products_id,
                'product' => [
                    'id' => $product->products_id,
                    'name' => $product->products_name,
                    'master_sku' => $product->master_sku,
                    'description' => $product->products_description,
                    'ready_to_ship' => $product->ready_to_ship,
                    'categories_id' => $product->categories_id,
                    'parent_category_id' => $product->parent_category_id,
                    'psc_id' => $product->psc_id,
                    'is_build' => $product->is_build_product,
                    'product_collection_id'=> $product->product_collection_id,
                ],
                'category' => $category ? [
                    'id' => $category->category_id,
                    'name' => $category->category_name,
                    'parent' => $parent ? [
                        'id' => $parent->category_id,
                        'name' => $parent->category_name
                    ] : null
                ] : null,
                'metal_variations' => $groupedByMetal,
            ];
        }

        return response()->json([
            'banner_video'=>$bannerVideo,
            'banner_image' => $bannerImage,
            'style_data' => $styleData,
            'collection_data' => $collectionData,
            'metal_types' => $metalTypes,
            'data' => $validProducts,
            'totalProducts' => $filteredProductIds->count(),
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($filteredProductIds->count() / $perPage),
        ]);
    }

    public function weddingData(Request $request, $slug = null)
    {
        $filters = [
            'price' => $request->input('price'),
            'style' => $request->input('style'),
            'collection' => $request->input('collection'),
            'ready_to_ship' => $request->input('ready_to_ship'),
            'sort' => $request->input('sort'),
            'metal_color_id' => $request->input('metal_color_id'),
            'category' => $request->input('category'),
            'subcategory' => $request->input('subcategory'),
            'menucollection' => $request->input('menucollection'),
            'gender' => null,
            'bond' => null,
        ];

        if ($slug) {
            switch ($slug) {
                case 'mens-wedding-rings':
                    $filters['gender'] = '0'; 
                    break;
                case 'mens-metal-wedding-bands':
                    $filters['gender'] = '0';
                    $filters['bond'] = '0';
                    break;
                case 'mens-diamond-wedding-bands':
                    $filters['gender'] = '0';
                    $filters['bond'] = '1';
                    break;
                case 'womens-wedding-rings':
                    $filters['gender'] = '1'; // only women’s rings
                    break;
                case 'womens-metal-wedding-rings':
                    $filters['gender'] = '1';
                    $filters['bond'] = '0';
                    break;
                case 'womens-diamond-wedding-rings':
                    $filters['gender'] = '1';
                    $filters['bond'] = '1';
                    break;
                case 'womens-anniversary-rings':
                    $filters['gender'] = '1';

                    // Lookup category id dynamically
                    $category = DB::table('categories')
                        ->whereRaw('LOWER(category_name) = ?', ['anniversary'])
                        ->first();
                    
                    if ($category) {
                        $filters['category'] = $category->category_id;
                    }
                    break;

                case 'womens-eternity-rings':
                    $filters['gender'] = '1';
                    $category = DB::table('categories')
                        ->whereRaw('LOWER(category_name) = ?', ['eternity'])
                        ->first();
                    
                    if ($category) {
                        $filters['category'] = $category->category_id;
                    }
                    break;

            }
        }

        // Always enforce wedding products
        $filters['is_build_product'] = 2;

        if (!empty($filters['price'])) {
            $filters['price'] = str_replace(['\u2013', ' '], ['-', ''], $filters['price']);
        }

        $perPage = (int) $request->input('perPage', 20);
        $page = (int) $request->input('page', 1);

        // -----------------------------
        // Style Data
        // -----------------------------
        /* $styleData = !empty($filters['subcategory']) || !empty($filters['category'])
            ? ProductStyleCategory::where('psc_category_id', $filters['subcategory'] ?? $filters['category'])->get()
            : ProductStyleCategory::join(DB::raw('(
                SELECT MIN(psc_id) as psc_id FROM products_style_category
                WHERE parent_category_id IS NULL GROUP BY psc_category_id
            ) as grouped_styles'), 'products_style_category.psc_id', '=', 'grouped_styles.psc_id')
                ->select('products_style_category.*')->get();
        */
        // -----------------------------
        // Collection Data
        // -----------------------------
       /*  if (!empty($filters['subcategory']) && !empty($filters['category'])) {
            $collectionData = ProductCollection::where('product_category_id', $filters['subcategory'])->get();
        } elseif (!empty($filters['category'])) {
            $collectionData = ProductCollection::where('parent_category_id', $filters['category'])->get();
        } else {
            $collectionData = ProductCollection::join(DB::raw('(
                    SELECT MIN(id) as id
                    FROM product_collections
                    WHERE parent_category_id IS NOT NULL AND product_category_id IS NULL
                    GROUP BY parent_category_id
                ) as grouped_collection'), 'product_collections.id', '=', 'grouped_collection.id')
                ->select('product_collections.*')
                ->get();
        } */

        // -----------------------------
        // Metal Types sorted
        // -----------------------------
        $metalTypes = MetalType::all()->sort(function ($a, $b) {
            $aVal = is_numeric(substr($a->dmt_tooltip, 0, 2)) ? (int) filter_var($a->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            $bVal = is_numeric(substr($b->dmt_tooltip, 0, 2)) ? (int) filter_var($b->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            return $aVal <=> $bVal;
        })->values();

        // -----------------------------
        // Product + Variation Query (Optimized)
        // -----------------------------
        $sort = $filters['sort'];
        $sortDirection = in_array($sort, ['price_desc', 'date_desc']) ? 'desc' : 'asc';
        $priceAggFunc = $sort === 'price_desc' ? 'MAX' : 'MIN';

        $productQuery = Product::query()
            ->select('products.products_id')
            ->join('product_variations', 'products.products_id', '=', 'product_variations.product_id')
            ->where('products.is_build_product', 2);


        // Apply gender filter if set
        if (!is_null($filters['gender'])) {
            $productQuery->where('products.gender', (string)$filters['gender']);
        }

        // Apply bond filter
        if (!is_null($filters['bond'])) {
            $productQuery->where('products.bond', $filters['bond']);
        }

        // ---- Variation Filters ----
        if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
            [$min, $max] = explode('-', $filters['price']);
            $productQuery->whereBetween('product_variations.price', [(int) $min, (int) $max]);
        }
        if (!empty($filters['metal_color_id'])) {
            $productQuery->where('product_variations.metal_color_id', $filters['metal_color_id']);
        }

        // ---- Product Filters ----
        if (!empty($filters['category'])) {
            $productQuery->where('products.categories_id', $filters['category']);
        }
        // if (!empty($filters['subcategory'])) {
        //     $productQuery->where('products.categories_id', $filters['subcategory']);
        // } elseif (!empty($filters['category'])) {
        //     $productQuery->where('products.categories_id', $filters['category']);
        // }
        if (!empty($filters['menucollection'])) {
            $productQuery->where('products.product_collection_id', $filters['menucollection']);
        }
        if (!empty($filters['style'])) {
            $productQuery->where('products.psc_id', $filters['style']);
        }
        if (!empty($filters['collection'])) {
            $productQuery->where('products.product_collection_id', $filters['collection']);
        }
        if (!empty($filters['ready_to_ship']) && $filters['ready_to_ship'] === 'true') {
            $productQuery->where('products.ready_to_ship', 1);
        }

        // ---- Sorting ----
        if ($sort === 'date_asc' || $sort === 'date_desc') {
            $productQuery->orderBy('products.created_at', $sortDirection);
            $totalProducts = (clone $productQuery)->distinct()->count('products.products_id');
            $productIds = $productQuery->distinct()
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('products.products_id');
        } else {
            $priceQuery = ProductVariation::select('product_id', DB::raw("$priceAggFunc(price) as sort_price"))
                ->whereIn('product_id', $productQuery->pluck('products.products_id'))
                ->groupBy('product_id');

            $totalProducts = $priceQuery->distinct('product_id')->count('product_id');

            $productIds = $priceQuery
                ->orderBy('sort_price', $sortDirection)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('product_id');
        }

        if ($productIds->isEmpty()) {
            return response()->json([
                // 'style_data' => $styleData,
                // 'collection_data' => $collectionData,
                'metal_types' => $metalTypes,
                'data' => [],
                'totalProducts' => 0,
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalPages' => 0,
            ]);
        }


        // -----------------------------
        // Fetch Products + Variations
        // -----------------------------
        $products = Product::with([
                'productcategory.parent',
                'variations.metalColor'
            ])
            ->whereIn('products_id', $productIds)
            ->orderByRaw('FIELD(products_id, ' . implode(',', $productIds->toArray()) . ')')
            ->get();

        $validProducts = [];
        foreach ($products as $product) {
            $variations = $product->variations;

            // Extra filtering inside eager-loaded variations
            if (!empty($filters['metal_color_id'])) {
                $hasMatchingMetal = $variations->contains('metal_color_id', $filters['metal_color_id']);
                if (!$hasMatchingMetal) continue;
            }

            if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
                [$min, $max] = explode('-', $filters['price']);
                $variations = $variations->filter(fn($v) => $v->price >= $min && $v->price <= $max);
            }
            if ($variations->isEmpty()) continue;

            $category = $product->productcategory;
            $parent = $category?->parent;

            // Group variations by metal color
            $groupedByMetal = $variations->groupBy('metal_color_id')->map(function ($group) use ($category, $parent) {
                return $group->map(function ($variation) use ($category, $parent) {
                    return [
                        'id' => $variation->id,
                        'product_id' => $variation->product_id,
                        'carat' => $variation->carat,
                        'price' => $variation->price,
                        'original_price' => $variation->regular_price,
                        'sku' => $variation->sku,
                        'metal_color_id' => $variation->metal_color_id,
                        'metal_color' => $variation->metalColor ? [
                            'id' => $variation->metalColor->dmt_id,
                            'name' => $variation->metalColor->dmt_name,
                            'quality' => $variation->metalColor->dmt_tooltip,
                            'hex' => $variation->metalColor->color_code ?? null,
                        ] : null,
                        'weight' => $variation->weight,
                        'images' => $variation->images,
                        'category' => $category ? [
                            'id' => $category->category_id,
                            'name' => $category->category_name,
                            'parent' => $parent ? [
                                'id' => $parent->category_id,
                                'name' => $parent->category_name
                            ] : null
                        ] : null,
                    ];
                });
            })->filter(fn($group) => $group->isNotEmpty());

            // Sort metals by quality
            $groupedByMetal = collect($groupedByMetal)->sortBy(function ($group) {
                $quality = $group->first()['metal_color']['quality'] ?? null;
                return is_numeric($quality) ? (int) $quality : PHP_INT_MAX . '_' . $quality;
            });

            $validProducts[] = [
                'id' => $product->products_id,
                'product' => [
                    'id' => $product->products_id,
                    'name' => $product->products_name,
                    'master_sku' => $product->master_sku,
                    'description' => $product->products_description,
                    'ready_to_ship' => $product->ready_to_ship,
                    'categories_id' => $product->categories_id,
                    'parent_category_id' => $product->parent_category_id,
                    'psc_id' => $product->psc_id,
                    'is_build' => $product->is_build_product,
                    'product_collection_id' => $product->product_collection_id,
                ],
                'category' => $category ? [
                    'id' => $category->category_id,
                    'name' => $category->category_name,
                    'parent' => $parent ? [
                        'id' => $parent->category_id,
                        'name' => $parent->category_name
                    ] : null
                ] : null,
                'metal_variations' => $groupedByMetal,
            ];
        }
        $totalProducts = count($validProducts);
        // -----------------------------
        // Final Response
        // -----------------------------
        return response()->json([
            'slug' => $slug,
            // 'style_data' => $styleData,
            // 'collection_data' => $collectionData,
            'metal_types' => $metalTypes,
            'data' => $validProducts,
            'totalProducts' => $totalProducts,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($totalProducts / $perPage),
        ]);
    }

    public function collectionData(Request $request, $slug = null)
    {
        /* -----------------------------------------------------------
            1) READ FILTERS
        ----------------------------------------------------------- */
        $filters = [
            'price'           => null,
            'style'           => $request->input('style'),
            'ready_to_ship'   => $request->input('ready_to_ship'),
            'sort'            => $request->input('sort'),
            'metal_color_id'  => $request->input('metal_color_id'),
            'category'        => $request->input('category'),
            'subcategory'     => $request->input('subcategory'),
            'menucollection'  => $request->input('menucollection'),
        ];

        $perPage = (int) $request->input('perPage', 20);
        $page    = (int) $request->input('page', 1);

        /* -----------------------------------------------------------
            2) HANDLE COLLECTION SLUG
        ----------------------------------------------------------- */
        if ($slug) {
            $slugMap = [
                'cassatt'                   => 'cassatt',
                'the-fulton-collection'     => 'flutton',
                'the-seraphine-collection'  => 'seraphine',
                'the-windsor-collection'    => 'windsor',
                'the-bond-collection'       => 'bond',
                'bouquet'                   => 'bouquet',
            ];

            if (isset($slugMap[$slug])) {

                $collectionName = strtolower($slugMap[$slug]);

                // Get all IDs where name = Flutton (case-insensitive)
                $collectionIds = ProductCollection::whereRaw("LOWER(name) = ?", [$collectionName])
                                ->pluck('id')
                                ->toArray();

                if (empty($collectionIds)) {
                    return $this->emptyResponse($perPage, $slug);
                }

                $filters['menucollection'] = $collectionIds;
            }

            if ($slug === 'ready-to-ship-diamond-jewelry-gifts') {
                $filters['ready_to_ship'] = 'true';
            }
        }

        /* -----------------------------------------------------------
            3) CLEAN PRICE
        ----------------------------------------------------------- */
        if ($request->has('price') && preg_match('/^(\d+)-(\d+)$/', $request->price)) {
            $filters['price'] = str_replace(' ', '', $request->price);
        }

        /* -----------------------------------------------------------
            4) STYLE DATA
        ----------------------------------------------------------- */
        if ($filters['subcategory'] || $filters['category']) {

            $styleData = ProductStyleCategory::where(
                "psc_category_id",
                $filters['subcategory'] ?? $filters['category']
            )->get();

        } else {

            $styleData = ProductStyleCategory::join(DB::raw("
                (SELECT MIN(psc_id) as psc_id 
                FROM products_style_category
                WHERE parent_category_id IS NULL
                GROUP BY psc_category_id
                ) grouped
            "),'products_style_category.psc_id','=','grouped.psc_id')
            ->select('products_style_category.*')
            ->get();
        }

        /* -----------------------------------------------------------
            5) METAL TYPES SORTED
        ----------------------------------------------------------- */
        $metalTypes = MetalType::all()
            ->sortBy(fn($m) => (int) filter_var($m->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT))
            ->values();

        /* -----------------------------------------------------------
            6) BASE PRODUCT QUERY
        ----------------------------------------------------------- */
        $productQuery = Product::select('products.products_id')
            ->join('product_variations', 'products.products_id', '=', 'product_variations.product_id');

        // CATEGORY
        if ($filters['category']) {
            $productQuery->where('products.categories_id', $filters['category']);
        }

        // STYLE
        if ($filters['style']) {
            $productQuery->where('products.psc_id', $filters['style']);
        }

        // COLLECTIONS
        if (!empty($filters['menucollection'])) {
            $productQuery->whereIn('products.product_collection_id', $filters['menucollection']);
        }

        // READY TO SHIP
        if ($filters['ready_to_ship'] === 'true') {
            $productQuery->where('products.ready_to_ship', 1);
        }

        // PRICE
        if ($filters['price']) {
            [$min, $max] = explode('-', $filters['price']);
            $productQuery->whereBetween('product_variations.price', [(int)$min, (int)$max]);
        }

        // METAL COLOR
        if ($filters['metal_color_id']) {
            $productQuery->where('product_variations.metal_color_id', $filters['metal_color_id']);
        }

        /* -----------------------------------------------------------
            7) SORTING
        ----------------------------------------------------------- */
        $sort = $filters['sort'];
        $direction = str_contains($sort, 'desc') ? 'desc' : 'asc';

        if (str_contains($sort, 'date')) {

            $productQuery->orderBy('products.created_at', $direction);

            $totalProducts = $productQuery->distinct()->count('products.products_id');

            $productIds = $productQuery->distinct()
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('products.products_id');

        } else {

            $priceAgg = ($sort === 'price_desc') ? 'MAX' : 'MIN';

            $mainProductIds = $productQuery->pluck('products.products_id');

            $priceQuery = ProductVariation::select(
                    'product_id',
                    DB::raw("$priceAgg(price) AS sort_price")
                )
                ->whereIn('product_id', $mainProductIds)
                ->groupBy('product_id');

            $totalProducts = $priceQuery->count();

            $productIds = $priceQuery->orderBy('sort_price', $direction)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('product_id');
        }

        if ($productIds->isEmpty()) {
            return $this->emptyResponse($perPage, $slug);
        }

        /* -----------------------------------------------------------
            8) FETCH FINAL PRODUCTS WITH VARIATIONS
        ----------------------------------------------------------- */
        $products = Product::with([
                'productcategory.parent',
                'variations.metalColor'
            ])
            ->whereIn('products_id', $productIds)
            ->orderByRaw('FIELD(products_id, ' . implode(',', $productIds->toArray()) . ')')
            ->get();

        $finalProducts = [];

        foreach ($products as $product) {

            $variations = $product->variations;

            // ---- Metal filter
            if (!empty($filters['metal_color_id'])) {
                $variations = $variations->where('metal_color_id', $filters['metal_color_id']);
            }

            // ---- Price filter
            if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
                [$min, $max] = explode('-', $filters['price']);
                $variations = $variations->filter(fn($v) => $v->price >= $min && $v->price <= $max);
            }

            if ($variations->isEmpty()) continue;

            $category = $product->productcategory;
            $parent   = $category?->parent;

            // ---- Group by metal
            $groupedVariations = $variations->groupBy('metal_color_id')->map(function ($group) {
                return $group->map(function ($v) {
                    return [
                        'id'             => $v->id,
                        'product_id'     => $v->product_id,
                        'carat'          => $v->carat,
                        'price'          => $v->price,
                        'original_price' => $v->regular_price,
                        'sku'            => $v->sku,
                        'metal_color_id' => $v->metal_color_id,
                        'metal_color'    => $v->metalColor ? [
                            'id'     => $v->metalColor->dmt_id,
                            'name'   => $v->metalColor->dmt_name,
                            'quality'=> $v->metalColor->dmt_tooltip,
                            'hex'    => $v->metalColor->color_code,
                        ] : null,
                        'weight'         => $v->weight,
                        'images'         => $v->images,
                    ];
                });
            })->filter(fn($g) => $g->isNotEmpty());

            // ---- Sort by metal quality
            $groupedVariations = $groupedVariations->sortBy(function ($group) {
                $quality = $group->first()['metal_color']['quality'] ?? null;
                return is_numeric($quality) ? (int)$quality : PHP_INT_MAX;
            });

            // ---- Final product structure
            $finalProducts[] = [
                'id' => $product->products_id,
                'product' => [
                    'id' => $product->products_id,
                    'name' => $product->products_name,
                    'master_sku' => $product->master_sku,
                    'description' => $product->products_description,
                    'ready_to_ship' => $product->ready_to_ship,
                    'categories_id' => $product->categories_id,
                    'parent_category_id' => $product->parent_category_id,
                    'psc_id' => $product->psc_id,
                    'is_build' => $product->is_build_product,
                    'product_collection_id' => $product->product_collection_id,
                ],
                'category' => $category ? [
                    'id' => $category->category_id,
                    'name' => $category->category_name,
                    'parent' => $parent ? [
                        'id' => $parent->category_id,
                        'name' => $parent->category_name
                    ] : null
                ] : null,
                'metal_variations' => $groupedVariations,
            ];
        }

        /* -----------------------------------------------------------
            9) RESPONSE
        ----------------------------------------------------------- */
        return response()->json([
            'slug'          => $slug,
            'style_data'    => $styleData,
            'metal_types'   => $metalTypes,
            'data'          => $finalProducts,
            'totalProducts' => count($finalProducts),
            'currentPage'   => $page,
            'perPage'       => $perPage,
            'totalPages'    => ceil($totalProducts / $perPage),
        ]);
    }

    /* -----------------------------------------------------------
        EMPTY RESPONSE for collectionData method
    ----------------------------------------------------------- */
    private function emptyResponse($perPage, $slug)
    {
        return response()->json([
            'slug'             => $slug,
            'style_data'       => [],
            'metal_types'      => [],
            'data'             => [],
            'totalProducts'    => 0,
            'currentPage'      => 1,
            'perPage'          => $perPage,
            'totalPages'       => 0,
        ]);
    }

    public function giftData(Request $request, $slug = null)
    {
        $filters = [
            'price' => null,
            'style' => $request->input('style'),
            'collection' => $request->input('collection'),
            'ready_to_ship' => $request->input('ready_to_ship'),
            'sort' => $request->input('sort'),
            'metal_color_id' => $request->input('metal_color_id'),
            'category' => $request->input('category'),
            'subcategory' => $request->input('subcategory'),
            'menucollection' => $request->input('menucollection'),
            'gender' => null,
            'bond' => null,
        ];

        $defaultPrice = null;

        if ($slug) {
            switch ($slug) {
                case 'jewelry-gifts-for-him':
                    $filters['gender'] = '0';
                    break;

                case 'gifts-under-500':
                    $defaultPrice = '0-500';
                    break;

                case 'gifts-under-1000':
                    $defaultPrice = '0-1000';
                    break;

                case 'gifts-under-1500':
                    $defaultPrice = '0-1500';
                    break;

                case 'ready-to-ship-diamond-jewelry-gifts':
                    $filters['ready_to_ship'] = 'true';
                    break;

                // Collections
                case 'bouquet':
                case 'toi-et-moi-collection':
                case 'vine-collection':
                    $collectionNames = [
                        'bouquet' => 'Bouquet',
                        'toi-et-moi-collection' => 'Toi et Moi',
                        'vine-collection' => 'Vine',
                    ];
                    $collection = DB::table('product_collections')
                        ->whereRaw('LOWER(name) = ?', [strtolower($collectionNames[$slug])])
                        ->first();

                    if (!$collection) {
                        return response()->json([
                            'style_data' => [],
                            'collection_data' => [],
                            'metal_types' => [],
                            'data' => [],
                            'totalProducts' => 0,
                            'currentPage' => 1,
                            'perPage' => $perPage,
                            'totalPages' => 0,
                        ]);
                    }
                    $filters['menucollection'] = $collection->id;
                    break;

                // Categories
                case 'diamond-ring-gifts':
                case 'necklace-gifts':
                case 'earring-gifts':
                case 'bracelet-gifts':
                    $categoryNames = [
                        'diamond-ring-gifts' => 'rings',
                        'necklace-gifts' => 'necklaces',
                        'earring-gifts' => 'earrings',
                        'bracelet-gifts' => 'bracelet',
                    ];
                    $category = DB::table('categories')
                        ->whereRaw('LOWER(category_name) = ?', [strtolower($categoryNames[$slug])])
                        ->first();

                    if (!$category) {
                        return response()->json([
                            'style_data' => [],
                            'collection_data' => [],
                            'metal_types' => [],
                            'data' => [],
                            'totalProducts' => 0,
                            'currentPage' => 1,
                            'perPage' => $perPage,
                            'totalPages' => 0,
                        ]);
                    }
                    $filters['category'] = $category->category_id;
                    break;
            }
        }

        if ($request->has('price') && preg_match('/^(\d+)-(\d+)$/', $request->query('price'))) {
            $filters['price'] = $request->query('price'); // e.g., 1000-2000
        } elseif ($defaultPrice) {
            $filters['price'] = $defaultPrice; // e.g., 0-1500
        }

        // Always enforce wedding products
        $filters['is_build_product'] = "3";

        if (!empty($filters['price'])) {
            $filters['price'] = str_replace(['\u2013', ' '], ['-', ''], $filters['price']);
        }

        $perPage = (int) $request->input('perPage', 20);
        $page = (int) $request->input('page', 1);

        // -----------------------------
        // Style Data
        // -----------------------------
        $styleData = !empty($filters['subcategory']) || !empty($filters['category'])
            ? ProductStyleCategory::where('psc_category_id', $filters['subcategory'] ?? $filters['category'])->get()
            : ProductStyleCategory::join(DB::raw('(
                SELECT MIN(psc_id) as psc_id FROM products_style_category
                WHERE parent_category_id IS NULL GROUP BY psc_category_id
            ) as grouped_styles'), 'products_style_category.psc_id', '=', 'grouped_styles.psc_id')
                ->select('products_style_category.*')->get();

        // -----------------------------
        // Collection Data
        // -----------------------------
        if (!empty($filters['subcategory']) && !empty($filters['category'])) {
            $collectionData = ProductCollection::where('product_category_ids', $filters['subcategory'])->get();
        } elseif (!empty($filters['category'])) {
            $collectionData = ProductCollection::where('parent_category_ids', $filters['category'])->get();
        } else {
            $collectionData = ProductCollection::join(DB::raw('(
                    SELECT MIN(id) as id
                    FROM product_collections
                    WHERE parent_category_ids IS NOT NULL AND product_category_ids IS NULL
                    GROUP BY parent_category_ids
                ) as grouped_collection'), 'product_collections.id', '=', 'grouped_collection.id')
                ->select('product_collections.*')
                ->get();
        }

        // -----------------------------
        // Metal Types sorted
        // -----------------------------
        $metalTypes = MetalType::all()->sort(function ($a, $b) {
            $aVal = is_numeric(substr($a->dmt_tooltip, 0, 2)) ? (int) filter_var($a->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            $bVal = is_numeric(substr($b->dmt_tooltip, 0, 2)) ? (int) filter_var($b->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            return $aVal <=> $bVal;
        })->values();

        // -----------------------------
        // Product + Variation Query (Optimized)
        // -----------------------------
        $sort = $filters['sort'];
        $sortDirection = in_array($sort, ['price_desc', 'date_desc']) ? 'desc' : 'asc';
        $priceAggFunc = $sort === 'price_desc' ? 'MAX' : 'MIN';

        $productQuery = Product::query()
            ->select('products.products_id')
            ->join('product_variations', 'products.products_id', '=', 'product_variations.product_id')
            ->where(function($q) {
                $q->where('products.is_build_product', '3')
                ->orWhere('products.is_gift', 1);
            });



        // Apply gender filter if set
        if (!is_null($filters['gender'])) {
            $productQuery->where('products.gender', (string)$filters['gender']);
        }

        // Apply bond filter
        if (!is_null($filters['bond'])) {
            $productQuery->where('products.bond', $filters['bond']);
        }

        // ---- Variation Filters ----
        if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
            [$min, $max] = explode('-', $filters['price']);
            $productQuery->whereBetween('product_variations.price', [(int) $min, (int) $max]);
        }
        if (!empty($filters['metal_color_id'])) {
            $productQuery->where('product_variations.metal_color_id', $filters['metal_color_id']);
        }

        // ---- Product Filters ----
        if (!empty($filters['category'])) {
            $productQuery->where('products.categories_id', $filters['category']);
        }
        // if (!empty($filters['subcategory'])) {
        //     $productQuery->where('products.categories_id', $filters['subcategory']);
        // } elseif (!empty($filters['category'])) {
        //     $productQuery->where('products.categories_id', $filters['category']);
        // }
        if (!empty($filters['menucollection'])) {
            $productQuery->where('products.product_collection_id', $filters['menucollection']);
        }
        if (!empty($filters['style'])) {
            $productQuery->where('products.psc_id', $filters['style']);
        }
        if (!empty($filters['collection'])) {
            $productQuery->where('products.product_collection_id', $filters['collection']);
        }
        if (!empty($filters['ready_to_ship']) && $filters['ready_to_ship'] === 'true') {
            $productQuery->where('products.ready_to_ship', 1);
        }

        // ---- Sorting ----
        if ($sort === 'date_asc' || $sort === 'date_desc') {
            $productQuery->orderBy('products.created_at', $sortDirection);
            $totalProducts = (clone $productQuery)->distinct()->count('products.products_id');
            $productIds = $productQuery->distinct()
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('products.products_id');
        } else {
            $priceQuery = ProductVariation::select('product_id', DB::raw("$priceAggFunc(price) as sort_price"))
                ->whereIn('product_id', $productQuery->pluck('products.products_id'))
                ->groupBy('product_id');

            $totalProducts = $priceQuery->distinct('product_id')->count('product_id');

            $productIds = $priceQuery
                ->orderBy('sort_price', $sortDirection)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('product_id');
        }

        if ($productIds->isEmpty()) {
            return response()->json([
                'style_data' => $styleData,
                'collection_data' => $collectionData,
                'metal_types' => $metalTypes,
                'data' => [],
                'totalProducts' => 0,
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalPages' => 0,
            ]);
        }


        // -----------------------------
        // Fetch Products + Variations
        // -----------------------------
        $products = Product::with([
                'productcategory.parent',
                'variations.metalColor'
            ])
            ->whereIn('products_id', $productIds)
            ->orderByRaw('FIELD(products_id, ' . implode(',', $productIds->toArray()) . ')')
            ->get();

        $validProducts = [];
        foreach ($products as $product) {
            $variations = $product->variations;

            // Extra filtering inside eager-loaded variations
            if (!empty($filters['metal_color_id'])) {
                $hasMatchingMetal = $variations->contains('metal_color_id', $filters['metal_color_id']);
                if (!$hasMatchingMetal) continue;
            }

            if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
                [$min, $max] = explode('-', $filters['price']);
                $variations = $variations->filter(fn($v) => $v->price >= $min && $v->price <= $max);
            }
            if ($variations->isEmpty()) continue;

            $category = $product->productcategory;
            $parent = $category?->parent;

            // Group variations by metal color
            $groupedByMetal = $variations->groupBy('metal_color_id')->map(function ($group) use ($category, $parent) {
                return $group->map(function ($variation) use ($category, $parent) {
                    return [
                        'id' => $variation->id,
                        'product_id' => $variation->product_id,
                        'carat' => $variation->carat,
                        'price' => $variation->price,
                        'original_price' => $variation->regular_price,
                        'sku' => $variation->sku,
                        'metal_color_id' => $variation->metal_color_id,
                        'metal_color' => $variation->metalColor ? [
                            'id' => $variation->metalColor->dmt_id,
                            'name' => $variation->metalColor->dmt_name,
                            'quality' => $variation->metalColor->dmt_tooltip,
                            'hex' => $variation->metalColor->color_code ?? null,
                        ] : null,
                        'weight' => $variation->weight,
                        'images' => $variation->images,
                        'category' => $category ? [
                            'id' => $category->category_id,
                            'name' => $category->category_name,
                            'parent' => $parent ? [
                                'id' => $parent->category_id,
                                'name' => $parent->category_name
                            ] : null
                        ] : null,
                    ];
                });
            })->filter(fn($group) => $group->isNotEmpty());

            // Sort metals by quality
            $groupedByMetal = collect($groupedByMetal)->sortBy(function ($group) {
                $quality = $group->first()['metal_color']['quality'] ?? null;
                return is_numeric($quality) ? (int) $quality : PHP_INT_MAX . '_' . $quality;
            });

            $validProducts[] = [
                'id' => $product->products_id,
                'product' => [
                    'id' => $product->products_id,
                    'name' => $product->products_name,
                    'master_sku' => $product->master_sku,
                    'description' => $product->products_description,
                    'ready_to_ship' => $product->ready_to_ship,
                    'categories_id' => $product->categories_id,
                    'parent_category_id' => $product->parent_category_id,
                    'psc_id' => $product->psc_id,
                    'is_build' => $product->is_build_product,
                    'product_collection_id' => $product->product_collection_id,
                ],
                'category' => $category ? [
                    'id' => $category->category_id,
                    'name' => $category->category_name,
                    'parent' => $parent ? [
                        'id' => $parent->category_id,
                        'name' => $parent->category_name
                    ] : null
                ] : null,
                'metal_variations' => $groupedByMetal,
            ];
        }
        $totalProducts = count($validProducts);
        // -----------------------------
        // Final Response
        // -----------------------------
        return response()->json([
            'slug' => $slug,
            'style_data' => $styleData,
            'collection_data' => $collectionData,
            'metal_types' => $metalTypes,
            'data' => $validProducts,
            'totalProducts' => $totalProducts,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($totalProducts / $perPage),
        ]);
    }

    public function saleData(Request $request, $slug = null)
    {
        $filters = [
            'price' => $request->input('price'),
            'style' => $request->input('style'),
            'collection' => $request->input('collection'),
            'ready_to_ship' => $request->input('ready_to_ship'),
            'sort' => $request->input('sort'),
            'metal_color_id' => $request->input('metal_color_id'),
            'category' => $request->input('category'),
            'subcategory' => $request->input('subcategory'),
            'menucollection' => $request->input('menucollection'),
            'gender' => null,
            'bond' => null,
        ];

        if ($slug) {
            switch ($slug) {
                case 'men-wedding-rings':
                    $filters['gender'] = '0'; // or 2, depending on your DB values
                    break;
                case 'mens-metal-wedding-bands':
                    $filters['gender'] = '0';
                    $filters['bond'] = "0";
                    break;
                case 'mens-diamond-wedding-bands':
                    $filters['gender'] = '0';
                    $filters['bond'] = "1";
                    break;
                case 'women-wedding-rings':
                    $filters['gender'] = '1'; // only women’s rings
                    break;
                case 'womens-metal-wedding-rings':
                    $filters['gender'] = '1';
                    $filters['bond'] = "0";
                    break;
                case 'womens-diamond-wedding-rings':
                    $filters['gender'] = '1';
                    $filters['bond'] = "1";
                    break;
                case 'womens-anniversary-rings':
                    $filters['gender'] = '1';

                    // Lookup category id dynamically
                    $category = DB::table('categories')
                        ->whereRaw('LOWER(category_name) = ?', ['anniversary'])
                        ->first();
                    
                    if ($category) {
                        $filters['category'] = $category->category_id;
                    }
                    break;

                case 'womens-eternity-rings':
                    $filters['gender'] = '1';
                    $category = DB::table('categories')
                        ->whereRaw('LOWER(category_name) = ?', ['eternity'])
                        ->first();
                    
                    if ($category) {
                        $filters['category'] = $category->category_id;
                    }
                    break;

            }
        }

        // Always enforce wedding products
        $filters['is_build_product'] = "4";

        if (!empty($filters['price'])) {
            $filters['price'] = str_replace(['\u2013', ' '], ['-', ''], $filters['price']);
        }

        $perPage = (int) $request->input('perPage', 20);
        $page = (int) $request->input('page', 1);

        // -----------------------------
        // Style Data
        // -----------------------------
        $styleData = !empty($filters['subcategory']) || !empty($filters['category'])
            ? ProductStyleCategory::where('psc_category_id', $filters['subcategory'] ?? $filters['category'])->get()
            : ProductStyleCategory::join(DB::raw('(
                SELECT MIN(psc_id) as psc_id FROM products_style_category
                WHERE parent_category_id IS NULL GROUP BY psc_category_id
            ) as grouped_styles'), 'products_style_category.psc_id', '=', 'grouped_styles.psc_id')
                ->select('products_style_category.*')->get();

        // -----------------------------
        // Collection Data
        // -----------------------------
        if (!empty($filters['subcategory']) && !empty($filters['category'])) {
            $collectionData = ProductCollection::where('product_category_ids', $filters['subcategory'])->get();
        } elseif (!empty($filters['category'])) {
            $collectionData = ProductCollection::where('parent_category_ids', $filters['category'])->get();
        } else {
            $collectionData = ProductCollection::join(DB::raw('(
                    SELECT MIN(id) as id
                    FROM product_collections
                    WHERE parent_category_ids IS NOT NULL AND product_category_ids IS NULL
                    GROUP BY parent_category_ids
                ) as grouped_collection'), 'product_collections.id', '=', 'grouped_collection.id')
                ->select('product_collections.*')
                ->get();
        }

        // -----------------------------
        // Metal Types sorted
        // -----------------------------
        $metalTypes = MetalType::all()->sort(function ($a, $b) {
            $aVal = is_numeric(substr($a->dmt_tooltip, 0, 2)) ? (int) filter_var($a->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            $bVal = is_numeric(substr($b->dmt_tooltip, 0, 2)) ? (int) filter_var($b->dmt_tooltip, FILTER_SANITIZE_NUMBER_INT) : 999;
            return $aVal <=> $bVal;
        })->values();

        // -----------------------------
        // Product + Variation Query (Optimized)
        // -----------------------------
        $sort = $filters['sort'];
        $sortDirection = in_array($sort, ['price_desc', 'date_desc']) ? 'desc' : 'asc';
        $priceAggFunc = $sort === 'price_desc' ? 'MAX' : 'MIN';

        $productQuery = Product::query()
            ->select('products.products_id')
            ->join('product_variations', 'products.products_id', '=', 'product_variations.product_id')
            ->where(function($q) {
                $q->where('products.is_build_product', '4')
                ->orWhere('products.is_sale', 1);
            });


        // Apply gender filter if set
        if (!is_null($filters['gender'])) {
            $productQuery->where('products.gender', (string)$filters['gender']);
        }

        // Apply bond filter
        if (!is_null($filters['bond'])) {
            $productQuery->where('products.bond', $filters['bond']);
        }

        // ---- Variation Filters ----
        if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
            [$min, $max] = explode('-', $filters['price']);
            $productQuery->whereBetween('product_variations.price', [(int) $min, (int) $max]);
        }
        if (!empty($filters['metal_color_id'])) {
            $productQuery->where('product_variations.metal_color_id', $filters['metal_color_id']);
        }

        // ---- Product Filters ----
        if (!empty($filters['category'])) {
            $productQuery->where('products.categories_id', $filters['category']);
        }
        // if (!empty($filters['subcategory'])) {
        //     $productQuery->where('products.categories_id', $filters['subcategory']);
        // } elseif (!empty($filters['category'])) {
        //     $productQuery->where('products.categories_id', $filters['category']);
        // }
        if (!empty($filters['menucollection'])) {
            $productQuery->where('products.product_collection_id', $filters['menucollection']);
        }
        if (!empty($filters['style'])) {
            $productQuery->where('products.psc_id', $filters['style']);
        }
        if (!empty($filters['collection'])) {
            $productQuery->where('products.product_collection_id', $filters['collection']);
        }
        if (!empty($filters['ready_to_ship']) && $filters['ready_to_ship'] === 'true') {
            $productQuery->where('products.ready_to_ship', 1);
        }

        // ---- Sorting ----
        if ($sort === 'date_asc' || $sort === 'date_desc') {
            $productQuery->orderBy('products.created_at', $sortDirection);
            $totalProducts = (clone $productQuery)->distinct()->count('products.products_id');
            $productIds = $productQuery->distinct()
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('products.products_id');
        } else {
            $priceQuery = ProductVariation::select('product_id', DB::raw("$priceAggFunc(price) as sort_price"))
                ->whereIn('product_id', $productQuery->pluck('products.products_id'))
                ->groupBy('product_id');

            $totalProducts = $priceQuery->distinct('product_id')->count('product_id');

            $productIds = $priceQuery
                ->orderBy('sort_price', $sortDirection)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('product_id');
        }

        if ($productIds->isEmpty()) {
            return response()->json([
                'style_data' => $styleData,
                'collection_data' => $collectionData,
                'metal_types' => $metalTypes,
                'data' => [],
                'totalProducts' => 0,
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalPages' => 0,
            ]);
        }


        // -----------------------------
        // Fetch Products + Variations
        // -----------------------------
        $products = Product::with([
                'productcategory.parent',
                'variations.metalColor'
            ])
            ->whereIn('products_id', $productIds)
            ->orderByRaw('FIELD(products_id, ' . implode(',', $productIds->toArray()) . ')')
            ->get();

        $validProducts = [];
        foreach ($products as $product) {
            $variations = $product->variations;

            // Extra filtering inside eager-loaded variations
            if (!empty($filters['metal_color_id'])) {
                $hasMatchingMetal = $variations->contains('metal_color_id', $filters['metal_color_id']);
                if (!$hasMatchingMetal) continue;
            }

            if (!empty($filters['price']) && preg_match('/^(\d+)-(\d+)$/', $filters['price'])) {
                [$min, $max] = explode('-', $filters['price']);
                $variations = $variations->filter(fn($v) => $v->price >= $min && $v->price <= $max);
            }
            if ($variations->isEmpty()) continue;

            $category = $product->productcategory;
            $parent = $category?->parent;

            // Group variations by metal color
            $groupedByMetal = $variations->groupBy('metal_color_id')->map(function ($group) use ($category, $parent) {
                return $group->map(function ($variation) use ($category, $parent) {
                    return [
                        'id' => $variation->id,
                        'product_id' => $variation->product_id,
                        'carat' => $variation->carat,
                        'price' => $variation->price,
                        'original_price' => $variation->regular_price,
                        'sku' => $variation->sku,
                        'metal_color_id' => $variation->metal_color_id,
                        'metal_color' => $variation->metalColor ? [
                            'id' => $variation->metalColor->dmt_id,
                            'name' => $variation->metalColor->dmt_name,
                            'quality' => $variation->metalColor->dmt_tooltip,
                            'hex' => $variation->metalColor->color_code ?? null,
                        ] : null,
                        'weight' => $variation->weight,
                        'images' => $variation->images,
                        'category' => $category ? [
                            'id' => $category->category_id,
                            'name' => $category->category_name,
                            'parent' => $parent ? [
                                'id' => $parent->category_id,
                                'name' => $parent->category_name
                            ] : null
                        ] : null,
                    ];
                });
            })->filter(fn($group) => $group->isNotEmpty());

            // Sort metals by quality
            $groupedByMetal = collect($groupedByMetal)->sortBy(function ($group) {
                $quality = $group->first()['metal_color']['quality'] ?? null;
                return is_numeric($quality) ? (int) $quality : PHP_INT_MAX . '_' . $quality;
            });

            $validProducts[] = [
                'id' => $product->products_id,
                'product' => [
                    'id' => $product->products_id,
                    'name' => $product->products_name,
                    'master_sku' => $product->master_sku,
                    'description' => $product->products_description,
                    'ready_to_ship' => $product->ready_to_ship,
                    'categories_id' => $product->categories_id,
                    'parent_category_id' => $product->parent_category_id,
                    'psc_id' => $product->psc_id,
                    'is_build' => $product->is_build_product,
                    'product_collection_id' => $product->product_collection_id,
                ],
                'category' => $category ? [
                    'id' => $category->category_id,
                    'name' => $category->category_name,
                    'parent' => $parent ? [
                        'id' => $parent->category_id,
                        'name' => $parent->category_name
                    ] : null
                ] : null,
                'metal_variations' => $groupedByMetal,
            ];
        }
        $totalProducts = count($validProducts);
        // -----------------------------
        // Final Response
        // -----------------------------
        return response()->json([
            'slug' => $slug,
            'style_data' => $styleData,
            'collection_data' => $collectionData,
            'metal_types' => $metalTypes,
            'data' => $validProducts,
            'totalProducts' => $totalProducts,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($totalProducts / $perPage),
        ]);
    }


    public function showProductById($id)
    {
        $product = Product::with([
            'variations.shape:id,name,image',
            'variations.metalColor:dmt_id,dmt_name,dmt_tooltip,color_code',
            'productcategory:category_id,category_name,parent_id',
            'productcategory.parent:category_id,category_name',
            'variations.diamondQualityGroup:dqg_id,dqg_name',
            'diamondCut:id,name',
            'stone:pst_id,pst_name',
            'productClarity:id,name',
        ])
        ->where('products_id', $id)
        ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $isBuild = (int) ($product->is_build_product ?? $product->is_build ?? 0);

        $category = $product->productcategory;
        $parentCategory = $category?->parent;

        $variations = $product->variations;

        /* ---------------------------------------
        * Collect tooltip / quality per metal
        ----------------------------------------*/
        $qualityByMetal = [];
        foreach ($variations as $v) {
            $mid = (string) $v->metal_color_id;
            if (!isset($qualityByMetal[$mid])) {
                $qualityByMetal[$mid] = optional($v->metalColor)->dmt_tooltip;
            }
        }

        /* ---------------------------------------
        * Format Variation
        ----------------------------------------*/
        $formatVariation = function ($v) use ($category, $parentCategory) {
            return [
                'id' => $v->id,
                'product_id' => $v->product_id,
                'carat' => $v->carat,
                'price' => $v->price,
                'original_price' => $v->regular_price, // FIXED
                'sku' => $v->sku,
                'shape_id' => $v->shape_id,
                'metal_color_id' => $v->metal_color_id,
                'weight' => $v->weight,
                'diamond_quality_id' => $v->diamond_quality_id,
                'diamond_quality_name' => optional($v->diamondQualityGroup)->dqg_name,
                'metal_color' => $v->metalColor ? [
                    'id' => $v->metalColor->dmt_id,
                    'name' => $v->metalColor->dmt_name,
                    'quality' => $v->metalColor->dmt_tooltip,
                    'hex' => $v->metalColor->color_code,
                ] : null,

                'shape' => $v->shape ? [
                    'id' => $v->shape->id,
                    'name' => $v->shape->name,
                    'image' => $v->shape->image ? asset("storage/shapes/{$v->shape->image}") : null,
                ] : null,

                'images' => $v->images,
                'video' => $v->video ? asset("storage/variation_videos/{$v->video}") : null,

                'category' => $category ? [
                    'id' => $category->category_id,
                    'name' => $category->category_name,
                    'parent' => $parentCategory ? [
                        'id' => $parentCategory->category_id,
                        'name' => $parentCategory->category_name,
                    ] : null,
                ] : null,
            ];
        };

        /*---------------------------------------
        * Group variations by metal and sort
        ----------------------------------------*/
        $grouped = $variations->groupBy('metal_color_id')
            ->map(fn($group) => $group->map($formatVariation)->values())
            ->filter()
            ->toArray();

        // Sorting metal groups by tooltip value
        $metalIds = array_keys($grouped);

        usort($metalIds, function ($a, $b) use ($qualityByMetal) {
            $q1 = $qualityByMetal[$a] ?? null;
            $q2 = $qualityByMetal[$b] ?? null;

            $n1 = is_numeric($q1);
            $n2 = is_numeric($q2);

            if ($n1 && $n2) return ((int) $q1) <=> ((int) $q2);
            if ($n1) return -1;
            if ($n2) return 1;

            return strcmp((string) $q1, (string) $q2);
        });

        $sortedMetal = [];
        foreach ($metalIds as $mId) {
            $sortedMetal[$mId] = $grouped[$mId];
        }

        /* ---------------------------------------
        * Final JSON Response
        ----------------------------------------*/
        return response()->json([
            'id' => $product->products_id,
            'product' => [
                'id' => $product->products_id,
                'name' => $product->products_name,
                'master_sku' => $product->master_sku,
                'description' => $product->products_description,
                'ready_to_ship' => $product->ready_to_ship,
                'delivery_days' => $product->delivery_days,
                'product_clarity' => $product->productClarity->name ?? null,
                'product_modal' => $product->products_model,
                'stone_type' => $product->stone->pst_name ?? null,
                'cut' => $product->diamondCut->name ?? null,
                'is_build' => $isBuild,
            ],

            'category' => $category ? [
                'id' => $category->category_id,
                'name' => $category->category_name,
                'parent' => $parentCategory ? [
                    'id' => $parentCategory->category_id,
                    'name' => $parentCategory->category_name,
                ] : null,
            ] : null,

            'metal_variations' => $sortedMetal,
        ]);
    }

}