<?php

use App\Http\Controllers\ArrangementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::get('/locations', [LocationController::class, 'index'])
        ->middleware('permission:access dashboard')
        ->name('locations');

    Route::name('customers.')->prefix('arrangements')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])
            ->middleware('permission:view customers')
            ->name('index');
        Route::post('/customers', [CustomerController::class, 'store'])
            ->middleware('permission:create bookings for customers')
            ->name('store');
        Route::get('/customers/{id}', [CustomerController::class, 'find'])
            ->middleware('permission:view customers')
            ->name('find');

    });

    Route::name('arrangements.')->prefix('arrangements')->group(function () {
        Route::get('/', [ArrangementController::class, 'index'])
            ->middleware('permission:view all bookings')
            ->name('index');
        Route::post('/', [ArrangementController::class, 'store'])
            ->middleware('permission:create bookings for customers|edit bookings')
            ->name('store');

        // The status itself decides which permission is needed, see
        // ArrangementStatus::permission(). Everyone who may touch a status at
        // all needs `edit bookings` first.
        Route::post('/update/status/', [ArrangementController::class, 'update'])
            ->middleware('permission:edit bookings')
            ->name('status');

    });
    Route::name('locations.')->prefix('locations')->group(function () {

        Route::post('/store', [LocationController::class, 'store'])
            ->middleware('permission:manage locations')
            ->name('store');

        Route::post('/available', [LocationController::class, 'checkAvailability'])
            ->withoutMiddleware('auth:sanctum')
            ->middleware('customer_permission:create booking')
            ->name('available');
    });

    Route::name('news.')->prefix('news')->group(function () {

        Route::post('/store', [NewsController::class, 'store'])
            ->middleware('permission:manage news')
            ->name('store');
    });
});

Route::name('api.calculations.')
    ->prefix('calculations')
    ->withoutMiddleware('auth:sanctum')
    ->middleware('customer_permission:create booking')
    ->group(function () {
        Route::get('/days', [ArrangementController::class, 'calculateDays'])->name('days');
        Route::get('/price', [ArrangementController::class, 'calculatePrice'])->name('price');

    });
