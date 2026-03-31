<?php


use App\Events\PaymentMade;
use App\Jobs\ExtendBundleChildSubscriptions;
use App\Jobs\Kafka\SuccessPaymentEventJob;
use App\Libs\Mpesa;
use App\Models\Cart;
use App\Models\PaymentMethod;
use App\Models\Point;
use App\Models\Product;
use App\Models\Rate;
use App\Models\Region;
use App\Models\Subscription;
use App\Models\SubscriptionGroup;
use App\Models\Transaction;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


if(!function_exists('mpesa_transaction_status'))
{
    function mpesa_transaction_status($payment_method,$receipt_number,$identifier_type,$callbackurl,$timeouturl,$occasion='Payment',$env='production')
    {
        $mpesa = new Mpesa();
        $mpesa->consumerkey       = $payment_method->configuration['consumer_key'];
        $mpesa->consumersecret    = $payment_method->configuration['consumer_secret'];
        $mpesa->passkey           = $payment_method->configuration['pass_key'];
        $mpesa->shortcode         = $payment_method->configuration['shortcode'];
        $initiator = @$payment_method->configuration['initiator'];
        $initiator_password = @$payment_method->configuration['initiator_password'];
        $token = $mpesa->generate_token();
        $data = [
            'Initiator'              => $initiator,
            'SecurityCredential'     => mpesa_cert_encrypt($initiator_password),
            'CommandID'              => 'TransactionStatusQuery',
            'TransactionID'          => $receipt_number,
            'PartyA'                 => $mpesa->shortcode,
            'IdentifierType'         => $identifier_type,
            'ResultURL'              => $callbackurl,
            'QueueTimeOutURL'        => $timeouturl,
            'Remarks'                => 'Paybill Payment check',
            'Occasion'               => $occasion,
            //'OriginalConversationID' => '4fe9-4cd8-ab70-95b3e86ac48937724569'
        ];

        $link = $env == 'production' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';

        $client = new Client(['headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token
        ],
            'verify'=> base_path('/cacert.pem'),'http_errors'=>false]);

        $response = $client->post($link.config('mpesa.transtat_link'), [
            'body' => json_encode($data)
        ]);

        $result = json_decode(@$response->getBody()->getContents());

        return $result;
    }
}

if(!function_exists('mpesa_cert_encrypt'))
{
    function mpesa_cert_encrypt($plaintext,$env='production')
    {
        $cert      = $env == 'production' ? app_path('Resources/Mpesa_public_cert.cer') : app_path('Resources/Mpesa_public_sandbox_cert.cer') ;
        $fp        = fopen($cert, "r");
        $publicKey = fread($fp, filesize($cert));
        fclose($fp);
        openssl_get_publickey($publicKey);
        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        return base64_encode($encrypted);
    }
}

if(!function_exists('activate_mpesa_cart_subscription'))
{
    function activate_mpesa_cart_subscription($cart,$transcode, $amount, $receipt, $name, $number, $transtime, $response)
    {
        $transactions =  Transaction::with(['subscription.rate','user'])
            ->whereHas('subscription',function ($query) use($cart){
                $query->where('cart_id',$cart->id);
            })->where('channel','like','%mpesa%')->get();

        foreach ($transactions as $transaction)
        {
            try
            {
                $kafka_data =   [
                    'transaction'    => $transaction,
                    'user'           => $transaction->user,
                    'payment_method' => 'Mpesa',
                    'subscription'   => $transaction->subscription,
                ];
                SuccessPaymentEventJob::dispatch($kafka_data);
            }
            catch (\Exception $e)
            {
                Log::error("Kafka successful payment", [$e->getMessage()]);
            }

            $transaction->amount_paid =  $transaction->amount;
            $transaction->status           = 1;
            $transaction->receipt          = $receipt;
            $transaction->initiator        = $name . ' - ' . $number;
            $transaction->response         = $response;
            $transaction->transaction_date = Carbon::parse($transtime)->toDateTimeString();
            $transaction->save();

            $transaction->subscription()->update([
                'status' => 1,
            ]);

            $transaction->identifier = $cart->identifier;

            event(new PaymentMade($transaction));
        }
        Cart::where('id',$cart->id)->update(['status' => 1]);
    }
}

if(!function_exists('is_bad_url'))
{
    function is_bad_url($url)
    {
        $illegalDomains = ['localhost', '127.0.0.1'];

        // Parse the domain from the URL
        $host = parse_url($url, PHP_URL_HOST);

        // Check if the host matches any illegal domain
        return in_array($host, $illegalDomains, true);
    }
}

