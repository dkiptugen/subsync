<?php
namespace App\Traits;


use Illuminate\Support\Facades\Http;


trait Helper
    {
        public function invoke_server($link,$dt,$token,$method='post')
            {
                if($method == 'post')
                    $data       =   Http::withHeaders(['Content-Type:application/json','Authorization: Bearer '.$token])
                                        ->connectTimeout(3)
                                        ->timeout(10)
                                        ->retry([100, 500], throw: false)
                                        ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                                        ->withToken($token)
                                        ->post($link,$dt);
                else if($method == 'put')
                    $data       =   Http::withHeaders(['Content-Type:application/json','Authorization: Bearer '.$token])
                                        ->connectTimeout(3)
                                        ->timeout(10)
                                        ->retry([100, 500], throw: false)
                                        ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                                        ->withToken($token)
                                        ->put($link,$dt);
                else if($method == 'get')
                    $data       =   Http::withHeaders(['Content-Type:application/json','Authorization: Bearer '.$token])
                                        ->connectTimeout(3)
                                        ->timeout(10)
                                        ->retry([100, 500], throw: false)
                                        ->withOptions(['verify' => app_path("Resources/cacert.pem"), 'http_errors' => false])
                                        ->withToken($token)
                                        ->get($link,$dt);

                if($data->successful())
                    {
                        return $data->object();
                    }
                return $data->body();
//                throwException((new \Exception())->notification());
            }
        public function msisdnFormatter(String $msisdn,$prefix=254,int $size=9) :String
            {
                return $prefix.substr($msisdn,-($size));
            }

    }
