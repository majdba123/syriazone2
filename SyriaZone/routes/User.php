<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactController;

use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Category\SubCategortController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Product\RatingController;
use App\Http\Controllers\Vendor\AnswerRatingController;

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

Route::middleware(['auth:sanctum'])->group(function () {



  /*  Route::post('/profile/store', [ProfileController::class, 'storeProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::put('/my_info/update', [ProfileController::class, 'UpdateInfo']);
    Route::get('/my_info/get', [ProfileController::class, 'getUserInfo']);

    */
    Route::get('dashboard', [UserController::class, 'dashboard']);

    Route::prefix('categories')->group(function () {
        Route::get('get_all', [CategoryController::class, 'index']);
        Route::get('show/{id}', [CategoryController::class, 'show']);
    });


    Route::prefix('subcategories')->group(function () {
        Route::get('getall/', [SubCategortController::class, 'index']); // عرض جميع الفئات الفرعية
        Route::get('get_by_category/{category_id}/', [SubCategortController::class, 'get_by_category']); // عرض جميع الفئات الفرعية
        Route::get('show/{id}', [SubCategortController::class, 'show']); // عرض الفئة الفرعية حسب ID

    });

    Route::prefix('product')->group(function () {
        Route::get('/category/{categoryId}', [ProductController::class, 'getProductsByCategory']);
        Route::get('/subcategory/{subCategoryId}', [ProductController::class, 'getProductsBySubCategory']);
        Route::get('/search', [ProductController::class, 'searchProducts']);
        Route::get('/vendor/{vendorId}', [ProductController::class, 'getProductsByVendor']);
        });


        Route::prefix('orders')->group(function () {
            Route::post('/process_order', [OrderController::class, 'process_order']);
            Route::post('/store', [OrderController::class, 'createOrder']);
            Route::get('/ByStatus', [OrderController::class, 'getUserOrders']);
            Route::get('/get_product/{order_id}', [OrderController::class, 'getProductOrder']);
        });


    Route::get('/rate/get_all', [RatingController::class, 'getUserRatings']);
    Route::post('/rate/store/{Product_id}', [RatingController::class, 'rateProduct']);
    Route::put('/rate/update/{Rate_id}', [RatingController::class, 'Update']);
    Route::delete('/rate/delete/{Rate_id}', [RatingController::class, 'destroy']);
    Route::get('/product/rating/{product_id}', [RatingController::class, 'getRateProduct']);


    Route::prefix('profile')->group(function () {
        Route::post('/update', [UserController::class, 'updateProfile']);
        Route::get('/my_info', [UserController::class, 'getProfile']);
    });

    Route::prefix('contact')->group(function () {
        Route::post('/store', [ContactController::class, 'store']);
        Route::get('/my_contact', [ContactController::class, 'myContacts']);

    });


});
