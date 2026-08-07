<?php

use App\Http\Controllers\ArrangementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::get('/locations', [LocationController::class, 'index'])->name('locations');
    Route::name('customers.')->prefix('arrangements')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('store');
        Route::get('/customers/{id}', [CustomerController::class, 'find'])->name('find');

    });

    Route::name('arrangements.')->prefix('arrangements')->group(function () {
        Route::get('/', [ArrangementController::class, 'index'])->name('index');
        Route::post('/', [ArrangementController::class, 'store'])->name('store');
        Route::post('/update/status/', [ArrangementController::class, 'update'])->name('status');

    });
    Route::name('locations.')->prefix('locations')->group(function () {

        Route::post('/store', [LocationController::class, 'store'])->name('store');
    });
});

// The public booking page needs these to show the amount of nights and the price, so they run without auth.
Route::name('api.calculations.')->prefix('calculations')->withoutMiddleware('auth:sanctum')->group(function () {
    Route::get('/days', [ArrangementController::class, 'calculateDays'])->name('days');
    Route::get('/price', [ArrangementController::class, 'calculatePrice'])->name('price');

});
