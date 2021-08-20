<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Brand\BrandController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Model\MModelController;
use App\Http\Controllers\Moderator\ModeratorController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\RRequest\RequestController;
use App\Http\Controllers\Shipper\ShipperController;
use App\Http\Controllers\Supplier\SupplierController;
use App\Http\Controllers\UsersController;
use App\Models\BridgeTables\ModeratorSupplier;
use App\Models\RRequest\Request;
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

/** NOTE MIDDLEWARE FOR AUTH SHOULD BE ADDED INSIDE THE CONTROLLER*/
Route::prefix('admin')->group(function () {
    Route::apiResource('/admin-operations', AdminController::class);
});


/**
 * ==============
 * To make the URL path like this: http://localhost:800/api/admin/admin-operations
 * For more arrangement
 * ==============
 */



/** NOTE MIDDLEWARE FOR AUTH SHOULD BE ADDED INSIDE THE CONTROLLER */
Route::prefix('moderator')->group(function () {
    Route::apiResource('/moderator-operations', ModeratorController::class);
    //To update a client
    Route::patch("/update-client", [ModeratorController::class, 'updateClient']);
    //To update a supplier
    Route::patch("/update-supplier", [ModeratorController::class, 'updateSupplier']);
    
    //To accept a new supplier
    Route::patch("/verify-supplier", [ModeratorController::class, 'verifySupplier']);
    //To suspend a supplier
    Route::patch("/suspend-supplier", [ModeratorController::class, 'suspendSupplier']);
    //To cancel a supplier
    Route::patch("/unverify-supplier", [ModeratorController::class, 'unverifySupplier']);

    //To get the last five records from the tables
    Route::get("/last-five-records", [ModeratorController::class, 'lastFiveRecords']);

    //To get all records in the db together
    Route::get('/all-records/{status?}', [ModeratorController::class, 'allRecords']);
    
});

/**
 * ==============
 * To make the URL path like this: http://localhost:800/api/moderator/moderator-operations
 * For more arrangement
 * ==============
 */



/** NOTE MIDDLEWARE FOR AUTH SHOULD BE ADDED INSIDE THE CONTROLLER*/
Route::prefix('supplier')->group(function () {
    Route::apiResource('/supplier-operations', SupplierController::class);
    Route::get("/all-requests", [SupplierController::class, 'allRequests']);
});

 /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/supplier/supplier-operations
 * For more arrangement
 * ==============
 */


/** NOTE MIDDLEWARE FOR AUTH SHOULD BE ADDED IN SOME ROUTE INSIDE THE CONTROLLER */
Route::prefix('client')->group(function () {
    Route::apiResource('/client-operations', ClientController::class);
    Route::get("/all-requests", [ClientController::class, 'allRequests']);
    Route::get("/single-request", [ClientController::class, 'singleRequest']);
});

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/client/client-operations
 * For more arrangement
 * ==============
 */


Route::prefix('shipper')->group(function () {
    Route::apiResource('/shipper-operations', ShipperController::class);
});

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/shipper/shipper-operations
 * For more arrangement
 * ==============
 */

Route::prefix('brand')->group(function () {
    Route::apiResource('/brand-operations', BrandController::class);
});




  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/model/model-operations
 * For more arrangement
 * ==============
 */

Route::prefix('request')->group(function () {
    Route::apiResource('/request-operations', RequestController::class);
    //To update the amount of each supplier to be displayed to the client er to choose from
    Route::patch("/update-amounts", [RequestController::class, 'updateAmounts']);
    //To Fetch client's requests with offsetS
    Route::get('/request-operations/{clientId}/{offset?}', [RequestController::class, 'show']);

    //To show full data of each supplier with his amount offer 
    Route::get("/show-amounts", [RequestController::class, 'showFullAmounts']);
    
    //To update the request with the final amount + supplier id which has the best offer
    Route::patch("/select-best-price", [RequestController::class, 'selectBestPrice']);

    //To update the request status when it's on the way to the shipper
    Route::patch("/move-to-shipper", [RequestController::class, 'moveToShipper']);

    //To update the request status when it's canceled
    Route::patch("/cancel-request", [RequestController::class, 'cancelRequest']);

    //To update the request status when it's completed
    Route::patch("/complete-request", [RequestController::class, 'complete']);

    //To Fetch the specific request status
    Route::post("/detailed-request", [RequestController::class, 'detailedRequest']);
    
    //To fetch pending requests
    Route::get("/pending-requests/{offset?}", [RequestController::class, 'pendingRequests']);

    //To fetch only assigned requests to specific supplier
    Route::get('/assigned-requests/{supplierId}/{offset?}', [RequestController::class, 'assignedRequests']);

    //To cancel request from the supplier
    Route::patch('/cancel-request-supplier', [RequestController::class, 'cancelRequestSupplier']);

    //To fetch single request
    Route::get('/single-request/{requestId}', [RequestController::class, 'singleRequest']);
});

Route::prefix('/payment')->group(function(){
    Route::apiResource('/payment-operations', PaymentController::class);
    Route::patch('/refund', [PaymentController::class, "refund"]);
});


Route::patch('/add-token', [UsersController::class, 'addToken']);   

Route::patch('/logout', [UsersController::class, 'logout']);

Route::patch('/store-remember-token', [UsersController::class, 'storeRememberToken']);

Route::patch('/update-password', [UsersController::class, 'updatePassword']);


  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/request/request-operations
 * For more arrangement
 * ==============
 */
