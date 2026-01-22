<?php

use Illuminate\Support\Facades\Route;
use App\Models\UserModel;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\MaterialRequestController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReceiveController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\SpbController;
use App\Http\Controllers\Api\SpbPoController;
use App\Http\Controllers\Api\SpbDoController;
use App\Http\Controllers\Api\SpbInvoiceController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Middleware\CheckInputOpen;

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

/*
|--------------------------------------------------------------------------
| READ ONLY (GET) — SELALU BOLEH
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // AUTH
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/auth/email-verified', [AuthController::class, 'emailVerified']);

    // USERS
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    // BARANG
    Route::get('/barang', [BarangController::class, 'index']);
    Route::get('/barang/{id}', [BarangController::class, 'show']);
    Route::get('/barang/export-excel', [BarangController::class, 'exportBarang']);

    // STOCK
    Route::get('/stock', [StockController::class, 'index']);
    Route::get('/stock/export-excel', [StockController::class, 'exportStock']);

    // MATERIAL REQUEST
    Route::get('/mr', [MaterialRequestController::class, 'index']);
    Route::get('/mr/open', [MaterialRequestController::class, 'getOpenMR']);
    Route::get('/mr/generate-kode', [MaterialRequestController::class, 'generateKode']);
    Route::get('/mr/kode/{kode}', [MaterialRequestController::class, 'showKode'])->where('kode', '.*');
    Route::get('/mr/{id}', [MaterialRequestController::class, 'show']);
    Route::get('/mr/{kode}/export/pdf', [MaterialRequestController::class,'exportPdf'])->where('kode', '.*');

    // PURCHASE REQUEST
    Route::get('/pr', [PurchaseRequestController::class, 'index']);
    Route::get('/pr/open', [PurchaseOrderController::class, 'getPrOpen']);
    Route::get('/pr/kode/{kode}', [PurchaseRequestController::class, 'showKode'])->where('kode', '.*');
    Route::get('/pr/{id}', [PurchaseRequestController::class, 'show']);
     Route::get('{kode}/export/pdf', [PurchaseRequestController::class,'exportPdf'])->where('kode', '.*');

    // PURCHASE ORDER
    Route::get('{kode}/export/pdf', [PurchaseOrderController::class,'exportPdf']);
    Route::get('/po', [PurchaseOrderController::class, 'index']);
    Route::get('/po/kode/{kode}', [PurchaseOrderController::class, 'showKode'])->where('kode', '.*');
    Route::get('/po/{id}', [PurchaseOrderController::class, 'show']);
    

    // RECEIVE
    Route::get('/receive', [ReceiveController::class, 'index']);
    Route::get('/receive/history', [ReceiveController::class, 'history']);
    Route::get('/receive/purchase-orders', [ReceiveController::class, 'getPoPurchased']);
    Route::get('/receive/kode/{kode}', [ReceiveController::class, 'showByKode'])->where('kode', '.*');
    Route::get('/receive/export-excel', [ReceiveController::class, 'exportReceive']);
    Route::get('/receive/{kode}/export/pdf', [ReceiveController::class, 'exportPdf']);

    // DELIVERY
    Route::get('/deliveries', [DeliveryController::class, 'index']);
    Route::get('/deliveries/kode/{kode}', [DeliveryController::class, 'showKode']);
    Route::get('/deliveries/export-excel', [DeliveryController::class, 'exportDeliveryHeader']);
    Route::get('/deliveries/{kode}/export/pdf', [DeliveryController::class, 'exportPdf']);

    // SPB
    Route::get('/spb/generate-kode', [SpbController::class, 'generateKodeSpb']);
    Route::get('/spb', [SpbController::class, 'index']);
    Route::get('/spb/po', [SpbPoController::class, 'index']);
    Route::get('/spb/do', [SpbDoController::class, 'index']);
    Route::get('/spb/invoice', [SpbInvoiceController::class, 'index']);
    Route::get('/spb/report', [SpbController::class, 'view']);
    Route::get('/spb/kode/{kode}', [SpbController::class, 'showKode'])->where('kode', '.*');
    Route::get('/spb/export-excel', [SpbController::class, 'exportSpbExcel']);
    Route::get('/spb/print/{kode}', [SpbController::class, 'printSpb'])->where('kode', '.*');

    // VENDOR
    Route::get('/vendors', [VendorController::class, 'index']);
    Route::get('/vendors/{id}', [VendorController::class, 'show']);

    // CUSTOMER
    Route::get('/customers', [CustomerController::class, 'index']);

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| WRITE (POST / PUT / DELETE) — DITUTUP TANGGAL 5
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', CheckInputOpen::class])->group(function () {

    // AUTH
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);

    // USERS
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}/status', [UserController::class, 'updateStatus']);

    // BARANG
    Route::post('/barang', [BarangController::class, 'store']);
    Route::put('/barang/{id}', [BarangController::class, 'update']);

    // STOCK
    Route::post('/stock', [StockController::class, 'store']);

    // MR
    Route::post('/mr', [MaterialRequestController::class, 'store']);
    Route::put('/mr/{id}', [MaterialRequestController::class, 'update']);
    Route::delete('/mr/{id}', [MaterialRequestController::class, 'destroy']);

    // PR
    Route::post('/pr', [PurchaseRequestController::class, 'store']);
    Route::put('/pr/{id}', [PurchaseRequestController::class, 'update']);
    Route::delete('/pr/{id}', [PurchaseRequestController::class, 'destroy']);

    // PO
    Route::post('/po', [PurchaseOrderController::class, 'store']);
    Route::put('/po/{id}', [PurchaseOrderController::class, 'update']);
    Route::delete('/po/{id}', [PurchaseOrderController::class, 'destroy']);

    // RECEIVE
    Route::post('/receive', [ReceiveController::class, 'store']);
    Route::post('/receive/{kode}/sign-penerima', [ReceiveController::class, 'signPenerima']);

    // DELIVERY
    Route::post('/deliveries', [DeliveryController::class, 'store']);
    Route::put('/deliveries/kode/{kode}', [DeliveryController::class, 'update']);
    Route::patch('/deliveries/kode/{kode}/status', [DeliveryController::class, 'updateStatus']);
    Route::patch('/deliveries/kode/{kode}/pickup-plan', [DeliveryController::class, 'updatePickupPlan']);
    Route::post('/deliveries/{kode}/receive', [DeliveryController::class, 'receive']);
    Route::post('/deliveries/{kode}/sign-penerima', [DeliveryController::class, 'signPenerima']);

    // SPB
    Route::post('/spb', [SpbController::class, 'store']);
    Route::post('/spb/po', [SpbPoController::class, 'store']);
    Route::post('/spb/do', [SpbDoController::class, 'store']);
    Route::post('/spb/invoice', [SpbInvoiceController::class, 'store']);
    

    // VENDOR
    Route::post('/vendors', [VendorController::class, 'store']);
    Route::put('/vendors/{id}', [VendorController::class, 'update']);
    Route::delete('/vendors/{id}', [VendorController::class, 'destroy']);
    Route::put('/vendors/{id}/toggle', [VendorController::class, 'toggleStatus']);

    // CUSTOMER
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::put('/customers/{id}/toggle', [CustomerController::class, 'toggleStatus']);
});

/*
|--------------------------------------------------------------------------
| INPUT STATUS (UNTUK FRONTEND)
|--------------------------------------------------------------------------
*/

Route::get('/input-status', function () {
    $today = now()->day;

    return response()->json([
        'is_open' => $today !== 5,
        'message' => $today === 5
            ? 'Input ditutup tanggal 5'
            : 'Input dibuka'
    ]);
});
