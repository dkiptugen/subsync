<?php

use App\Http\Controllers\AgentsController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DPOCallbackController;
use App\Http\Controllers\API\FastHubController;
use App\Http\Controllers\Auth\ExpiredPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\B2b\OrganizationController;
use App\Http\Controllers\B2b\PurchaseController;
use App\Http\Controllers\B2b\RateController as B2BRateController;
use App\Http\Controllers\B2b\SubscriptionController as B2BSubscriptionController;
use App\Http\Controllers\B2b\TransactionController as B2BTransactionController;
use App\Http\Controllers\B2b\UserController as B2BUserController;
use App\Http\Controllers\ChurnController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CurrencyConvertorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataMigController;
use App\Http\Controllers\Debug\DebugController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\FinanceApprovalController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\MediaEventsController;
use App\Http\Controllers\MediaLibraryController;
use App\Http\Controllers\MpesaBlacklistController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PluginInstallerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\RateTypeController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReportTSController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SubscriberSubsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserWhitelistController;
use App\Http\Resources\RateResource;
use App\Models\Rate;
use App\Models\Region;
use App\Utils\Helper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Pusher\Pusher;

Route::prefix('plugins')->name('plugins.')->group(function (): void {
    Route::get('/', [PluginInstallerController::class, 'index'])->name('index');
    Route::post('/upload', [PluginInstallerController::class, 'upload'])->name('upload');
    Route::post('/install', [PluginInstallerController::class, 'install'])->name('install');
    Route::post('/enable', [PluginInstallerController::class, 'enable'])->name('enable');
    Route::post('/disable', [PluginInstallerController::class, 'disable'])->name('disable');

});

Route::controller(MediaLibraryController::class)->prefix('media-library')->name('media-library.')->group(function (): void {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
});
Route::redirect('/', '/login')->name('landing');
Auth::routes(['register' => false]);
Route::get('/reset/{token}', [LoginController::class, 'reset_form']);

// Route::middleware(['auth:api'])->post('/pusher/auth', [PusherController::class, 'authenticate']);

Route::get('success', function () {

    return view('modules.auth.success', Helper::site_def());
});

