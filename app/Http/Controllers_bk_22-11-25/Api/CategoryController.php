<?php

// namespace App\Http\Controllers\Api;

// use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;
// use App\Models\Category;
// use App\Models\ProductCollection;

// class CategoryController extends Controller
// {
//     //
//     public function jewelryData()
//     {
//         $all = Category::select('category_id', 'category_name', 'parent_id')->get();

//         $mainCategories = $all->whereNull('parent_id')->values();
//         $categoryMap = [];

//         foreach ($mainCategories as $main) {
//             $children = $all
//                 ->where('parent_id', $main->category_id)
//                 ->map(function ($child) {
//                     return [
//                         'id' => $child->category_id,
//                         'name' => $child->category_name,
//                     ];
//                 })
//                 ->values();

//             $categoryMap[$main->category_name] = $children;
//         }

//         $collections = ProductCollection::where('display_in_menu', 1)
//         ->select('id', 'name')
//         ->get();

//         return response()->json([
//             'categories' => $mainCategories->map(fn($cat) => [
//                 'id' => $cat->category_id,
//                 'name' => $cat->category_name
//             ])->values(),
//             'categoryMap' => $categoryMap,
//             'collections' => $collections,
//         ]);
//     }


// }



namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductCollection;

class CategoryController extends Controller
{
    public function jewelryData()
{
    $categoriesWithJewelry = Category::whereJsonContains('product_type', "0")
        ->select('category_id', 'category_name', 'parent_id')
        ->get();

    $parentIds = $categoriesWithJewelry->pluck('parent_id')->filter()->unique();
    $parentCategories = Category::whereIn('category_id', $parentIds)
        ->select('category_id', 'category_name', 'parent_id')
        ->get();

    $all = $categoriesWithJewelry->merge($parentCategories);

    $mainCategories = $all->whereNull('parent_id')->values();

    $categoryMap = [];
    foreach ($mainCategories as $main) {
        $children = $all
            ->where('parent_id', $main->category_id)
            ->map(function ($child) {
                return [
                    'id' => $child->category_id,
                    'name' => $child->category_name,
                ];
            })
            ->values();

        $categoryMap[$main->category_name] = $children;
    }

    $collections = ProductCollection::where('display_in_menu', 1)
        ->whereJsonContains('product_type', "0")
        ->select('id', 'name')
        ->get();

    return response()->json([
        'categories' => $mainCategories->map(fn($cat) => [
            'id' => $cat->category_id,
            'name' => $cat->category_name
        ])->values(),
        'categoryMap' => $categoryMap,
        'collections' => $collections,
    ]);
}

}

