<?php

namespace App\Libs;

use App\Models\PaymentMethod;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Mockery\Exception;

class FastHub
{

    public $method;
    public function __construct(PaymentMethod $method)
    {
        $this->method = $method;
    }

    public function stkPush($amount,$msisdn,$order_id,$type='C2B',$callback_url="")
    {
        $config = (object)$this->method->configuration;
        $key = $config->signature_key;
        $signature_value = $amount.$msisdn.$order_id.$type.$callback_url;

        $payload = [
            "type" => $type,
            "amount" => $amount,
            "msisdn" => $msisdn,
            "order_id" =>$order_id,
            "callback_url" =>$callback_url,
            "signature" => hash_hmac('sha512', $signature_value, $key)
        ];

        return $this->request('/api/payments', $payload);
    }

    public function payment_status($transaction_id)
    {
        $payload = ['transaction_id' =>$transaction_id];

       return $this->request('/api/payments/enquiry', $payload);
    }

    public function request($uri, $payload)
    {
        $config = (object)($this->method->configuration);
        $url = $config->endpoint.$uri;
        $client = new Client([
            'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Client-Id'=>$config->client_id,
            'X-Client-Secret'=>$config->client_secret,
        ],
            'verify'=> base_path('/cacert.pem'),'http_errors'=>true]);

        //dd($url,json_encode($payload),json_encode($client->getConfig('headers')));

        $response = null;
        try{
            $response = $client->post($url, [
                'body' => json_encode($payload)
            ]);
        }catch (RequestException $e)
        {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $html = (string) $response->getBody();

                // Return the actual HTML page to the browser
                return response($html)
                    ->header('Content-Type', 'text/html')
                    ->setStatusCode($response->getStatusCode());
            }
        }

        $result = json_decode(@$response->getBody()->getContents());

        return $result;
    }
}
