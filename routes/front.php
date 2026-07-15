<?php

use App\Http\Controllers\Frontend\DigitalAccessController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*Route::get('/',[\App\Http\Controllers\Front\LoginController::class,'login_form'])->name('front.login_form');
Route::post('/front/login',[\App\Http\Controllers\Front\LoginController::class,'login'])->name('front.login');
Route::get('/passchange/{$token}',[\App\Http\Controllers\Front\LoginController::class,'pass_form'])->name('front.changepass');
Route::get('/rates/{product?}',[\App\Http\Controllers\Front\RatesController::class, 'rates'])->name('front.rates');*/
Route::prefix('cache')->group(function () {
    Route::get('config', function () {
        try {

            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('event:clear');
            Artisan::call('optimize:clear');
            echo 'success';
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    });
});

Route::prefix('digital')->name('digital.')->middleware('track.flow')->group(function (): void {
    Route::get('/', [DigitalAccessController::class, 'authentication'])->name('home');
    Route::get('/auth', [DigitalAccessController::class, 'authentication'])->name('auth');

    Route::middleware('auth')->group(function (): void {
        Route::get('/profile', [DigitalAccessController::class, 'profile'])->name('profile');
        Route::get('/payments', [DigitalAccessController::class, 'payments'])->name('payments');
    });
});
