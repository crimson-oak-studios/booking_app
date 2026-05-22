<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\BusinessProfileController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\PublicBookingController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\TimeBlockController;
use Illuminate\Support\Facades\Route;

Route::post('/public/bookings', [PublicBookingController::class, 'store']);
Route::post('/webhooks/square', [PaymentWebhookController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/business-profile', [BusinessProfileController::class, 'show']);
    Route::put('/business-profile', [BusinessProfileController::class, 'update']);
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::apiResource('services', ServiceController::class)->except(['show']);
    Route::apiResource('customers', CustomerController::class)->only(['index', 'store', 'update']);
    Route::apiResource('appointments', AppointmentController::class)->only(['index', 'store', 'update']);
    Route::apiResource('time-blocks', TimeBlockController::class)->only(['index', 'store', 'update']);
});
