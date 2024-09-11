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

    //Dashboard
    Route::get('dashboard-data', [\App\Http\Controllers\Common\DashboardController::class, 'index']);

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
        Route::post('category-wise-item-stock', [\App\Http\Controllers\Sales\SalesController::class, 'getCategoryWiseTotalItemStock']);
        Route::post('add', [\App\Http\Controllers\Sales\SalesController::class, 'store']);
        Route::get('get-sales-info/{salesCode}', [\App\Http\Controllers\Sales\SalesController::class, 'getSalesInfo']);
        Route::post('update', [\App\Http\Controllers\Sales\SalesController::class, 'update']);
        Route::post('return', [\App\Http\Controllers\Sales\SalesController::class, 'returnProducts']);
    });


    Route::group(['prefix' =>'vaccineschedule'],function () {
        Route::get('supporting-data', [\App\Http\Controllers\VaccineSchedule\VaccineScheduleController::class, 'getSupportingData']);
        Route::post('list', [\App\Http\Controllers\VaccineSchedule\VaccineScheduleController::class, 'index']);
        Route::post('add', [\App\Http\Controllers\VaccineSchedule\VaccineScheduleController::class, 'doStore']);
    });

    //Transfer
    Route::group(['prefix' =>'transfer'],function () {
        Route::post('list', [\App\Http\Controllers\Transfer\TransferController::class, 'index']);
        Route::get('supporting-data', [\App\Http\Controllers\Transfer\TransferController::class, 'getSupportingData']);
        Route::post('category-wise-item', [\App\Http\Controllers\Transfer\TransferController::class, 'getCategoryWiseItemData']);
        Route::post('check-stock-item-wise', [\App\Http\Controllers\Transfer\TransferController::class, 'checkItemWiseStockData']);
        Route::post('add', [\App\Http\Controllers\Transfer\TransferController::class, 'store']);
        Route::get('get-production-info/{productionCode}', [\App\Http\Controllers\Transfer\TransferController::class, 'getProductionInfo']);
        Route::post('update', [\App\Http\Controllers\Transfer\TransferController::class, 'update']);

        Route::post('medicine-list', [\App\Http\Controllers\Transfer\MedicineTransferController::class, 'index']);
        Route::get('medicine-supporting-data', [\App\Http\Controllers\Transfer\MedicineTransferController::class, 'getSupportingData']);
        Route::post('medicine-category-wise-item', [\App\Http\Controllers\Transfer\MedicineTransferController::class, 'getCategoryWiseItemData']);
        Route::post('medicine-check-stock-item-wise', [\App\Http\Controllers\Transfer\MedicineTransferController::class, 'checkItemWiseStockData']);
        Route::get('get-existing-medicine-info/{transferCode}', [\App\Http\Controllers\Transfer\MedicineTransferController::class, 'getExistingMedicineTransferInfo']);
        Route::post('add-medicine', [\App\Http\Controllers\Transfer\MedicineTransferController::class, 'store']);
        Route::post('update-medicine', [\App\Http\Controllers\Transfer\MedicineTransferController::class, 'update']);
     });

    //Customer
    Route::group(['prefix' =>'customers'],function () {
        Route::post('list', [\App\Http\Controllers\Customer\CustomerController::class, 'index']);
        Route::post('add', [\App\Http\Controllers\Customer\CustomerController::class, 'store']);
        Route::get('get-customer-info/{customerCode}',[\App\Http\Controllers\Customer\CustomerController::class,'getCustomerInfo']);
        Route::post('update', [\App\Http\Controllers\Customer\CustomerController::class, 'update']);
        Route::post('due-list', [\App\Http\Controllers\Customer\CustomerDueController::class, 'index']);
        Route::post('due-list-update', [\App\Http\Controllers\Customer\CustomerDueController::class, 'dueListUpdate']);
    });
    Route::group(['prefix' =>'report'],function () {
        Route::post('daily-production', [\App\Http\Controllers\Report\DailyProductionController::class, 'index']);
        Route::get('daily-production-supporting-data', [\App\Http\Controllers\Report\DailyProductionController::class, 'getSupportingData']);
        Route::post('daily-sales', [\App\Http\Controllers\Report\DailySalesController::class, 'index']);
        Route::post('vaccine-report', [\App\Http\Controllers\Report\VaccineReportController::class, 'index']);
        Route::post('customer-wise-sales', [\App\Http\Controllers\Report\CustomerWiseSalesController::class, 'index']);
        Route::get('daily-sales-supporting-data', [\App\Http\Controllers\Report\DailySalesController::class, 'getSupportingData']);
        Route::post('current-stock', [\App\Http\Controllers\Report\CurrentStockController::class, 'index']);
        Route::post('item-balance', [\App\Http\Controllers\Report\ItemBalanceController::class, 'index']);
        Route::post('report-expenses', [\App\Http\Controllers\Report\ExpenseController::class, 'index']);
        Route::post('location-wise-pl', [\App\Http\Controllers\Report\LocationWisePLController::class, 'index']);
        Route::post('daily-purchase-report', [\App\Http\Controllers\Report\DailyPurchaseController::class, 'index']);
    });
    Route::group(['prefix' =>'setup'],function () {
        //Category
        Route::post('category-list', [\App\Http\Controllers\Setup\CategorySetupController::class, 'index']);
        Route::post('category-add', [\App\Http\Controllers\Setup\CategorySetupController::class, 'store']);
        Route::get('get-category-info/{categoryCode}',[\App\Http\Controllers\Setup\CategorySetupController::class,'getCategoryInfo']);
        Route::post('category-update', [\App\Http\Controllers\Setup\CategorySetupController::class, 'update']);

        //Location
        Route::post('location-list', [\App\Http\Controllers\Setup\LocationSetupController::class, 'index']);
        Route::post('location-add', [\App\Http\Controllers\Setup\LocationSetupController::class, 'store']);
        Route::get('get-location-info/{locationCode}',[\App\Http\Controllers\Setup\LocationSetupController::class,'getLocationInfo']);
        Route::post('location-update', [\App\Http\Controllers\Setup\LocationSetupController::class, 'update']);

        //Category Location
        Route::post('category-location-list', [\App\Http\Controllers\Setup\CategoryLocationSetupController::class, 'index']);
        Route::get('get-category-location-supporting-data', [\App\Http\Controllers\Setup\CategoryLocationSetupController::class, 'getSupportingData']);
        Route::post('category-location-add', [\App\Http\Controllers\Setup\CategoryLocationSetupController::class, 'store']);
        Route::get('get-category-location-info/{categoryCode}/{locationCode}',[\App\Http\Controllers\Setup\CategoryLocationSetupController::class,'getCategoryLocationInfo']);
        Route::post('category-location-update', [\App\Http\Controllers\Setup\CategoryLocationSetupController::class, 'update']);

		//Item
        Route::post('item-list', [\App\Http\Controllers\Setup\ItemSetupController::class, 'index']);
        Route::get('get-item-supporting-data', [\App\Http\Controllers\Setup\ItemSetupController::class, 'getSupportingData']);
        Route::post('item-add', [\App\Http\Controllers\Setup\ItemSetupController::class, 'store']);
        Route::get('get-item-info/{itemCodeCode}',[\App\Http\Controllers\Setup\ItemSetupController::class,'getItemInfo']);
        Route::post('item-update', [\App\Http\Controllers\Setup\ItemSetupController::class, 'update']);

        //Expense Head
        Route::post('expense-head-list', [\App\Http\Controllers\Setup\ExpenseHeadController::class, 'index']);
        Route::post('expense-head-add', [\App\Http\Controllers\Setup\ExpenseHeadController::class, 'store']);
        Route::get('get-expense-head-info/{headCode}',[\App\Http\Controllers\Setup\ExpenseHeadController::class,'getExpenseHeadInfo']);
        Route::post('expense-head-update', [\App\Http\Controllers\Setup\ExpenseHeadController::class, 'update']);

    });

    //Expense
    Route::group(['prefix' =>'expense'],function () {
        Route::post('expense-list', [\App\Http\Controllers\Expense\ExpenseController::class, 'index']);
        Route::get('supporting-data', [\App\Http\Controllers\Expense\ExpenseController::class, 'getSupportingData']);
        Route::post('category-wise-item', [\App\Http\Controllers\Expense\ExpenseController::class, 'getCategoryWiseItemData']);
        Route::post('add', [\App\Http\Controllers\Expense\ExpenseController::class, 'store']);
        Route::get('get-expense-info/{expenseCode}', [\App\Http\Controllers\Expense\ExpenseController::class, 'getExpenseInfo']);
        Route::post('update', [\App\Http\Controllers\Expense\ExpenseController::class, 'update']);
        Route::post('add-month-closing', [\App\Http\Controllers\Expense\ExpenseController::class, 'addMonthClosing']);

    });


});

