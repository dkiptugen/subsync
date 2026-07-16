<?php

use Illuminate\Support\Facades\Route;

Route::prefix('b2b')->middleware(['auth', 'is_business'])->group(function () {
    Route::get('/',[\App\Http\Controllers\Client\DashboardController::class,'index'])->name('client_dashboard.index');

    Route::resource('client_receipt',\App\Http\Controllers\Client\ReceiptController::class,['except' => [ 'show' ]]);
    Route::post('/client_receipt/get',[\App\Http\Controllers\Client\ReceiptController::class,'get'])->name('client_receipt.datatable');

    Route::resource('client_subscription',\App\Http\Controllers\Client\SubscriptionController::class,['except' => [ 'show' ]]);
    Route::post('/client_subscription/get',[\App\Http\Controllers\Client\SubscriptionController::class,'get'])->name('client_subscription.datatable');

    Route::get('client_transaction',[\App\Http\Controllers\Client\SubscriptionController::class,'trans'])->name('client_transaction.index');
    Route::post('/client_transaction/get',[\App\Http\Controllers\Client\SubscriptionController::class,'get_trans'])->name('client_transaction.datatable');

    Route::resource('client_users',\App\Http\Controllers\Client\UserController::class,['except' => [ 'show' ]]);
    Route::post('/client_users/get',[\App\Http\Controllers\Client\UserController::class,'get'])->name('client_users.datatable');
    Route::get('/client_users/upload-form/{id}',[\App\Http\Controllers\Client\UserController::class,'upload_form'] ) ->name('client_users.uploadform') ;
    Route::get('/client_users/upload/{id}',[\App\Http\Controllers\Client\UserController::class,'upload'] ) ->name('client_users.upload') ;

    Route::resource('client_purchase_order',\App\Http\Controllers\Client\PurchaseOrderController::class,['except' => [ 'show' ]]);
    Route::post('/client_purchase_order/get',[\App\Http\Controllers\Client\PurchaseOrderController::class,'get'])->name('client_purchase_order.datatable');

    Route::resource('client_invoice',\App\Http\Controllers\Client\InvoiceController::class,['except' => [ 'show' ]]);
    Route::post('/client_invoice/get',[\App\Http\Controllers\Client\InvoiceController::class,'get'])->name('client_invoice.datatable');
});
