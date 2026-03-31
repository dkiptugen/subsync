<?php

use App\Http\Controllers\API\ApplePayCallbackController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BillingController;
use App\Http\Controllers\API\DPOCallbackController;
use App\Http\Controllers\API\MpesaCallbackController;
use App\Http\Controllers\B2b\OrganizationController;
use App\Http\Controllers\RateController;
use App\Models\Cart;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('subscription/{identifier}/email_renew', [BillingController::class, 'email_renewal'])->name('subscription.email_renew');
Route::get('verify_payment', [BillingController::class, 'verify_token'])->name('dpo-verify-token');
Route::post('update-pass/{orgid}', [OrganizationController::class, 'set_default_password'])->name('default_pass');
Route::get('get_rate_select', [RateController::class, 'getSelect2Data'])->name('get_rate_select');
Route::prefix('auth')->group(function ()
    {
        Route::post('email_check', [AuthController::class, 'auth_email'])->name('auth.email');
        Route::get('social/{social}', [AuthController::class, 'redirectToProvider']);
        Route::get('social/{social}/callback', [AuthController::class, 'handleProviderCallback']);
        Route::any('social/{social}/delete', [AuthController::class, 'deleteProviderCallback']);
    });
Route::prefix('billing')->group(function ()
    {

        Route::prefix('dpo')->group(function ()
            {

                Route::get('verifytoken', [DPOCallbackController::class, 'verifyToken'])->name('dpo.verify_token');
                Route::any('notification', [DPOCallbackController::class, 'notification'])->name('dpo.notification');
            });
        Route::prefix('apple-pay')->group(function ()
            {
                Route::post('callback', [ApplePayCallbackController::class, 'get_transaction_id'])->name('applepay-get-transid');
            });
    });


