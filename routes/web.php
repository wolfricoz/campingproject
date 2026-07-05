<?php

use App\Http\Controllers\ArrangementController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => (Route::has('login') && !auth()->check()),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');
Route::get('/booking', [BookingController::class, 'index'])->middleware('auth')->name('booking');
Route::get('/payment/{guid}', [PaymentController::class, 'index'])->middleware('auth')->name('payment');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/arrangements/{status?}', [ArrangementController::class, 'index'])->middleware(['auth', 'verified'])->name('arrangement.index');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
