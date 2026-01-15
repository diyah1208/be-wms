<?php

use Illuminate\Support\Facades\Route;
use App\Models\UserModel;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\MaterialRequestController;
// use App\Http\Controllers\Api\MaterialRequestItemController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReceiveController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\CustomerController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});


Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $user = UserModel::findOrFail($id);

    if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, 'Invalid verification link');
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified']);
    }

    $user->markEmailAsVerified();
    return response()->json(['message' => 'Email verified successfully']);
})->middleware('signed')->name('verification.verify');


Route::middleware('api')->post('/mr/sign', [MaterialRequestController::class, 'sign']);
Route::delete(
    '/mr/{kode}/signature',
    [MaterialRequestController::class, 'clearSignature']
)->where('kode', '.*');

Route::middleware('api')->post('/pr/sign', [PurchaseRequestController::class, 'sign']);
Route::delete(
    '/pr/{kode}/signature',
    [PurchaseRequestController::class, 'clearSignature']
)->where('kode', '.*');

Route::middleware('api')->post('/po/sign', [PurchaseOrderController::class, 'sign']);
Route::delete(
    '/po/{kode}/signature',
    [PurchaseOrderController::class, 'clearSignature']
)->where('kode', '.*');

Route::middleware('auth:sanctum')->group(function () {


    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/email-verified', [AuthController::class, 'emailVerified']);
        Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::put('/{id}/status', [UserController::class, 'updateStatus']);
    });

    // ===== BARANG =====
    Route::prefix('barang')->group(function () {
        Route::get('/', [BarangController::class, 'index']);
        Route::post('/', [BarangController::class, 'store']);
        Route::get('/{id}', [BarangController::class, 'show']);
        Route::put('/{id}', [BarangController::class, 'update']);
    });

    // ===== STOCK =====
    Route::prefix('stock')->group(function () {
        Route::get('/', [StockController::class, 'index']);
        Route::post('/', [StockController::class, 'store']);
    });

    // ===== MATERIAL REQUEST (MR) =====
    Route::prefix('mr')->group(function () {
        Route::get('/', [MaterialRequestController::class, 'index']);
        Route::post('/', [MaterialRequestController::class, 'store']);
        Route::get('/generate-kode', [MaterialRequestController::class, 'generateKode']);
        Route::get('/open', [MaterialRequestController::class, 'getOpenMR']);
        Route::get('/kode/{kode}', [MaterialRequestController::class, 'showKode'])->where('kode', '.*');
        Route::get('/{id}', [MaterialRequestController::class, 'show']);
        Route::put('/{id}', [MaterialRequestController::class, 'update']);
        Route::delete('/{id}', [MaterialRequestController::class, 'destroy']);

        // // MR ITEMS
        // Route::get('/{mr_id}/items', [MaterialRequestItemController::class, 'index']);
        // Route::post('/{mr_id}/items', [MaterialRequestItemController::class, 'store']);
        // Route::put('/items/{item_id}', [MaterialRequestItemController::class, 'update']);
        // Route::delete('/items/{item_id}', [MaterialRequestItemController::class, 'destroy']);


    });

    // ===== PURCHASE REQUEST (PR) =====
    Route::prefix('pr')->group(function () {
        Route::get('/', [PurchaseRequestController::class, 'index']);
        Route::post('/', [PurchaseRequestController::class, 'store']);
        Route::get('/kode/{kode}', [PurchaseRequestController::class, 'showKode'])->where('kode', '.*');
        Route::get('/open', [PurchaseOrderController::class, 'getPrOpen']);
        Route::get('/{id}', [PurchaseRequestController::class, 'show']);
        Route::put('/{id}', [PurchaseRequestController::class, 'update']);
        Route::delete('/{id}', [PurchaseRequestController::class, 'destroy']);
        Route::get('/pr/{kode}', [PurchaseRequestController::class, 'show']);
        
    });

    // ===== PURCHASE ORDER (PO) =====
    Route::prefix('po')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index']);
        Route::post('/', [PurchaseOrderController::class, 'store']);
        Route::get('/kode/{kode}', [PurchaseOrderController::class, 'showKode'])->where('kode', '.*');
        Route::get('/{id}', [PurchaseOrderController::class, 'show']);
        Route::put('/{id}', [PurchaseOrderController::class, 'update']);
        Route::delete('/{id}', [PurchaseOrderController::class, 'destroy']);
    });

    // ===== RECEIVE =====
    Route::prefix('receive')->group(function () {
        Route::get('/', [ReceiveController::class, 'index']);
        Route::post('/', [ReceiveController::class, 'store']);
        Route::get('/history', [ReceiveController::class, 'history']);
        Route::get('/purchase-orders', [ReceiveController::class, 'getPoPurchased']);
        Route::get('/kode/{kode}', [ReceiveController::class, 'showByKode'])->where('kode', '.*');
        Route::get('/{id}', [ReceiveController::class, 'show']);
    });

    // ===== DELIVERY =====
    Route::prefix('deliveries')->group(function () {
        Route::get('/', [DeliveryController::class, 'index']);
        Route::post('/', [DeliveryController::class, 'store']);
        Route::get('/{id}', [DeliveryController::class, 'show']);
        Route::get('/kode/{kode}', [DeliveryController::class, 'showKode']);
        Route::put('/kode/{kode}', [DeliveryController::class, 'update']);
        Route::patch('/kode/{kode}/status', [DeliveryController::class, 'updateStatus']);
    });

Route::prefix('vendors')->group(function () {
    Route::get('/', [VendorController::class, 'index']);        // READ
    Route::get('{id}', [VendorController::class, 'show']);      // READ detail
    Route::post('/', [VendorController::class, 'store']);       // CREATE
    Route::put('{id}', [VendorController::class, 'update']);    // UPDATE
    Route::delete('{id}', [VendorController::class, 'destroy']); // DELETE
    Route::put('{id}/toggle', [VendorController::class, 'toggleStatus']); // suspend
});
Route::prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::put('/{id}', [CustomerController::class, 'update']);
    Route::put('/{id}/toggle', [CustomerController::class, 'toggleStatus']);
});

    // ===== DASHBOARD =====
    Route::get('/dashboard', [DashboardController::class, 'index']);


});
