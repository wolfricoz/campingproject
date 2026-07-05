<?php

use App\Http\Controllers\ArrangementController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::get('/locations', [LocationController::class, 'index'])->name('locations');
    Route::name('customers.')->prefix('arrangements')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('store');

    });

    Route::name('arrangements.')->prefix('arrangements')->group(function () {
        Route::get('/', [ArrangementController::class, 'index'])->name('index');
        Route::post('/', [ArrangementController::class, 'store'])->name('store');
        Route::post('/update/status/', [ArrangementController::class, 'update'])->name('status');

    });
});