if(!function_exists('get_country_codes'))
{
    function get_country_codes()
    {
        $countries = [
            'Kenya' => '+254',
            'Afghanistan' => '+93',
            'Albania' => '+355',
            'Algeria' => '+213',
            'Andorra' => '+376',
            'Angola' => '+244',
            'Argentina' => '+54',
            'Armenia' => '+374',
            'Australia' => '+61',
            'Austria' => '+43',
            'Azerbaijan' => '+994',
            'Bahamas' => '+1-242',
            'Bahrain' => '+973',
            'Bangladesh' => '+880',
            'Belarus' => '+375',
            'Belgium' => '+32',
            'Belize' => '+501',
            'Benin' => '+229',
            'Bhutan' => '+975',
            'Bolivia' => '+591',
            'Bosnia and Herzegovina' => '+387',
            'Botswana' => '+267',
            'Brazil' => '+55',
            'Bulgaria' => '+359',
            'Burkina Faso' => '+226',
            'Burundi' => '+257',
            'Cambodia' => '+855',
            'Cameroon' => '+237',
            'Canada' => '+1',
            'Cape Verde' => '+238',
            'Central African Republic' => '+236',
            'Chad' => '+235',
            'Chile' => '+56',
            'China' => '+86',
            'Colombia' => '+57',
            'Comoros' => '+269',
            'Congo' => '+242',
            'Costa Rica' => '+506',
            'Croatia' => '+385',
            'Cuba' => '+53',
            'Cyprus' => '+357',
            'Czech Republic' => '+420',
            'Denmark' => '+45',
            'Djibouti' => '+253',
            'Dominica' => '+1-767',
            'Dominican Republic' => '+1-809',
            'Ecuador' => '+593',
            'Egypt' => '+20',
            'El Salvador' => '+503',
            'Equatorial Guinea' => '+240',
            'Eritrea' => '+291',
            'Estonia' => '+372',
            'Eswatini' => '+268',
            'Ethiopia' => '+251',
            'Fiji' => '+679',
            'Finland' => '+358',
            'France' => '+33',
            'Gabon' => '+241',
            'Gambia' => '+220',
            'Georgia' => '+995',
            'Germany' => '+49',
            'Ghana' => '+233',
            'Greece' => '+30',
            'Grenada' => '+1-473',
            'Guatemala' => '+502',
            'Guinea' => '+224',
            'Guyana' => '+592',
            'Haiti' => '+509',
            'Honduras' => '+504',
            'Hungary' => '+36',
            'Iceland' => '+354',
            'India' => '+91',
            'Indonesia' => '+62',
            'Iran' => '+98',
            'Iraq' => '+964',
            'Ireland' => '+353',
            'Israel' => '+972',
            'Italy' => '+39',
            'Jamaica' => '+1-876',
            'Japan' => '+81',
            'Jordan' => '+962',
            'Kazakhstan' => '+7',
            'Kuwait' => '+965',
            'Laos' => '+856',
            'Latvia' => '+371',
            'Lebanon' => '+961',
            'Lesotho' => '+266',
            'Liberia' => '+231',
            'Libya' => '+218',
            'Lithuania' => '+370',
            'Luxembourg' => '+352',
            'Madagascar' => '+261',
            'Malawi' => '+265',
            'Malaysia' => '+60',
            'Maldives' => '+960',
            'Mali' => '+223',
            'Malta' => '+356',
            'Mauritania' => '+222',
            'Mauritius' => '+230',
            'Mexico' => '+52',
            'Moldova' => '+373',
            'Monaco' => '+377',
            'Mongolia' => '+976',
            'Montenegro' => '+382',
            'Morocco' => '+212',
            'Mozambique' => '+258',
            'Myanmar' => '+95',
            'Namibia' => '+264',
            'Nepal' => '+977',
            'Netherlands' => '+31',
            'New Zealand' => '+64',
            'Nicaragua' => '+505',
            'Niger' => '+227',
            'Nigeria' => '+234',
            'North Korea' => '+850',
            'Norway' => '+47',
            'Oman' => '+968',
            'Pakistan' => '+92',
            'Palestine' => '+970',
            'Panama' => '+507',
            'Paraguay' => '+595',
            'Peru' => '+51',
            'Philippines' => '+63',
            'Poland' => '+48',
            'Portugal' => '+351',
            'Qatar' => '+974',
            'Romania' => '+40',
            'Russia' => '+7',
            'Rwanda' => '+250',
            'Saudi Arabia' => '+966',
            'Senegal' => '+221',
            'Serbia' => '+381',
            'Seychelles' => '+248',
            'Sierra Leone' => '+232',
            'Singapore' => '+65',
            'Slovakia' => '+421',
            'Slovenia' => '+386',
            'Somalia' => '+252',
            'South Africa' => '+27',
            'South Korea' => '+82',
            'Spain' => '+34',
            'Sri Lanka' => '+94',
            'Sudan' => '+249',
            'Sweden' => '+46',
            'Switzerland' => '+41',
            'Syria' => '+963',
            'Tanzania' => '+255',
            'Thailand' => '+66',
            'Togo' => '+228',
            'Tunisia' => '+216',
            'Turkey' => '+90',
            'Uganda' => '+256',
            'Ukraine' => '+380',
            'United Arab Emirates' => '+971',
            'United Kingdom' => '+44',
            'United States' => '+1',
            'Uruguay' => '+598',
            'Uzbekistan' => '+998',
            'Venezuela' => '+58',
            'Vietnam' => '+84',
            'Yemen' => '+967',
            'Zambia' => '+260',
            'Zimbabwe' => '+263',
        ];
        return $countries;
    }
}

