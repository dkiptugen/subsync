<?php
namespace App\Libs;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Pesapal
    {
        public $link;
        public $consumerkey;
        public $consumersecret;
        public function __construct($config,$env = 'production')
            {
                if($env != 'production')
                    {
                        $this->link = 'https://pay.pesapal.com/v3';
                    }
                else
                    {
                        $this->link = 'https://cybqa.pesapal.com/pesapalv3';
                    }
                $this->consumerkey      =   $config['consumer_key'];
                $this->consumersecret   =   $config['consumer_secret'];
            }
        public function getToken()
            {
                $post   =   [
                    'consumer_key'      =>  $this->consumerkey,
                    'consumer_secret'   =>  $this->consumersecret
                ];
                $data   =   Http::accept('application/json')
                    ->contentType('application/json')
                    ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                    ->post($this->link.'/api/Auth/RequestToken',$post);
                if($data->successful())
                    {
                        return $data->object();
                    }

            }
        public function ipnRegister()
            {
                $post   =   [
                    'url'                   =>  '',
                    "ipn_notification_type" =>  "GET"
                ];
                Cache::put('access_token',$this->getToken()->token,Carbon::now()->diffInSeconds(Carbon::parse($this->getToken()->expiryDate)));

                $data = Http::withBasicAuth(Cache::get('access_token'))
                    ->accept('application/json')
                    ->contentType('application/json')
                    ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                    ->post($this->link.'/api/URLSetup/RegisterIPN',$post);
                if($data->successful())
                    {
                        return $data->object();
                    }
            }
        public function getIpn()
            {
                $token = Cache::put('access_token',$this->getToken()->token,Carbon::now()->diffInSeconds(Carbon::parse($this->getToken()->expiryDate)));
                $data = Http::withBasicAuth($token)
                    ->accept('application/json')
                    ->contentType('application/json')
                    ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                    ->get($this->link.'/api/URLSetup/GetIpnList');
                if($data->successful())
                    {
                        return $data->object();
                    }
            }
        public function submitOrder(Request $request)
            {
                $post   =   [
                    "id"                =>  $request->id,
                    "currency"          =>  $request->currency,
                    "amount"            =>  $request->amount,
                    "description"       =>  $request->description,
                    "callback_url"      =>  $request->responseUrl,
                    "notification_id"   =>  Str::uuid(),
                    "billing_address"   =>  [
                        "email_address" => $request->email,
                        "phone_number"  => $request->phone_no,
                        "country_code"  => $request->country_code,
                        "first_name"    => $request->firstname,
                        "middle_name"   => $request->middlename,
                        "last_name"     =>  $request->lastname,
                        "line_1"        =>  $request->line1,
                        "line_2"        =>   $request->line2,
                        "city"          =>  $request->city,
                        "state"         =>  $request->state,
                        "postal_code"   =>  $request->postal_code,
                        "zip_code"      =>  $request->zip_code
                    ]

                ];
                Cache::put('access_token',$this->getToken()->token,Carbon::now()->diffInSeconds(Carbon::parse($this->getToken()->expiryDate)));
                $data  = Http::withBasicAuth(Cache::get('access_token'))
                    ->accept('application/json')
                    ->contentType('application/json')
                    ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                    ->post($this->link.'/api/Transactions/SubmitOrderRequest',$post);
                if($data->successful())
                    {
                        return $data->object();
                    }
            }
        public function transactionStatus(Request $request)
            {
                $post = ['orderTrackingId'=>$request->order_tracking_id];
                Cache::put('access_token',$this->getToken()->token,Carbon::now()->diffInSeconds(Carbon::parse($this->getToken()->expiryDate)));
                $data = Http::withBasicAuth(Cache::get('access_token'))
                    ->accept('application/json')
                    ->contentType('application/json')
                    ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                    ->get($this->link.'/api/Transactions/GetTransactionStatus',$post);
                if($data->successful())
                    {
                        return $data->object();
                    }
            }
    }