Route::group(['middleware' => ['passkey', 'force_json', 'cors']], function ()
    {
        Route::post('/email_reverify', [AuthController::class, 'email_reverify'])->name('email_reverify');
        Route::post('/user-subscribe', [AuthController::class, 'email_subscribe'])->name('user.subscribe');

        Route::post('/leads', [BillingController::class, 'save_leads'])->name('api.save_leads');
        Route::prefix('auth')->group(function ()
            {
                Route::post('/social_login_v2', [AuthController::class, 'social_login_v2'])->name('api.social_login_v2');
                Route::post('/social_login', [AuthController::class, 'social_login'])->name('api.social_login');
                Route::post('/login', [AuthController::class, 'login'])->name('api.login');
                Route::post('/register', [AuthController::class, 'register'])->name('api.register');
                Route::post('/passforgot', [AuthController::class, 'resetpassword'])->name('api.reset');
                Route::post('/password_reset', [AuthController::class, 'reset'])->name('update.password');
                Route::get('/user-verify/{token}', [AuthController::class, 'email_verify'])->name('user.verification');
            });

        Route::get('/get_rates/{productid}', [BillingController::class, 'getRates'])->name('api.rates');
        Route::get('/get_products', [BillingController::class, 'getProducts'])->name('api.products');
        Route::get('/get_regions', [BillingController::class, 'getRegions'])->name('api.get_regions');
        Route::get('/get_region/{iso}', [BillingController::class, 'getRegionDetails'])->name('api.get_region_specific');
        Route::post('/currency_convert', [BillingController::class, 'currency_convertor'])->name('api.currency_convertor');
        Route::get('/configuration', [BillingController::class, 'configuration'])->name('api.configuration');
        Route::get('/notification_users', [BillingController::class, 'notification_users'])->name('api.notification_users');
        Route::get('/notification_users_corporate', [BillingController::class, 'notification_users_corporate'])->name('api.notification_users_corporate');
        Route::get('/reasons', [BillingController::class, 'get_reason'])->name('api.reasons');

        Route::post('create_cart',[BillingController::class,'initiate_cart'])->name('api.create_cart');
        Route::post('/add_to_cart', [BillingController::class, 'cart'])->name('api.cart');
        Route::get('/get_cart', [BillingController::class, 'get_cart'])->name('api.get_cart');
        Route::patch('/remove_from_cart', [BillingController::class, 'remove_from_cart'])->name('api.remove_from_cart');
        Route::get('check_nickname', [BillingController::class, 'check_nickname'])->name('api.check_nickname');
        Route::get('get_rate/{id}', [BillingController::class, 'getRate'])->name('api.rate');

        Route::middleware(['auth:api', 'auth.session'])->group(function ()
            {
                Route::post('/add_edition', [BillingController::class, 'add_edition'])->name('api.add_edition');
                Route::post('/add_subscription', [BillingController::class, 'add_subscription'])->name('api.add_subscription');
                Route::post('/mpesa-subscribe', [MpesaCallbackController::class, 'mpesa_payment'])->name('api.mpesa_payment');
                Route::patch('/auth/change_password', [AuthController::class, 'change_password'])->name('api.change_password');
                Route::get('/get_user', [AuthController::class, 'getUser'])->name('api.get_user');
                Route::patch('/user_profile', [AuthController::class, 'user_profile'])->name('api.user_profile');
                Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
                Route::post('/get_user_by_id', [AuthController::class, 'getUserById'])->name('api.getuserbyid');
                Route::post('/get_user_by_email', [AuthController::class, 'getUserByEmail'])->name('api.getuserbyemail');

                Route::post('/check_payment', [BillingController::class, 'check_payment'])->name('api.check_payment');
                Route::post('/subscription', [BillingController::class, 'getUserSubscription'])->name('api.get_subscription');
                Route::post('/direct_checkout', [BillingController::class, 'direct_checkout'])->name('api.direct_checkout');
                Route::post('/check_coupon', [BillingController::class, 'getCouponDetails'])->name('api.check_coupon');
                Route::post('/stkpush', [BillingController::class, 'stkpush'])->name('api.stkpush');
                Route::post('/get_subscriptions', [BillingController::class, 'get_subscriptions'])->name('api.get_subscriptions');
                Route::get('/payment_methods', [BillingController::class, 'getPaymentMethods'])->name('api.get_payment_methods');
                Route::post('/checkout', [BillingController::class, 'subscribe'])->name('api.subscribe');
                Route::get('/get_user_sub', [BillingController::class, 'get_subscriptions'])->name('api.get_user_sub');
                Route::post('/unsubscribe', [BillingController::class, 'unsubscribe'])->name('api.unsubscribe');
                Route::post('/notification_status', [BillingController::class, 'notification_status'])->name('api.notification_status');
                Route::post('mpesa/verify_transaction',[BillingController::class,'verify_transaction'])->name('api.verify_transaction');
                Route::post("mpesa/qr",[BillingController::class,"get_mpesa_qr"])->name('api.get_mpesa_qr');

                Route::post('add_points', [BillingController::class, 'add_points'])->name('api.add_points');
                Route::get('list_points', [BillingController::class, 'list_points'])->name('api.list_points');
                Route::get('list_events', [BillingController::class, 'list_events'])->name('api.list_events');
                Route::get('verify_user_nickname', [BillingController::class, 'check_user_nickname'])->name('api.verify_user_nickname');
                Route::post('upgrade_subscription', [BillingController::class, 'upgrade_subscription'])->name('api.upgrade_subscription');
                Route::post('apply_coupon', [BillingController::class, 'apply_promocode'])->name('api.apply_coupon');

            });
    });


Route::post("transaction_validate",function(){
    $request = request();
    $reference = $request->reference;
    $transaction = \App\Models\Transaction::where('identifier', $reference)->first();

    $cart = null;
    $transactions = null;
    if(!$transaction)
        {
            $cart = Cart::where('identifier', $reference)->first();
            if($cart)
                {
                    $transactions = Transaction::whereHas('subscription', function ($query) use ($cart) {
                        $query->where('cart_id', $cart->id);
                    })
                                               ->get();

                    $amount = 0;
                    foreach($transactions as $trans)
                        {
                            $amount = $amount + $trans->amount;
                        }
                }
        }
    if($transaction || $cart){
        if($transaction){
            $amount = $transaction->amount;
        }
        else{
            $transaction = $transactions->first();
        }

        return response()->json([
            'reference' => !is_null($cart) ? $cart->identifier : $transaction->identifier,
            'amount' => $amount,
            'type' => $transaction->source == "stkpush" ? "stkpush" : "confirmation",
            'checkout_request_id' => $transaction->checkout_request_id,
        ]);
    }

    return null;
});

Route::get('health_check', function(){
    $count = \App\Models\Product::count();
    return response()->json(['status'=>'Ok','products' => $count,'version' => 1]);
});
