<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Jewellery\CategoryController;
use App\Http\Controllers\DiamondMaster\DiamondCutController;
use App\Http\Controllers\DiamondMaster\DiamondSizeController;
use App\Http\Controllers\DiamondMaster\DiamondCuletController;
use App\Http\Controllers\DiamondMaster\DiamondShadeController;
use App\Http\Controllers\DiamondMaster\DiamondShapeController;
use App\Http\Controllers\DiamondMaster\DiamondGirdleController;
use App\Http\Controllers\DiamondMaster\DiamondMasterController;
use App\Http\Controllers\DiamondMaster\DiamondVendorController;
use App\Http\Controllers\DiamondMaster\DiamondWeightGroupController;
use App\Http\Controllers\DiamondMaster\DiamondSymmetryController;
use App\Http\Controllers\DiamondMaster\DiamondLabMasterController;
use App\Http\Controllers\DiamondMaster\DiamondFancyColorController;
use App\Http\Controllers\DiamondMaster\DiamondColorMasterController;
use App\Http\Controllers\DiamondMaster\DiamondFlourescenceController;
use App\Http\Controllers\DiamondMaster\DiamondPolishMasterController;
use App\Http\Controllers\DiamondMaster\DiamondClarityMasterController;
use App\Http\Controllers\DiamondMaster\DiamondKeyToSymbolsMasterController;
use App\Http\Controllers\DiamondMaster\DiamondFancyColorIntensityMasterController;
use App\Http\Controllers\DiamondMaster\OrderController;
use App\Http\Controllers\Jewellery\MetalTypeController;
use App\Http\Controllers\Jewellery\ProductsToMetalTypeController;
use App\Http\Controllers\Jewellery\DiamondQualityGroupController;
use App\Http\Controllers\Jewellery\DiamondClarityGroupController;
use App\Http\Controllers\Jewellery\ProductColorMasterController;
use App\Http\Controllers\Jewellery\ProductCutMasterController;
use App\Http\Controllers\Jewellery\ProductController;
// use App\Http\Controllers\Jewellery\ProductImageController;
use App\Http\Controllers\Jewellery\ProductOptionController;
use App\Http\Controllers\Jewellery\ProductOptionValueController;
use App\Http\Controllers\Jewellery\ProductStoneController;
use App\Http\Controllers\Jewellery\ProductStyleCategoryController;
use App\Http\Controllers\Jewellery\ProductStyleGroupController;
use App\Http\Controllers\Jewellery\ProductToCategoryController;
use App\Http\Controllers\Jewellery\ProductToStyleCategoryController;
use App\Http\Controllers\Jewellery\ShopZonesController;
use App\Http\Controllers\Jewellery\ProductAssignOptionController;
use App\Http\Controllers\Jewellery\GeoZonesController;
use App\Http\Controllers\Jewellery\ProductToStyleGroupController;
use App\Http\Controllers\Jewellery\ProductToStoneTypeController;
use App\Http\Controllers\Jewellery\ProductToOptionController;
use App\Http\Controllers\Jewellery\CountryController;
use App\Http\Controllers\Jewellery\ProductToShapeController;
use App\Http\Controllers\Jewellery\ShopTaxClassesController;
use App\Http\Controllers\Jewellery\ShopTaxRateController;
use App\Http\Controllers\Jewellery\CollectionController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\PayPalController;
use App\Http\Controllers\Jewellery\CouponController;
use App\Http\Controllers\Jewellery\MetalPriceController;
use App\Http\Controllers\Jewellery\AppointmentController;
use App\Http\Controllers\Jewellery\ContactUsController;
use App\Http\Controllers\Jewellery\BlogController;
use App\Http\Controllers\Jewellery\EnquiryController;
use App\Http\Controllers\Jewellery\UserController;


Route::prefix('admin/jewellery')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/fetch', [UserController::class, 'fetchUsers'])->name('users.fetch');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
});


Route::get('/share/{id}', [ProductController::class, 'share'])->name('product.share');

