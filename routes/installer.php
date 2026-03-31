<?php

use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

Route::prefix('install')
    ->middleware(['web', 'not_installed'])
    ->name('install.')
    ->controller(InstallController::class)
    ->group(function (): void {
        Route::get('/', 'welcome')->name('welcome');
        Route::post('/database', 'database')->name('database');
        Route::post('/admin', 'admin')->name('admin');
        Route::post('/finish', 'finish')->name('finish');
    });
