<?php

use App\Http\Controllers\Frontend\DigitalAccessController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*Route::get('/',[\App\Http\Controllers\Front\LoginController::class,'login_form'])->name('front.login_form');
Route::post('/front/login',[\App\Http\Controllers\Front\LoginController::class,'login'])->name('front.login');
Route::get('/passchange/{$token}',[\App\Http\Controllers\Front\LoginController::class,'pass_form'])->name('front.changepass');
Route::get('/rates/{product?}',[\App\Http\Controllers\Front\RatesController::class, 'rates'])->name('front.rates');*/
if (app()->isLocal()) {
    Route::prefix('cache')->middleware(['auth', 'role:Super Admin'])->group(function (): void {
        Route::get('config', function (): string {
            Artisan::call('optimize:clear');

            return 'success';
        });
    });
}

Route::prefix('digital')->name('digital.')->middleware('track.flow')->group(function (): void {
    Route::get('/', [DigitalAccessController::class, 'authentication'])->name('home');
    Route::get('/auth', [DigitalAccessController::class, 'authentication'])->name('auth');

    Route::middleware('auth')->group(function (): void {
        Route::get('/profile', [DigitalAccessController::class, 'profile'])->name('profile');
        Route::get('/payments', [DigitalAccessController::class, 'payments'])->name('payments');
    });
});