Route::get('/import-products', [ProductImportController::class, 'showForm'])->name('products.import.form');
Route::post('/import-products', [ProductImportController::class, 'import'])->name('products.import');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
Route::get('/admin/forgetPassword', [AuthController::class, 'forgetPasswordView'])->name('admin.forget');
Route::post('/password/email', [AuthController::class, 'sendResetLink'])->name('sendResetLink');
Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'forgetPassword']);
Route::get('/paypal/capture', [PayPalController::class, 'captureOrder'])->name('paypal.capture');
Route::get('/paypal/cancel', [PayPalController::class, 'cancel'])->name('paypal.cancel');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard'); // welcome page
    })->name('admin.dashboard');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/profile', [AdminAuthController::class, 'profile'])->name('admin.profile');
    Route::post('/profile/upload', [AdminAuthController::class, 'upload'])->name('profile.upload');
    Route::delete('/profile/image', [AdminAuthController::class, 'deleteImage'])->name('profile.deleteImage');
    Route::get('/changePassword', [AdminAuthController::class, 'changePassword'])->name('change.password');
    Route::post('/changePassword', [AuthController::class, 'resetPassword'])->name('admin.reset.password');

    Route::controller(DiamondClarityMasterController::class)->group(function () {
        Route::get('/clarity', 'index')->name('clarity.index');
        Route::post('/clarity', 'store')->name('clarity.store');
        Route::get('/clarity/{id}', 'show')->name('clarity.show');
        Route::put('/clarity/{id}', 'update')->name('clarity.update');
        Route::delete('/clarity/{id}', 'destroy')->name('clarity.destroy');
    });

    Route::controller(DiamondShadeController::class)->group(function () {
        Route::get('/shades', 'index')->name('shades.index');
        Route::post('/shades', 'store')->name('shades.store');
        Route::get('/shades/{id}', 'show')->name('shades.show');
        Route::put('/shades/{id}', 'update')->name('shades.update');
        Route::delete('/shades/{id}', 'destroy')->name('shades.destroy');
    });

    Route::controller(DiamondShapeController::class)->group(function () {
        Route::get('/shapes', 'index')->name('shapes.index');
        Route::post('/shapes', 'store')->name('shapes.store');
        Route::get('/shapes/{id}', 'show')->name('shapes.show');
        Route::put('/shapes/{id}', 'update')->name('shapes.update');
        Route::delete('/shapes/{id}', 'destroy')->name('shapes.destroy');
    });

    Route::controller(DiamondSizeController::class)->group(function () {
        Route::get('/sizes', 'index')->name('sizes.index');
        Route::post('/sizes', 'store')->name('sizes.store');
        Route::get('/sizes/{id}', 'show')->name('sizes.show');
        Route::put('/sizes/{id}', 'update')->name('sizes.update');
        Route::delete('/sizes/{id}', 'destroy')->name('sizes.destroy');
    });

    Route::controller(DiamondSymmetryController::class)->group(function () {
        Route::get('/symmetry', 'index')->name('symmetry.index');
        Route::post('/symmetry', 'store')->name('symmetry.store');
        Route::get('/symmetry/{id}', 'show')->name('symmetry.show');
        Route::put('/symmetry/{id}', 'update')->name('symmetry.update');
        Route::delete('/symmetry/{id}', 'destroy')->name('symmetry.destroy');
    });
    Route::controller(DiamondFlourescenceController::class)->group(function () {
        Route::get('/flourescence', 'index')->name('flourescence.index');
        Route::post('/flourescence', 'store')->name('flourescence.store');
        Route::get('/flourescence/{id}', 'show')->name('flourescence.show');
        Route::put('/flourescence/{id}', 'update')->name('flourescence.update');
        Route::delete('/flourescence/{id}', 'destroy')->name('flourescence.destroy');
    });

    Route::controller(DiamondFancyColorController::class)->group(function () {
        Route::get('/fancyColor', 'index')->name('fancyColor.index');
        Route::post('/fancyColor', 'store')->name('fancyColor.store');
        Route::get('/fancyColor/{id}', 'show')->name('fancyColor.show');
        Route::put('/fancyColor/{id}', 'update')->name('fancyColor.update');
        Route::delete('/fancyColor/{id}', 'destroy')->name('fancyColor.destroy');
    });

    Route::controller(DiamondGirdleController::class)->group(function () {
        Route::get('/girdle', 'index')->name('girdle.index');
        Route::post('/girdle', 'store')->name('girdle.store');
        Route::get('/girdle/{id}', 'show')->name('girdle.show');
        Route::put('/girdle/{id}', 'update')->name('girdle.update');
        Route::delete('/girdle/{id}', 'destroy')->name('girdle.destroy');
    });

    Route::controller(DiamondCuletController::class)->group(function () {
        Route::get('/culet', 'index')->name('culet.index');
        Route::post('/culet', 'store')->name('culet.store');
        Route::get('/culet/{id}', 'show')->name('culet.show');
        Route::put('/culet/{id}', 'update')->name('culet.update');
        Route::delete('/culet/{id}', 'destroy')->name('culet.destroy');
    });

    Route::controller(DiamondKeyToSymbolsMasterController::class)->group(function () {
        Route::get('/keyToSymbols', 'index')->name('keytosymbols.index');
        Route::get('/keyToSymbols/create', 'create')->name('keytosymbols.create');
        Route::get('/keyToSymbols/{id}/edit', 'edit')->name('keytosymbols.edit');
        Route::get('/keyToSymbols/{id}', 'show')->name('keytosymbols.show');
        Route::post('/keyToSymbols', 'store')->name('keytosymbols.store');
        Route::put('/keyToSymbols/{id}', 'update')->name('keytosymbols.update');
        Route::delete('/keyToSymbols/{id}', 'destroy')->name('keytosymbols.destroy');
    });

    Route::controller(DiamondWeightGroupController::class)->group(function () {
        Route::get('diamond-weight-groups/', 'index')->name('diamond-weight-groups.index');
        Route::get('diamond-weight-groups/data', 'getData')->name('diamond-weight-groups.getData');
        Route::post('diamond-weight-groups/', 'store')->name('diamond-weight-groups.store');
        Route::get('diamond-weight-groups/{id}/edit', 'edit')->name('diamond-weight-groups.edit');
        Route::put('diamond-weight-groups/{id}', 'update')->name('diamond-weight-groups.update');;
        Route::delete('diamond-weight-groups/{id}', 'destroy')->name('diamond-weight-groups.destroy');
    });

    Route::controller(DiamondLabMasterController::class)->group(function () {
        Route::get('/lab', 'index')->name('diamondlab.index');
        Route::get('/lab/create', 'create')->name('diamondlab.create');
        Route::post('/lab', 'store')->name('diamondlab.store');
        Route::get('/lab/{id}/edit', 'edit')->name('diamondlab.edit');
        Route::get('/lab/{id}', 'show')->name('diamondlab.show');
        Route::put('/lab/{id}', 'update')->name('diamondlab.update');
        Route::delete('/lab/{id}', 'destroy')->name('diamondlab.destroy');
    });

    Route::controller(DiamondPolishMasterController::class)->group(function () {
        Route::get('/polish', 'index')->name('diamondpolish.index');
        Route::post('/polish', 'store')->name('diamondpolish.store');
        Route::get('/polish/{id}', 'show')->name('diamondpolish.show');
        Route::put('/polish/{id}', 'update')->name('diamondpolish.update');
        Route::delete('/polish/{id}', 'destroy')->name('diamondpolish.destroy');
    });
    Route::controller(DiamondColorMasterController::class)->group(function () {
        Route::get('diamond-color', 'index')->name('color.index');
        Route::post('diamond-color', 'store')->name('color.store');
        Route::get('diamond-color/{id}', 'show');
        Route::put('diamond-color/{id}', 'update');
        Route::delete('diamond-color/{id}', 'destroy');
    });

    Route::controller(DiamondCutController::class)->group(function () {
        Route::get('/cut', 'index')->name('cut.index');
        Route::post('/cut', 'store')->name('cut.store');
        Route::get('/cut/{id}', 'show')->name('cut.show');
        Route::put('/cut/{id}', 'update')->name('cut.update');
        Route::delete('/cut/{id}', 'destroy')->name('cut.destroy');
    });

    Route::controller(DiamondFancyColorIntensityMasterController::class)->group(function () {
        Route::get('/diamond-fancy-color-intensity', 'index')->name('fancy-color-intensity.index');
        Route::post('/diamond-fancy-color-intensity', 'store')->name('fancy-color-intensity.store');
        Route::get('/diamond-fancy-color-intensity/{id}', 'show')->name('fancy-color-intensity.show');
        Route::put('/diamond-fancy-color-intensity/{id}', 'update')->name('fancy-color-intensity.update');
        Route::delete('/diamond-fancy-color-intensity/{id}', 'destroy')->name('fancy-color-intensity.destroy');
    });

    Route::controller(DiamondVendorController::class)->group(function () {
        Route::get('/vendor', 'index')->name('vendor.index');
        Route::post('/vendor', 'store')->name('vendor.store');
        Route::get('/vendor/{id}', 'show')->name('vendor.show');
        Route::put('/vendor/{id}', 'update')->name('vendor.update');
        Route::delete('/vendor/{id}', 'destroy')->name('vendor.destroy');
    });

    // Route::controller(DiamondMasterController::class)->group(function(){
    //     Route::get('/diamond-master', 'index')->name('diamond-master.index');
    //     Route::get('/diamond-master/data', 'data')->name('diamond-master.data');
    //     Route::post('/diamond-master', 'store')->name('diamond-master.store');
    //     Route::get('/diamond-master/{id}', 'edit')->name('diamond-master.edit');
    //     Route::put('/diamond-master/{id}', 'update')->name('diamond-master.update');
    //     Route::delete('/diamond-master/{id}', 'destroy')->name('diamond-master.destroy');
    //     Route::post('/diamond-master/update-status/{id}', [DiamondMasterController::class, 'updateStatus']);
    // }); 
    Route::controller(DiamondMasterController::class)->group(function () {
        Route::get('diamond-master', 'index')->name('diamond-master.index');
        Route::get('diamond-master/data', 'dataBackend')->name('diamond-master.data');
        Route::get('DiamondMaster/master/create', 'create')->name('diamond-master.create');
        Route::post('diamond-master', 'store')->name('diamond-master.store');
        Route::get('diamond-master/{id}/edit', 'edit')->name('diamond-master.edit');
        Route::put('diamond-master/{id}', 'update')->name('diamond-master.update');
        Route::delete('diamond-master/{id}', 'destroy')->name('diamond-master.destroy');
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::get('category', 'index')->name('category.index');
        Route::post('category', 'store')->name('category.store');
        Route::get('category/{id}', 'show')->name('category.show');
        Route::put('category/{id}', 'update')->name('category.update');
        Route::delete('category/{id}', 'destroy')->name('category.destroy');
        Route::get('/get-child-categories', [CategoryController::class, 'getChildCategories'])->name('get.child.categories');
    });

