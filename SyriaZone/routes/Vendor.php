<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Category\SubCategortController;
use App\Http\Controllers\Category\CategoryVendorController;
use App\Http\Controllers\Product\ProductController;
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

Route::middleware(['auth:sanctum', 'vendor'])->group(function () {


    Route::get('dashboard', [VendorProfileController::class, 'VendorDashboard']);


    Route::prefix('categories')->group(function () {
        Route::get('get_all', [CategoryController::class, 'index']);
        Route::get('show/{id}', [CategoryController::class, 'show']);
        Route::post('select_category', [CategoryVendorController::class, 'store']);

    });

    Route::prefix('subcategories')->group(function () {
        Route::get('get_by_category/{category_id}/', [SubCategortController::class, 'get_by_category']); // عرض جميع الفئات الفرعية
        Route::get('show/{id}', [SubCategortController::class, 'show']); // عرض الفئة الفرعية حسب ID

    });

    Route::prefix('product')->group(function () {
        Route::get('/get_all', [ProductController::class, 'getVendorProducts']);
        Route::get('show/{product_id}', [ProductController::class, 'getProductById']);
        Route::post('store', [ProductController::class, 'store']);
        Route::post('update/{product_id}', [ProductController::class, 'update']);
        Route::delete('delete/{product_id}', [ProductController::class, 'destroy']);

    });


    Route::prefix('orders')->group(function () {

        Route::get('get_all', [VendorController::class, 'getVendorOrders']);
        Route::get('get_all_by_status', [VendorController::class, 'getVendorOrdersByStatus']);
        Route::get('/get_all_by_produt_id/{product_id}', [VendorController::class, 'getOrdersByProductId']);
        Route::get('/get_all_by_user_id/{user_id}', [VendorController::class, 'getVendorOrdersByOrderProductStatus']);

    });


    Route::prefix('commissions')->group(function () {
        Route::get('calculate', [CommissionController::class, 'getVendorCommission']);
        Route::get('calculate/Product/{poduct_id}', [CommissionController::class, 'calculateByProduct']);

    });


});
