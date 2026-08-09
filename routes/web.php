<?php

use App\Http\Controllers\ArrangementController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// === Customer Facing Routes
Route::get('/', [FrontEndController::class, 'index'])->name('home');
Route::get('/about', [FrontEndController::class, 'about'])->name('about');
Route::get('/contact', [FrontEndController::class, 'contact'])->name('contact');
Route::post('/contact', [FrontEndController::class, 'store'])->name('contact.store');
Route::get('/locations', [FrontEndController::class, 'locations'])->name('locations');
Route::get('/news', [NewsController::class, 'index'])->name('news');

Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');
Route::get('/booking', [BookingController::class, 'index'])->name('booking');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/payment/{guid}', [PaymentController::class, 'index'])->name('payment');
Route::post('/payment/complete', [PaymentController::class, 'store'])->name('payment.complete');
Route::group(['middleware' => ['auth', 'permission:access dashboard'], 'prefix' => 'dashboard', 'as' => ''],
    static function () {
        Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

        Route::get('/arrangements/{status?}', [ArrangementController::class, 'index'])->middleware(['auth', 'verified', 'permission:view all bookings'])
            ->name('arrangement.index');

        Route::get('/location', [LocationController::class, 'adminIndex'])->middleware(['auth', 'verified', 'role:administrator'])
            ->name('locations.index');

        Route::get('/news', [NewsController::class, 'admin'])->middleware(['auth', 'verified', 'permission:manage news'])
            ->name('news.index');

    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
