<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);


Route::group(['middleware' => ['jwt']], function () {
    Route::post('logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout']);
    Route::post('refresh', [\App\Http\Controllers\Auth\AuthController::class, 'refresh']);
    Route::post('me', [\App\Http\Controllers\Auth\AuthController::class, 'me']);
    Route::get('app-supporting-data', [\App\Http\Controllers\Common\HelperController::class, 'appSupportingData']);
});


Route::group(['middleware' => ['jwt:api']], function () {

    // ADMIN USERS
    Route::group(['prefix' => 'user'],function () {
        Route::post('list', [\App\Http\Controllers\Admin\Users\AdminUserController::class, 'index']);
        //User Modal Data
        Route::get('modal',[\App\Http\Controllers\Admin\Users\AdminUserController::class,'userModalData']);
        Route::post('add', [\App\Http\Controllers\Admin\Users\AdminUserController::class, 'store']);
        Route::get('get-user-info/{UserId}',[\App\Http\Controllers\Admin\Users\AdminUserController::class,'getUserInfo']);
        Route::post('update', [\App\Http\Controllers\Admin\Users\AdminUserController::class, 'update']);
        Route::post('password-change',[\App\Http\Controllers\Common\HelperController::class,'passwordChange']);
    });

     //PRODUCTION
        Route::group(['prefix' =>'production'],function () {
        Route::post('list', [\App\Http\Controllers\Production\ProductionController::class, 'index']);
        Route::get('supporting-data', [\App\Http\Controllers\Production\ProductionController::class, 'getSupportingData']);
        Route::post('category-wise-item', [\App\Http\Controllers\Production\ProductionController::class, 'getCategoryWiseItemData']);
        Route::post('add', [\App\Http\Controllers\Production\ProductionController::class, 'store']);
        Route::get('get-production-info/{productionCode}', [\App\Http\Controllers\Production\ProductionController::class, 'getProductionInfo']);
        Route::post('update', [\App\Http\Controllers\Production\ProductionController::class, 'update']);
        Route::post('return', [\App\Http\Controllers\Production\ProductionController::class, 'returnProducts']);
     });

    //PURCHASE
     Route::group(['prefix' =>'purchase'],function () {
         Route::post('list', [\App\Http\Controllers\Purchase\PurchaseController::class, 'index']);
         Route::get('supporting-data', [\App\Http\Controllers\Purchase\PurchaseController::class, 'getSupportingData']);
         Route::post('category-wise-item', [\App\Http\Controllers\Purchase\PurchaseController::class, 'getCategoryWiseItemData']);
         Route::post('add', [\App\Http\Controllers\Purchase\PurchaseController::class, 'store']);
         Route::get('get-purchase-info/{purchaseCode}', [\App\Http\Controllers\Purchase\PurchaseController::class, 'getPurchaseInfo']);
         Route::post('update', [\App\Http\Controllers\Purchase\PurchaseController::class, 'update']);
         Route::post('return', [\App\Http\Controllers\Purchase\PurchaseController::class, 'returnProducts']);
     });

    //Sales
    Route::group(['prefix' =>'sales'],function () {
        Route::post('list', [\App\Http\Controllers\Sales\SalesController::class, 'index']);
        Route::get('supporting-data', [\App\Http\Controllers\Sales\SalesController::class, 'getSupportingData']);
        Route::post('category-wise-item', [\App\Http\Controllers\Sales\SalesController::class, 'getCategoryWiseItemData']);
        Route::post('category-wise-item-stock', [\App\Http\Controllers\Sales\SalesController::class, 'getCategoryWiseItemStock']);
        Route::post('add', [\App\Http\Controllers\Sales\SalesController::class, 'store']);
        Route::get('get-sales-info/{salesCode}', [\App\Http\Controllers\Sales\SalesController::class, 'getSalesInfo']);
        Route::post('update', [\App\Http\Controllers\Sales\SalesController::class, 'update']);
//        Route::post('return', [\App\Http\Controllers\Purchase\PurchaseController::class, 'returnProducts']);
    });


});