Route::group(['role' => ['admin'], 'middleware' => ['auth'], 'access_level' => ['owner']], function () {

    Route::middleware(['password.expired'])->prefix('manage')->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

        Route::resource('payment_method', PaymentMethodController::class, ['except' => ['show']]);
        Route::post('/payment_methods/get', [PaymentMethodController::class, 'get'])->name('payment_method.datatable');

        Route::resource('site', SiteController::class, ['except' => ['show']]);
        Route::post('/site/get', [SiteController::class, 'get'])->name('site.datatable');

        Route::resource('product', ProductController::class, ['except' => ['show']]);
        Route::post('/product/get', [ProductController::class, 'get'])->name('product.datatable');

        Route::resource('rate_type', RateTypeController::class, ['except' => ['show']]);
        Route::post('/rate_type/get', [RateTypeController::class, 'get'])->name('rate_type.datatable');

        Route::resource('product.rate', RateController::class, ['except' => ['show']]);
        Route::post('/product/{product_id}/rate/get', [RateController::class, 'get'])->name('product.rate.datatable');

        Route::resource('product.subscriber', SubscriberController::class, ['except' => ['show']]);
        Route::post('/product/{product_id}/subscriber/get', [SubscriberController::class, 'get'])->name('product.subscriber.datatable');
        Route::get('/product/{product_id}/subscriber/{user}/change', [SubscriberController::class, 'change'])->name('product.subscriber.change');
        Route::patch('/product/{product_id}/subscriber/{user}/change-password', [SubscriberController::class, 'change_password'])->name('product.subscriber.change_password');
        Route::get('/product/{product_id}/subscriber/{user}/reset', [SubscriberController::class, 'deactivate'])->name('product.subscriber.deactivate');

        Route::resource('product.churn', ChurnController::class, ['except' => ['show']]);
        Route::post('/product/{product_id}/churn/get', [ChurnController::class, 'get'])->name('product.churn.datatable');

        Route::resource('lead', LeadsController::class, ['except' => ['show', 'destroy', 'update', 'edit', 'store', 'create']]);
        Route::post('/lead/get', [LeadsController::class, 'get'])->name('lead.datatable');

        Route::resource('coupon', CouponController::class, ['except' => ['show', 'destroy']]);
        Route::post('/coupon/get', [CouponController::class, 'get'])->name('coupon.datatable');

        Route::resource('currency', CurrencyConvertorController::class, ['except' => ['show', 'destroy']]);
        Route::post('/currency/get', [CurrencyConvertorController::class, 'get'])->name('currency.datatable');
        Route::get('/currency/autocomplete/{id}', [CurrencyConvertorController::class, 'autocomplete'])->name('currency.autocomplete');

        Route::resource('subscription', SubscriptionController::class, ['except' => ['show']]);
        Route::post('/subscription/get', [SubscriptionController::class, 'get'])->name('subscription.datatable');

        Route::resource('subscription.transaction', TransactionController::class, ['except' => ['show']]);
        Route::post('/subscription/{subscription_id}/transaction/get', [TransactionController::class, 'get'])->name('subscription.transaction.datatable');
        Route::get('subscription/{subscriptionid}/transaction/{transactionid}/recheck', [TransactionController::class, 'recycle'])->name('subscription.transaction.recheck');

        Route::get('subscription-approval', [FinanceApprovalController::class, 'index'])->name('subscription-approval.index');
        Route::post('/subscription-approval/get', [FinanceApprovalController::class, 'get'])->name('subscription-approval.datatable');
        Route::put('/subscription-approval/{subscription}/approve', [FinanceApprovalController::class, 'approve'])->name('subscription-approval.approve');
        Route::put('/subscription-approval/{subscription}/disapprove', [FinanceApprovalController::class, 'disapprove'])->name('subscription-approval.disapprove');

        Route::resource('user', UserController::class, ['except' => ['show']]);
        Route::post('/user/get', [UserController::class, 'get'])->name('user.datatable');
        Route::put('/user/{user}/activate', [UserController::class, 'activate'])->name('user.activate');
        Route::get('/user/export', [UserController::class, 'export_view'])->name('user.export_view');
        Route::post('/user/export', [UserController::class, 'export'])->name('user.export');

        Route::resource('user.subscription', SubscriberSubsController::class);
        Route::post('user/{user}/subscription/get', [SubscriberSubsController::class, 'get'])->name('user.subscription.datatable');

        Route::resource('whitelist.type', UserWhitelistController::class, ['except' => ['show']]);
        Route::post('whitelist/{type}/get', [UserWhitelistController::class, 'get'])->name('whitelist.type.datatable');

        //                Route::resource('campaign', CampaignController::class, ['except' => ['show']]);
        //                Route::post('/campaign/get', [CampaignController::class, 'get'])->name('campaign.datatable');

        Route::get('/configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
        Route::post('/configuration/edit', [ConfigurationController::class, 'edit'])->name('configuration.edit');

        Route::resource('user.roles', RolesController::class, ['except' => ['show']]);
        Route::post('/user/{user}/roles/get', [RolesController::class, 'get'])->name('user.roles.datatable');
        Route::get('/user/{user}/roles/export', [RolesController::class, 'export_view'])->name('user.roles.export_view');
        Route::post('/user/{user}/roles/export', [RolesController::class, 'export'])->name('user.roles.export');

        Route::resource('user.permissions', PermissionsController::class, ['except' => ['show']]);
        Route::post('/user/{user}/permissions/get', [PermissionsController::class, 'get'])->name('user.permissions.datatable');
        Route::get('/user/{user}/permissions/export', [PermissionsController::class, 'export_view'])->name('user.permissions.export_view');
        Route::post('/user/{user}/permissions/export', [PermissionsController::class, 'export'])->name('user.permissions.export');

        Route::resource('user.logs', LogsController::class, ['except' => ['show']]);
        Route::post('user/{user}/logs/get', [LogsController::class, 'get'])->name('user.logs.datatable');
        Route::get('user/{user}/logs/export', [LogsController::class, 'export_view'])->name('user.logs.export_view');
        Route::post('user/{user}/logs/export', [LogsController::class, 'export'])->name('user.logs.export');

        Route::get('profile', [SubscriberController::class, 'profile'])->name('profile.index');
        Route::put('profile/{id}/update', [SubscriberController::class, 'profile_update'])->name('profile.update');

        Route::resource('organization', OrganizationController::class, ['except' => ['show']]);
        Route::post('/organization/get', [OrganizationController::class, 'get'])->name('organization.datatable');
        Route::get('/organization/{orgid}/user', [B2BUserController::class, 'index'])->name('organization.user');
        Route::delete('/organization/{orgid}/user/{id}/delete', [B2BUserController::class, 'destroy'])->name('organization.user.destroy');
        Route::get('/organization/{orgid}/user-create', [B2BUserController::class, 'create'])->name('organization.user_create');
        Route::get('/organization/{orgid}/user-upload', [B2BUserController::class, 'upload'])->name('organization.upload');
        Route::post('/organization/{orgid}/upload', [B2BUserController::class, 'upload_users'])->name('organization.user_uploads');
        Route::post('/organization/{orgid}/user-save', [B2BUserController::class, 'store'])->name('organization.user_save');
        Route::get('/organization/{orgid}/user-pass', [B2BUserController::class, 'pass_change'])->name('organization.user_pass');
        Route::post('/organization/{orgid}/user-get', [B2BUserController::class, 'get'])->name('organization.user_datatable');
        Route::get('/organization/{orgid}/user-export', [B2BUserController::class, 'export'])->name('organization.user_export');
        Route::get('/organization/{orgid}/user-edit/{userid}', [B2BUserController::class, 'edit'])->name('organization.user_edit');
        Route::post('/organization/{orgid}/user-edit/{userid}', [B2BUserController::class, 'update'])->name('organization.user_update');

        Route::resource('organization.rate', B2BRateController::class, ['except' => ['show']]);
        Route::post('/organization/{organization}/rate/get', [B2BRateController::class, 'get'])->name('organization.rate.datatable');

        Route::resource('organization.subscription', B2BSubscriptionController::class, ['except' => ['show']]);
        Route::get('organization/{organization}/subscribers', [B2BSubscriptionController::class, 'subscribers'])->name('organization.subscribers');
        Route::post('/organization/{organization}/subscription/get', [B2BSubscriptionController::class, 'get'])->name('organization.subscription.datatable');
        Route::get('/organization/{organization}/subscription/{subscription}/assign', [B2BSubscriptionController::class, 'assign'])->name('organization.subscription.assign');
        Route::post('/organization/{organization}/subscription/{subscription}/get', [B2BSubscriptionController::class, 'assign_datatable'])->name('organization.subscription.assign_datatable');
        Route::put('/organization/{organization}/subscription/{subscription}/assign', [B2BSubscriptionController::class, 'assign_update'])->name('organization.subscription.assignment');
        Route::post('/organization/{organization}/subscription/{subscription}/upload', [B2BSubscriptionController::class, 'upload'])->name('organization.subscription.upload');
        Route::get('/organization/{organization}/subscription/{subscription}/assign-upload', [B2BSubscriptionController::class, 'assign_upload_form'])->name('organization.subscription.assign_upload_form');
        Route::post('/organization/{organization}/subscription/{subscription}/assign-upload', [B2BSubscriptionController::class, 'assign_upload'])->name('organization.subscription.assign_upload');
        Route::get('/organization/{organization}/subscription/{subscription}/assign-form', [B2BSubscriptionController::class, 'assign_form'])->name('organization.subscription.assign_form');
        Route::post('/organization/{organization}/subscription/{subscription}/assignfromform', [B2BSubscriptionController::class, 'assign_from_form'])->name('organization.subscription.assign_user');
        Route::resource('organization.purchase', PurchaseController::class, ['except' => ['show', 'create', 'store', 'destroy']]);
        Route::post('/organization/{organization}/purchase/get', [PurchaseController::class, 'get'])->name('organization.purchase.datatable');
        Route::get('/organization/{organization}/purchase/{purchaseid}/invoice', [PurchaseController::class, 'invoice'])->name('organization.purchase.invoice');
        Route::get('/organization/{organization}/set_password', [OrganizationController::class, 'password'])->name('organization.password');
        Route::post('/organization/{organization}/save_pass', [OrganizationController::class, 'set_default_password'])->name('organization.password.store');

        Route::get('report/subscriber-form', [ReportsController::class, 'reg_index'])->name('report.subscriber_form');
        Route::post('report/subscriber-datatable', [ReportsController::class, 'reg_datatable'])->name('report.subscriber_datatable');
        Route::post('report/subscriber-export', [ReportTSController::class, 'subscribers_export'])->name('report.subscriber');

        Route::get('report/subscription-form', [ReportTSController::class, 'subscriptions_form'])->name('report.subscription_form');
        Route::post('report/subscription-datatable', [ReportTSController::class, 'subscriptions_datatable'])->name('report.subscription_datatable');
        Route::post('report/subscription', [ReportTSController::class, 'subscriptions'])->name('report.subscription');
        Route::get('report/individual-accounts-form', [ReportTSController::class, 'accounts_form'])->name('report.accounts_form');
        Route::post('report/individual-accounts-datatable', [ReportTSController::class, 'accounts_datatable'])->name('report.accounts_datatable');
        Route::post('report/individual-accounts', [ReportTSController::class, 'accounts'])->name('report.accounts');
        Route::get('report/activated-accounts-form', [ReportTSController::class, 'activated_accounts_form'])->name('report.activated_accounts_form');
        Route::post('report/activated-accounts-datatable', [ReportTSController::class, 'activated_accounts_datatable'])->name('report.activated_accounts_datatable');
        Route::post('report/activated-accounts', [ReportTSController::class, 'activated_accounts'])->name('report.activated_accounts');
        Route::get('report/revenue-form', [ReportTSController::class, 'revenue_form'])->name('report.revenue_form');
        Route::post('report/revenue-datatable', [ReportTSController::class, 'revenue_datatable'])->name('report.revenue_datatable');
        Route::post('report/revenue', [ReportTSController::class, 'revenue'])->name('report.revenue');

        Route::resource('organization.transaction', B2BTransactionController::class);
        Route::post('/organization/{organization}/transaction/get', [B2BTransactionController::class, 'get'])->name('organization.transaction.datatable');

        Route::get('migration/rates', [DataMigController::class, 'rate_form'])->name('migrates.index');
        Route::post('migration/ratesstore', [DataMigController::class, 'rate'])->name('migrates.store');
        Route::get('migration/sample/{type}', [DataMigController::class, 'sample'])->name('migration.sample');

        Route::get('migration/individuals', [DataMigController::class, 'individual_form'])->name('migindividuals.index');
        Route::post('migration/individualsstore', [DataMigController::class, 'individual'])->name('migindividuals.store');

        Route::get('migration/organizations', [DataMigController::class, 'organization_form'])->name('migorganizations.index');
        Route::post('migration/organizations', [DataMigController::class, 'organization'])->name('migorganizations.store');

        Route::get('migration/organization-users', [DataMigController::class, 'corporate_users_form'])->name('migorganizationusers.index');
        Route::post('migration/organization-users', [DataMigController::class, 'corporate_users'])->name('migorganizationusers.store');

        Route::resource('email_template', EmailTemplateController::class);
        Route::post('email_template/get', [EmailTemplateController::class, 'datatable'])->name('email_template.datatable');

        Route::get('simulate', [DebugController::class, 'simulate'])->name('simulate');

        Route::resource('mpesa_blacklist', MpesaBlacklistController::class);
        Route::post('mpesa_blacklist/get', [MpesaBlacklistController::class, 'datatable'])->name('mpesa_blacklist.datatable');
        Route::resource('media_events', MediaEventsController::class);
        Route::post('media_events/get', [MediaEventsController::class, 'datatable'])->name('media_events.datatable');

        Route::get('subscribers/bulk', [SubscriberController::class, 'bulkform'])->name('subscribers.bulk');
        Route::post('subscribers/upload', [SubscriberController::class, 'upload'])->name('subscribers.upload');

        Route::resource('agents', AgentsController::class);
        Route::post('agents/get', [AgentsController::class, 'datatable'])->name('agents.datatable');
        Route::get('agents/bulk/import', [AgentsController::class, 'import'])->name('agents.import');
        Route::post('agents/bulk/upload', [AgentsController::class, 'upload'])->name('agents.upload');
    });

});