if(!function_exists('email_link'))
{
    function email_link($platform)
    {
        $env = env('APP_ENV');

        $environment = str_contains($env,'prod') ? 'prod' : 'dev';
        $links = [
            'prod' =>[
                'epaper' => 'https://epaper.nation.africa/account/reset-password',
                'paywall' => 'https://www.nation.africa/africa/account/reset-password',
            ],
            'dev'=>[
                'epaper' => 'https://epaper-beta.nation.co.ke/account/reset-password',
                'paywall' => 'https://beta2020.nation.co.ke/nation/account/reset-password',
            ]
        ];

        $link = $links[$environment][$platform];

        return $link;
    }
}

if(!function_exists('extract_base_url'))
{
    function extract_base_url($url)
    {
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            return null; // Return null if URL is invalid
        }
        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    }
}

if(!function_exists('resolve_currency'))
{
    function resolve_currency($region_code,$default_currency)
    {
        $exempt_list = Region::where('direct_mapping',1)->pluck('currency_code','code')->toArray();
        if(count($exempt_list) && !in_array($region_code,array_keys($exempt_list)))
            return 'USD';

        return $default_currency;
    }
}

if(!function_exists('activate_coin_subscription'))
{
    function activate_coin_subscription($product_identifier,$rate_name,$event,$count=0)
    {
        $current = Point::where('identifier',$event)->count();
        if($count > 0 && $current > $count)
            return null;

        $product = Product::where('identifier',$product_identifier)->first();
        if(!$product)
            return null;
        $rate = Rate::where('product_id',$product->id)->where('name',$rate_name)->first();
        if(!$rate)
            return null;

        $user = auth()->user();

        $exists = Subscription::where('user_id',$user->id)
            ->where('product_id',$product->id)
            ->whereDate('expiry_date','>=',date_create('now')->format('Y-m-d'))
            ->orderBy('expiry_date','DESC')
            ->limit(1)
            ->first();

        if($exists && $exists->activator_reason == $event)
            return null;

        $identifier = strtoupper(Str::random(8));
        $start_date = Carbon::now()->startOfDay();
        if($exists)
            $start_date = Carbon::parse($exists->expiry_date)->addDays(1)->startOfDay();

        $end_date = Carbon::parse($start_date)->addDays($rate->period)->endOfDay();

        if($product->type == 'epaper')
            $end_date = Carbon::parse($start_date)->addDays($rate->period-1)->endOfDay();

        $subg = SubscriptionGroup::firstOrCreate(['subdate' => \Carbon\Carbon::now()->format('Y-m-d')],
            ['identifier' => Str::random(8)]);

        $subscription = new Subscription();
        $subscription->identifier = $identifier;
        $subscription->product_id = $product->id;
        $subscription->subscription_group_id = $subg->id;
        $subscription->subscription_date = $start_date;
        $subscription->expiry_date = $end_date;
        $subscription->rate_id = @$rate->id;
        $subscription->status = 1;
        $subscription->user_id = $user->id;
        $subscription->activator_reason = $event;
        $subscription->save();
        $identifier = strtoupper(Str::random(8));
        $payment_method = PaymentMethod::orderBy('id','asc')->limit(1)->first();
        $transaction = new Transaction();
        $transaction->identifier = $identifier;
        $transaction->total_amount = 0;
        $transaction->amount = 0;
        $transaction->subscription_id = $subscription->id;
        $transaction->payment_method_id = $payment_method->id;
        $transaction->channel = $request->description ?? 'promo';
        $transaction->currency = @$rate->currency ?? 'KES';
        $transaction->status = 1;
        $transaction->user_id = $user->id;
        $transaction->save();

        return $subscription;
    }
}

