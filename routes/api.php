<?php

use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminContractController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminKycController;
use App\Http\Controllers\Api\Admin\AdminPaymentController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminWithdrawalController;
use App\Http\Controllers\Api\Owner\OwnerAuthController;
use App\Http\Controllers\Api\Owner\OwnerComplaintController;
use App\Http\Controllers\Api\Owner\OwnerGalleryController;
use App\Http\Controllers\Api\Owner\OwnerKostController;
use App\Http\Controllers\Api\Owner\OwnerKYCController;
use App\Http\Controllers\Api\Owner\OwnerProfileController;
use App\Http\Controllers\Api\Owner\OwnerContractController;
use App\Http\Controllers\Api\Owner\OwnerDashboardController;
use App\Http\Controllers\Api\Owner\OwnerRoomController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsOwner;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/auth')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {
        Route::get('/me', [AdminAuthController::class, 'me']);
        Route::post('/logout', [AdminAuthController::class, 'logout']);
    });
});

Route::prefix('admin/users')->middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/', [AdminUserController::class, 'index']);
    Route::post('/owners', [AdminUserController::class, 'storeOwner']);
    Route::get('/{id}', [AdminUserController::class, 'show']);
    Route::put('/{id}', [AdminUserController::class, 'update']);
    Route::delete('/{id}', [AdminUserController::class, 'destroy']);
});

Route::prefix('admin/kyc')->middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/', [AdminKycController::class, 'index']);
    Route::get('/{id}', [AdminKycController::class, 'show']);
    Route::post('/{id}/approve', [AdminKycController::class, 'approve']);
    Route::post('/{id}/reject', [AdminKycController::class, 'reject']);
});

Route::prefix('admin/withdrawals')->middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/', [AdminWithdrawalController::class, 'index']);
    Route::get('/{id}', [AdminWithdrawalController::class, 'show']);
    Route::post('/{id}/approve', [AdminWithdrawalController::class, 'approve']);
    Route::post('/{id}/reject', [AdminWithdrawalController::class, 'reject']);
});

Route::prefix('admin/payments')->middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/', [AdminPaymentController::class, 'index']);
    Route::get('/{id}', [AdminPaymentController::class, 'show']);
});

Route::prefix('admin/contracts')->middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/', [AdminContractController::class, 'index']);
    Route::get('/{id}', [AdminContractController::class, 'show']);
});

Route::prefix('admin/dashboard')->middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index']);
});

Route::prefix('owner/auth')->group(function () {
    Route::post('/register', [OwnerAuthController::class, 'register']);
    Route::post('/login', [OwnerAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', EnsureUserIsOwner::class])->group(function () {
        Route::get('/me', [OwnerAuthController::class, 'me']);
        Route::post('/logout', [OwnerAuthController::class, 'logout']);
    });
});

Route::prefix('owner/profile')->middleware(['auth:sanctum', EnsureUserIsOwner::class])->group(function () {
    Route::get('/', [OwnerProfileController::class, 'show']);
    Route::put('/', [OwnerProfileController::class, 'update']);
});

Route::prefix('owner/kosts')->middleware(['auth:sanctum', EnsureUserIsOwner::class])->group(function () {
    Route::get('/', [OwnerKostController::class, 'index']);
    Route::post('/', [OwnerKostController::class, 'store']);
    Route::get('/{id}', [OwnerKostController::class, 'show']);
    Route::put('/{id}', [OwnerKostController::class, 'update']);
    Route::delete('/{id}', [OwnerKostController::class, 'destroy']);
});

Route::prefix('owner/kosts/{kostId}/rooms')->middleware(['auth:sanctum', EnsureUserIsOwner::class])->group(function () {
    Route::get('/', [OwnerRoomController::class, 'index']);
    Route::post('/', [OwnerRoomController::class, 'store']);
    Route::get('/{roomId}', [OwnerRoomController::class, 'show']);
    Route::put('/{roomId}', [OwnerRoomController::class, 'update']);
    Route::delete('/{roomId}', [OwnerRoomController::class, 'destroy']);
});

Route::prefix('owner/kosts/{kostId}/galleries')->middleware(['auth:sanctum', EnsureUserIsOwner::class])->group(function () {
    Route::get('/', [OwnerGalleryController::class, 'index']);
    Route::post('/', [OwnerGalleryController::class, 'store']);
    Route::put('/{galleryId}', [OwnerGalleryController::class, 'update']);
    Route::delete('/{galleryId}', [OwnerGalleryController::class, 'destroy']);
});

Route::prefix('owner/contracts')->middleware(['auth:sanctum', EnsureUserIsOwner::class])->group(function () {
    Route::get('/', [OwnerContractController::class, 'index']);
    Route::get('/{contractId}', [OwnerContractController::class, 'show']);
    Route::post('/{contractId}/verify/{action}', [OwnerContractController::class, 'verify'])
        ->whereIn('action', ['approve', 'reject']);
});

Route::prefix('owner/kyc')->middleware(['auth:sanctum', EnsureUserIsOwner::class])->group(function () {
    Route::get('/', [OwnerKYCController::class, 'show']);
    Route::post('/', [OwnerKYCController::class, 'submit']);
});

Route::prefix('owner/dashboard')->middleware(['auth:sanctum', EnsureUserIsOwner::class])->group(function () {
    Route::get('/', [OwnerDashboardController::class, 'index']);
});

Route::prefix('owner/complaints')->middleware(['auth:sanctum', EnsureUserIsOwner::class])->group(function () {
    Route::get('/', [OwnerComplaintController::class, 'index']);
    Route::get('/{id}', [OwnerComplaintController::class, 'show']);
    Route::put('/{id}', [OwnerComplaintController::class, 'update']);
});

Scramble::routes(fn ($route) => str_starts_with($route->uri(), 'api/admin') || str_starts_with($route->uri(), 'api/owner'));
