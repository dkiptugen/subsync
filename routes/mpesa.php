<?php

    use App\Http\Controllers\API\BillingController;
    use App\Http\Controllers\API\MpesaCallbackController;
	use Illuminate\Support\Facades\Route;
    Route::group(['middleware' => ['force_json', 'cors']], function () {
        Route::post('notify', [MpesaCallbackController::class, 'notify'])->name('mpesa.notify');
        Route::post('check_payment', [BillingController::class, 'check_mpesa_payment'])->name('mpesa.check_payment');
        Route::any('validation', [MpesaCallbackController::class, 'validation'])->name('mpesa.validation');
        Route::any('confirmation', [MpesaCallbackController::class, 'confirmation'])->name('mpesa.confirmation');
        Route::any('b2b', [MpesaCallbackController::class, 'b2b'])->name('mpesa.b2b');
        Route::any('b2c', [MpesaCallbackController::class, 'b2c'])->name('mpesa.b2c');
        Route::any('account_balance', [MpesaCallbackController::class, 'account_balance'])->name('mpesa.account_balance');
        Route::any('reversal', [MpesaCallbackController::class, 'reversal'])->name('mpesa.reversal');
        Route::any('transaction_status', [MpesaCallbackController::class, 'transaction_status'])->name('mpesa.transaction_status');
        Route::any('stk_push_request', [MpesaCallbackController::class, 'stk_push_request'])->name('mpesa.stk_push_request');
        Route::any('stk_push_query', [MpesaCallbackController::class, 'stk_push_query'])->name('mpesa.stk_push_query');
	    Route::get('mpesa-pay/{identifier}',[MpesaCallbackController::class,'mpesa_view'])->name ('mpesa.view');
	    Route::post('mpesa-pay',[MpesaCallbackController::class,'mpesa_pay'])->name('mpesa.pay');

        Route::post('transaction/status',[MpesaCallbackController::class,'mpesa_transaction_status'])->name('mpesa.transaction.status');
        Route::post('queue_timeout',[MpesaCallbackController::class,'queue_timeout'])->name('mpesa.queue_timeout');
    });