if(!function_exists('attach_products')){
    function attach_products($subscription){
        $subscription->load(['product.children','rate']);

        $ids = [];

        if($subscription->rate->category == 'normal')
        {
            return;
        }
        elseif($subscription->rate->category == 'premium')
        {
            if(is_null($subscription->product->counterpart))
            {
                return;
            }
            array_push($ids,$subscription->product->counterpart->id);
        }
        elseif ($subscription->rate->category == 'premium plus')
        {
            if($subscription->product->children->isEmpty()){
                return;
            }

            $ids = $subscription->product->children->pluck('id')->toArray();
        }

        $subscription->products()->attach($ids);
    }
}

if(!function_exists('give_all_access')){
    function give_all_access(Subscription $subscription)
    {
        $subscription->load(['product.region','transaction','rate']);
        $product = $subscription->product;
        $region = $product->region;
        $transaction = $subscription->transaction;
        //if subscription is an e-paper archive no all access
        $products = Product::where('region_id',$region->id)->whereNotIn('id',[$product->id])->get();

        foreach($products as $nproduct){
            $identifier = strtoupper(Str::random(8));

            $subg = SubscriptionGroup::firstOrCreate(['subdate' => \Carbon\Carbon::now()->format('Y-m-d')],
                ['identifier' => Str::random(8)]);

            $exists = Subscription::where('product_id',$nproduct->id)
                ->where('status',1)
                ->where('user_id',$subscription->user_id)
                ->whereDate('expiry_date','>=',date_create('now')->format('Y-m-d'))
                ->orderBy('expiry_date','desc')
                ->first();

            $rate = $subscription->rate;

            $days =  $rate->period;
            $start_date = $subscription->subscription_date;
            if($exists && @$exists->category != 'upgrade')
            {
                $start_date = Carbon::parse($exists->expiry_date)->startOfDay();
            }
            $end_date = Carbon::parse($start_date)->addDays($days)->endOfDay();

            $nsubscription = new Subscription();
            $nsubscription->identifier = $identifier;
            $nsubscription->product_id = $nproduct->id;
            $nsubscription->subscription_group_id = $subg->id;
            $nsubscription->subscription_date = $start_date;
            $nsubscription->expiry_date = $end_date;
            $nsubscription->rate_id = $subscription->rate_id;
            $nsubscription->status = 1;
            $nsubscription->user_id = $subscription->user_id;
            $nsubscription->activator_reason = 'all access for '.$transaction->receipt;
            $nsubscription->save();

            $identifier = strtoupper(Str::random(8));
            $ntransaction = new Transaction();
            $ntransaction->identifier = $identifier;
            $ntransaction->subscription_id = $nsubscription->id;
            $ntransaction->payment_method_id = $transaction->payment_method_id;
            $ntransaction->total_amount = 0;
            $ntransaction->amount = 0;
            $ntransaction->amount_paid = 0;
            $ntransaction->channel = $transaction->channel;
            $ntransaction->currency = $transaction->currency;
            $ntransaction->receipt = $transaction->receipt;
            $ntransaction->apple_transaction_id = $transaction->transaction_id;
            $ntransaction->status = 1;
            $ntransaction->user_id = $subscription->user_id;
            $ntransaction->save();
        }
    }
}

if(!function_exists('deactivate_after_upgrade')){
    function deactivate_after_upgrade($identifier)
    {
        $deactivation_sub = Subscription::where('deactivation_identifier', $identifier)->first();

        if($deactivation_sub)
        {
            Subscription::where('status',1)
                ->where('user_id',$deactivation_sub->user_id)
                ->where('expire_after_upgrade',1)
                ->where('expiry_date','>=',now())
                ->update(['expiry_date' => Carbon::now()->subDays(1)->toDateTimeString()]);
        }
    }
}

if(!function_exists('match_upgrade_currency')){
    function match_upgrade_currency($amount,$existing_currency,$new_currency)
    {
        $value = $amount;
        $table = [
            'KES' => 130,
            "UGX" => 3700,
            "TZS" => 2600,
        ];

        if($existing_currency !=="USD" && $new_currency =="USD")
        {
            $value = ceil( ($amount / $table[$existing_currency]) );
        }
        if($existing_currency == "USD" && $new_currency !=="USD")
        {
            $value = ceil( ($amount * $table[$new_currency]) );
        }

        return $value;
    }
}