// Dedoc\Scramble\Scramble::routes();

Route::middleware(['auth', 'is_owner'])->group(function () {

    Route::get('password/expired', [ExpiredPasswordController::class, 'expired'])->name('password.expired');
    Route::post('password/post_expired', [ExpiredPasswordController::class, 'postExpired'])->name('password.post_expired');
});
Route::get('/user-unsubscribe/{token}', [AuthController::class, 'email_unsubscribe'])->name('user.unsubscribe');

Route::get('dpo_callback', [DPOCallbackController::class, 'dpo_callback'])->name('dpo_callback');

Route::get('debug', [DebugController::class, 'generateUserToken']);
Route::get('check', [DebugController::class, 'checkDPOPayment']);
Route::get('check-mpesa', [DebugController::class, 'checkMpesa']);
Route::get('checkstatus', [DebugController::class, 'transactionStatus']);

Route::post('fasthub/callback', [FastHubController::class, 'callback'])->name('fasthub.callback');

Route::get('test', function (Request $request) {
    //        $coupon="TESTMANY1";
    //        $region = Region::where('code','KE')->first();
    //        $amount = 100;
    //        $con =  new \App\Http\Controllers\API\MpesaCallbackController();
    //        $rate = 3;
    //        $res = $con->discount_calc($coupon,$amount,$region,4,5613189,$rate);

    // $method = \App\Models\PaymentMethod::where('type','fasthub')->first();
    // $util = new \App\Libs\FastHub($method);
    // $res = $util->stkPush(100,'255750900766','PW_MWANASPOTI_ZXVCBOYU','C2B','https://dev-subscribe.nation.africa/fasthub/callback');

    $bundled = Rate::where('product_id', 38)
        ->where('status', 1)
        ->where('category', '!=', 'normal')
        ->where('name', '!=', 'article')
        ->where('currency', 'KES')
        ->orderBy('listorder', 'ASC')
        ->get();

    $temp[] = RateResource::collection($bundled);

    $is_compensatable = (Carbon::parse($request->subscription_date)->gte(today()->subDays(6)) && Carbon::parse($request->subscription_date)->lt(today()));
    $res = $is_compensatable;
    dd($bundled);

});
