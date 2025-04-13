<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Category\SubCategortController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\VendorProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('categories/get_all', [CategoryController::class, 'index']);
    Route::post('categories/store', [CategoryController::class, 'store']);
    Route::put('categories/update/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/delete/{id}', [CategoryController::class, 'destroy']);
    Route::get('/categories/show/{id}', [CategoryController::class, 'show']);

    Route::prefix('subcategories')->group(function () {
        Route::get('getall/', [SubCategortController::class, 'index']); // عرض جميع الفئات الفرعية
        Route::get('get_by_category/{category_id}/', [SubCategortController::class, 'get_by_category']); // عرض جميع الفئات الفرعية
        Route::get('show/{id}', [SubCategortController::class, 'show']); // عرض الفئة الفرعية حسب ID
        Route::post('store', [SubCategortController::class, 'store']); // إضافة فئة فرعية جديدة
        Route::put('update/{id}', [SubCategortController::class, 'update']); // تعديل الفئة الفرعية حسب ID
        Route::delete('delete/{id}', [SubCategortController::class, 'destroy']); // حذف الفئة الفرعية حسب ID
    });


    Route::prefix('vendores')->group(function () {
        Route::post('store', [AdminController::class, 'createUserAndVendor']);
        Route::put('update/{vendor_id}', [AdminController::class, 'updateUserAndVendor']);
        Route::post('update_status/{vendor_id}', [AdminController::class, 'updateVendorStatus']);
        Route::get('/get_by_status', [AdminController::class, 'getVendorsByStatus']);
        Route::get('/show_info/{vendor_id}', [AdminController::class, 'getVendorInfo']);
        Route::get('get_statical_commission/{vendor_id}', [VendorProfileController::class, 'VendorDashboard']);


    });


    Route::prefix('product')->group(function () {

        Route::get('/category/{categoryId}', [ProductController::class, 'getProductsByCategory']);
        Route::get('/subcategory/{subCategoryId}', [ProductController::class, 'getProductsBySubCategory']);
        Route::get('/search', [ProductController::class, 'searchProducts']);
        Route::get('/vendor/{vendorId}', [ProductController::class, 'getProductsByVendor']);

        });


    Route::prefix('orders')->group(function () {

            Route::get('get_all/ByVendor/{id}', [VendorController::class, 'getVendorOrders']);
            Route::get('get_all_by_status', [AdminController::class, 'getOrdersByStatus']);
            Route::get('get_all_by_price', [AdminController::class, 'getOrdersByPriceRange']);
            Route::get('/get_all_by_produt_id/{product_id}', [AdminController::class, 'getOrdersByProduct']);
            Route::get('/get_all_by_user_id/{user_id}', [AdminController::class, 'getOrdersByUser']);
            Route::get('/get_all_by_category/{category_id}', [AdminController::class, 'getOrdersByCategory']);
            Route::get('/get_all_by_sub_category/{sub_category_id}', [AdminController::class, 'getOrdersBySubCategory']);

        });


    Route::prefix('commissions')->group(function () {
            Route::get('calculate/{vendor_id}', [CommissionController::class, 'getVendorCommission']);
            Route::get('calculate/Vendor_Product/{poduct_id}', [CommissionController::class, 'calculateByProduct']);

        });



});