Route::controller(OrderController::class)->group(function () {
    Route::get('orders', 'index')->name('orders.index');
    Route::get('orders/fetch', 'fetch')->name('orders.fetch');
    Route::get('orders/search-products', 'searchProducts')->name('orders.search.products');
    Route::get('orders/search-diamonds', 'searchDiamonds')->name('orders.search.diamonds');
    Route::get('orders/search-users', 'searchUsers')->name('orders.search.users');
    Route::post('orders', 'store')->name('orders.store');
    Route::get('orders/{order}', 'show')->name('orders.show');
    Route::patch('orders/{order}/status', 'changeStatus')->name('orders.changeStatus');
    Route::get('orders/{order}/invoice/download', 'downloadInvoice')->name('orders.invoice.download');
    Route::get('orders/{order}/invoice/send', 'sendInvoice')->name('orders.invoice.send');
    Route::get('orders/{order}/check-cancellation', 'checkCancellation')->name('orders.checkCancellation');
    Route::post('orders/{order}/process-refund', 'processRefund')->name('orders.processRefund');
    Route::put('orders/{order}', 'update')->name('orders.update');
    Route::delete('orders/{order}', 'destroy')->name('orders.destroy');
});

    Route::controller(\App\Http\Controllers\Jewellery\DiamondQualityGroupController::class)->group(function () {
        Route::get('diamondqualitygroup', 'index')->name('diamondqualitygroup.index');
        Route::get('diamondqualitygroup/fetch', 'fetch')->name('diamondqualitygroup.fetch');
        Route::post('diamondqualitygroup/store', 'store')->name('diamondqualitygroup.store');
        Route::get('diamondqualitygroup/edit/{id}', 'edit')->name('diamondqualitygroup.edit');
        Route::post('diamondqualitygroup/update/{id}', 'update')->name('diamondqualitygroup.update');
        Route::delete('diamondqualitygroup/destroy/{id}', 'destroy')->name('diamondqualitygroup.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductClarityMasterController::class)->group(function () {
        Route::get('product-clarity-master/', 'index')->name('ProductClarity.index');
        Route::get('product-clarity-master/fetch', 'fetchData')->name('ProductClarity.fetch');
        Route::post('product-clarity-master/store', 'store')->name('ProductClarity.store');
        Route::get('product-clarity-master/edit/{id}', 'edit')->name('ProductClarity.edit');
        Route::put('product-clarity-master/update/{id}', 'update')->name('ProductClarity.update');
        Route::delete('product-clarity-master/destroy/{id}', 'destroy')->name('ProductClarity.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductColorMasterController::class)->group(function () {
        Route::get('product-color-master/', 'index')->name('product-color.index');
        Route::get('product-color-master/fetch', 'fetch')->name('product-color.fetch');
        Route::post('product-color-master/store', 'store')->name('product-color.store');
        Route::get('product-color-master/edit/{id}', 'edit')->name('product-color.edit');
        Route::post('product-color-master/update/{id}', 'update')->name('product-color.update');
        Route::delete('product-color-master/delete/{id}', 'destroy')->name('product-color.delete');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductCutMasterController::class)->group(function () {
        Route::get('product-cut-master/', 'index')->name('product-cut.index');
        Route::get('product-cut-master/fetch', 'fetch')->name('product-cut.fetch');
        Route::post('product-cut-master/store', 'store')->name('product-cut.store');
        Route::get('product-cut-master/edit/{id}', 'edit')->name('product-cut.edit');
        Route::post('product-cut-master/update/{id}', 'update')->name('product-cut.update');
        Route::delete('product-cut-master/delete/{id}', 'destroy')->name('product-cut.delete');
    });


    Route::controller(\App\Http\Controllers\Jewellery\ProductsToMetalTypeController::class)->group(function () {
        Route::get('ProductsToMetalType/',  'index')->name('ProductsToMetalType.index');
        Route::get('ProductsToMetalType/fetch',  'fetch')->name('ProductsToMetalType.fetch');
        Route::get('ProductsToMetalType/show/{id}',  'show')->name('ProductsToMetalType.show');
        Route::post('ProductsToMetalType/store',  'store')->name('ProductsToMetalType.store');
        Route::put('ProductsToMetalType/update/{id}',  'update')->name('ProductsToMetalType.update');
        Route::delete('ProductsToMetalType/delete/{id}',  'destroy')->name('ProductsToMetalType.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\MetalTypeController::class)->group(function () {
        Route::get('metal-type/', 'index')->name('metaltype.index');
        Route::get('metal-type/fetch', 'fetch')->name('metaltype.fetch');
        Route::post('metal-type/store', 'store')->name('metaltype.store');
        Route::put('metal-type/update/{id}', 'update')->name('metaltype.update');
        Route::delete('metal-type/delete/{id}', 'destroy')->name('metaltype.destroy');
        Route::get('metal-type/show/{id}', 'show')->name('metaltype.show');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductController::class)->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('product.index');
        Route::get('/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('/', [ProductController::class, 'store'])->name('product.store');
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('product.edit');
        Route::put('/{id}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
        Route::get('/get-category-psc-and-collections', [ProductController::class, 'getCategoryPscAndCollections'])->name('get.category.psc.and.collections');
        Route::get('/get-collections-by-category', [ProductController::class, 'getCollectionsByCategory'])->name('get.collections.by.category');
        Route::get('/get-style-groups-by-collection', [ProductController::class, 'getStyleGroupsByCollection'])->name('get.style.groups.by.collection');
    });

    Route::controller(ProductToStyleCategoryController::class)->group(function () {
        Route::get('product-to-style-category/', 'index')->name('ptsc.index');
        Route::post('product-to-style-category/', 'store')->name('ptsc.store');
        Route::get('product-to-style-category/{id}/edit', 'edit')->name('ptsc.edit');
        Route::put('product-to-style-category/{id}', 'update')->name('ptsc.update');
        Route::delete('product-to-style-category/{id}', 'destroy')->name('ptsc.destroy');
    });
    // Route::controller(\App\Http\Controllers\Jewellery\ProductImageController::class)->group(function(){
    //     Route::get('product-image/', [ProductImageController::class, 'index'])->name('product-image.index');
    //     Route::post('product-image/store', [ProductImageController::class, 'store'])->name('product-image.store');
    //     Route::get('product-image/edit/{id}', [ProductImageController::class, 'edit'])->name('product-image.edit');
    //     Route::post('product-image/update/{id}', [ProductImageController::class, 'update'])->name('product-image.update');
    //     Route::delete('product-image/delete/{id}', [ProductImageController::class, 'destroy'])->name('product-image.delete');
    // });

    Route::controller(\App\Http\Controllers\Jewellery\ProductToCategoryController::class)->group(function () {
        Route::get('product-category', 'index')->name('productCategory.index');
        Route::post('product-category', 'store')->name('productCategory.store');
        Route::get('product-category/{products_id}/{categories_id}', 'show')->name('productCategory.show');
        Route::put('product-category/{products_id}/{categories_id}', 'update')->name('productCategory.update');
        Route::delete('product-category/{products_id}/{categories_id}', [ProductToCategoryController::class, 'destroy'])->name('productCategory.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductOptionController::class)->group(function () {
        Route::get('product-options', 'index')->name('product-options.index');
        Route::get('product-options/fetch', 'fetch')->name('product-options.fetch');
        Route::post('product-options/store', 'store')->name('product-options.store');
        Route::get('product-options/edit/{id}', 'edit')->name('product-options.edit');
        Route::post('product-options/update/{id}', 'update')->name('product-options.update');
        Route::delete('product-options/delete/{id}', 'destroy')->name('product-options.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductOptionValueController::class)->group(function () {
        Route::get('product-option-values', 'index')->name('product-option-values.index');
        Route::post('product-option-values/store', 'store')->name('product-option-values.store');
        Route::get('product-option-values/edit/{id}', 'edit')->name('product-option-values.edit');
        Route::post('product-option-values/update/{id}', 'update')->name('product-option-values.update');
        Route::delete('product-option-values/delete/{id}', 'destroy')->name('product-option-values.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductStoneController::class)->group(function () {
        Route::get('product-stone', 'index')->name('product-stone.index');
        Route::post('product-stone/store', 'store')->name('product-stone.store');
        Route::get('product-stone/edit/{id}', 'edit')->name('product-stone.edit');
        Route::post('product-stone/update/{id}', 'update')->name('product-stone.update');
        Route::delete('product-stone/delete/{id}', 'destroy')->name('product-stone.delete');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductStyleCategoryController::class)->group(function () {
        Route::get('product-style-category', 'index')->name('product-style-category.index');
        Route::post('product-style-category', 'store')->name('product-style-category.store');
        Route::get('product-style-category/{id}/edit', 'edit')->name('product-style-category.edit');
        Route::put('product-style-category/{id}', 'update')->name('product-style-category.update');
        Route::delete('product-style-category/{id}', 'destroy')->name('product-style-category.destroy');
        Route::post('product-style-category/status/{id}', 'updateStatus')->name('product-style-category.status');
        Route::post('product-style-category/display/{id}', 'updateDisplay')->name('product-style-category.display');
        Route::post('/product-style-category/{id}/engagement', 'updateEngagement')->name('product-style-category.engagement');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductStyleGroupController::class)->group(function () {
        Route::get('style-groups', [ProductStyleGroupController::class, 'index'])->name('style-groups.index');
        Route::post('style-groups/', [ProductStyleGroupController::class, 'store'])->name('style-groups.store');
        Route::get('style-groups/{id}/edit', [ProductStyleGroupController::class, 'edit'])->name('style-groups.edit');
        Route::put('style-groups/{id}', [ProductStyleGroupController::class, 'update'])->name('style-groups.update');
        Route::delete('style-groups/{id}', [ProductStyleGroupController::class, 'destroy'])->name('style-groups.destroy');
        Route::post('style-groups/update-status', [ProductStyleGroupController::class, 'toggleStatus'])->name('style-groups.toggle-status');
        Route::post('style-groups/update-display', [ProductStyleGroupController::class, 'toggleDisplay'])->name('style-groups.toggle-display');
        Route::post('style-groups/update-sort', [ProductStyleGroupController::class, 'updateSortOrder'])->name('style-groups.update-sort');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ShopZonesController::class)->group(function () {
        Route::get('shop-zones', [ShopZonesController::class, 'index'])->name('shopzones.index');
        Route::post('shop-zones', [ShopZonesController::class, 'store'])->name('shopzones.store');
        Route::get('shop-zones/{id}/edit', [ShopZonesController::class, 'edit'])->name('shopzones.edit');
        Route::put('shop-zones/{id}', [ShopZonesController::class, 'update'])->name('shopzones.update');
        Route::delete('shop-zones/{id}', [ShopZonesController::class, 'destroy'])->name('shopzones.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductAssignOptionController::class)->group(function () {
        Route::get('assign-option/', [ProductAssignOptionController::class, 'index'])->name('assign-option.index');
        Route::get('assign-option/fetch', [ProductAssignOptionController::class, 'fetch'])->name('assign-option.fetch');
        Route::post('assign-option/store', [ProductAssignOptionController::class, 'store'])->name('assign-option.store');
        Route::get('assign-option/show/{productId}/{optionId}', [ProductAssignOptionController::class, 'show'])->name('assign-option.show');
        Route::post('assign-option/update/{productId}/{optionId}', [ProductAssignOptionController::class, 'update'])->name('assign-option.update');
        Route::post('assign-option/delete', [ProductAssignOptionController::class, 'destroy'])->name('assign-option.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\GeoZonesController::class)->group(function () {
        Route::get('geo-zones/', 'index')->name('geo-zones.index');
        Route::get('geo-zones/fetch', 'fetch')->name('geo-zones.fetch');
        Route::post('geo-zones/store', 'store')->name('geo-zones.store');
        Route::get('geo-zones/show/{id}', 'show')->name('geo-zones.show');
        Route::post('geo-zones/update/{id}', 'update')->name('geo-zones.update');
        Route::delete('geo-zones/destroy/{id}', 'destroy')->name('geo-zones.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductToStyleGroupController::class)->group(function () {
        Route::get('product-to-style-group/', [ProductToStyleGroupController::class, 'index'])->name('product-to-style-group.index');
        Route::get('product-to-style-group/fetch', [ProductToStyleGroupController::class, 'fetch'])->name('product-to-style-group.fetch');
        Route::post('product-to-style-group/store', [ProductToStyleGroupController::class, 'store'])->name('product-to-style-group.store');
        Route::get('product-to-style-group/show/{id}', [ProductToStyleGroupController::class, 'show'])->name('product-to-style-group.show');
        Route::post('product-to-style-group/update/{id}', [ProductToStyleGroupController::class, 'update'])->name('product-to-style-group.update');
        Route::delete('product-to-style-group/delete/{id}', [ProductToStyleGroupController::class, 'destroy'])->name('product-to-style-group.delete');
    });

    Route::controller(\App\Http\Controllers\Jewellery\ProductToStoneTypeController::class)->group(function () {
        Route::get('product-to-stone-type', 'index')->name('product-to-stone.index');
        Route::get('product-to-stone-type/fetch', 'fetch')->name('product-to-stone.fetch');
        Route::post('product-to-stone-type/store', 'store')->name('product-to-stone.store');
        Route::get('product-to-stone-type/show/{product}/{stone}', 'show')->name('product-to-stone.show');
        Route::post('product-to-stone-type/update/{product}/{stone}', 'update')->name('product-to-stone.update');
        Route::post('product-to-stone-type/delete', 'destroy')->name('product-to-stone.destroy');
    });

    Route::controller(App\Http\Controllers\Jewellery\ProductToOptionController::class)->group(function () {
        Route::get('product-to-option', 'index')->name('product_to_option.index');
        Route::post('product-to-option/store', 'store')->name('product_to_option.store');
        Route::get('product-to-option/show/{id}', 'show')->name('product_to_option.show');
        Route::post('product-to-option/update/{id}', 'update')->name('product_to_option.update');
        Route::post('product-to-option/delete', 'destroy')->name('product_to_option.delete');
    });

    Route::controller(CountryController::class)->group(function () {
        Route::get('countries', 'index')->name('countries.index'); // View + AJAX data
        Route::post('countries/store', 'store')->name('countries.store');
        Route::get('countries/{id}/edit', 'edit')->name('countries.edit');
        Route::delete('countries/{id}', 'destroy')->name('countries.destroy');
    });

    Route::controller(ProductToShapeController::class)->group(function () {
        Route::get('products-to-shape', 'index')->name('products-to-shape.index');
        Route::get('products-to-shape/fetch', 'fetch')->name('products-to-shape.fetch');
        Route::post('products-to-shape', 'store')->name('products-to-shape.store');
        Route::get('products-to-shape/show/{id}', 'show')->name('products-to-shape.show');
        Route::put('products-to-shape/update/{id}', 'update')->name('products-to-shape.update');
        Route::delete('products-to-shape/delete/{id}', 'destroy')->name('products-to-shape.delete');
    });

    Route::controller(ShopTaxClassesController::class)->group(function () {
        Route::get('tax-classes', 'index')->name('tax-classes.index');
        Route::get('tax-classes/data', 'getData')->name('tax-classes.data');
        Route::post('tax-classes', 'store')->name('tax-classes.store');
        Route::get('tax-classes/{id}', 'show')->name('tax-classes.show');
        Route::put('tax-classes/{id}', 'update')->name('tax-classes.update');
        Route::delete('tax-classes/{id}', 'destroy')->name('tax-classes.destroy');
    });

    Route::controller(ShopTaxRateController::class)->group(function () {
        Route::get('tax-rates/', [ShopTaxRateController::class, 'index'])->name('tax-rates.index');
        Route::get('tax-rates/data', [ShopTaxRateController::class, 'getData'])->name('tax-rates.data');
        Route::post('tax-rates/', [ShopTaxRateController::class, 'store'])->name('tax-rates.store');
        Route::get('tax-rates/{id}', [ShopTaxRateController::class, 'show'])->name('tax-rates.show');
        Route::put('tax-rates/{id}', [ShopTaxRateController::class, 'update'])->name('tax-rates.update');
        Route::delete('tax-rates/{id}', [ShopTaxRateController::class, 'destroy'])->name('tax-rates.destroy');
    });

    Route::controller(\App\Http\Controllers\Jewellery\CollectionController::class)->prefix('jewellery')->group(function () {
        Route::get('collections/', 'index')->name('collections.index');
        Route::post('collections/', 'store')->name('collections.store');
        Route::get('collections/edit/{id}', 'edit')->name('collections.edit');
        Route::post('collections/update/{id}', 'update')->name('collections.update');
        Route::delete('collections/destroy/{id}', 'destroy')->name('collections.destroy');
        Route::post('collections/status/{id}', 'updateStatus')->name('collections.status');
        Route::post('collections/display/{id}', 'updateDisplay')->name('collections.display');
    });

    Route::controller(CouponController::class)->group(function () {
        Route::get('/coupons', [CouponController::class, 'index'])->name('admin.coupons.index');
        Route::post('/coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
        Route::get('coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('admin.coupons.edit');
        Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('admin.coupons.update');
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('admin.coupons.destroy');
        Route::post('/coupons/{coupon}/status', [CouponController::class, 'updateStatus'])->name('admin.coupons.status');
    });

    Route::controller(\App\Http\Controllers\Jewellery\MetalPriceController::class)->group(function () {
        Route::get('metal-prices',  'index')->name('metal-prices.index');
        Route::post('metal-prices',  'store')->name('metal-prices.store');
        Route::get('metal-prices/{id}/edit',  'edit')->name('metal-prices.edit');
        Route::put('metal-prices/{id}',  'update')->name('metal-prices.update');
        Route::delete('/destroy/{id}', 'destroy')->name('metal-prices.destroy');
        Route::post('/update-products', 'updateProductPrices')->name('metal-prices.update-products');
        Route::get('/update-products-form', 'showPriceUpdateForm')->name('metal-prices.update-products.form');
    });

    Route::controller(\App\Http\Controllers\Jewellery\AppointmentController::class)->group(function () {
        Route::get('appointments', 'index')->name('admin.appointments.index');
        Route::get('appointments/fetch', 'fetch')->name('admin.appointments.fetch');
        Route::get('appointments/show/{id}', 'show')->name('admin.appointments.show');
        Route::delete('appointments/delete/{id}', 'destroy')->name('admin.appointments.delete');
    });



    Route::controller(\App\Http\Controllers\Jewellery\BlogController::class)->group(function () {
        Route::get('blogs', 'index')->name('admin.blogs.index');
        Route::get('blogs/fetch', 'fetch')->name('admin.blogs.fetch');
        Route::post('blogs/store', 'store')->name('admin.blogs.store');
        Route::get('blogs/show/{id}', 'show')->name('admin.blogs.show');
        Route::post('blogs/update/{id}', 'update')->name('admin.blogs.update');
        Route::delete('/admin/blogs/delete/{id}', 'destroy')->name('admin.blogs.delete');
    });

    // Enquiry Routes
    Route::controller(\App\Http\Controllers\Jewellery\EnquiryController::class)->group(function () {
        Route::get('/enquiries', 'index')->name('enquiries.index');
        Route::get('/enquiries/{id}', 'show')->name('enquiries.show');
        Route::delete('/enquiries/{id}', 'destroy')->name('enquiries.destroy');
    });
    
});

Route::prefix('admin/contact-us')->group(function () {
    Route::get('/', [ContactUsController::class, 'index'])->name('admin.contactus.index');
    Route::get('/fetch', [ContactUsController::class, 'fetch'])->name('admin.contactus.fetch');
    Route::get('/stats', [ContactUsController::class, 'getStats'])->name('admin.contactus.stats');
    Route::get('/show/{id}', [ContactUsController::class, 'show'])->name('admin.contactus.show');
    Route::post('/respond/{id}', [ContactUsController::class, 'respond'])->name('admin.contactus.respond');
    Route::delete('/delete/{id}', [ContactUsController::class, 'destroy'])->name('admin.contactus.delete');
});
 