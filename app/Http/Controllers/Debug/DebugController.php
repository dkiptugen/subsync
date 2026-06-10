<?php

namespace App\Http\Controllers\Debug;

use App\Enums\PaymentTypeIdentifiers;
use App\Http\Controllers\Controller;
use App\Http\Services\EmailService;
use App\Libs\DPO;
use App\Libs\Mpesa;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\SubscriptionExpired;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Passport;

class DebugController extends Controller
{
    public function generateUserToken(Request $request)
    {
        if($request->key == 'ashken')
        {
            $user = null;
            $email = $request->email;
            $phone = $request->phone;
            if($email)
                $user = User::where('email',$email)->limit(1)->first();
            else
                $user = User::where('phone',$phone)->limit(1)->first();

            if($user)
            {
                Passport::actingAs($user);

                $tokenResult = $user->createToken('Subsync Password Grant Client');

                $token = @$tokenResult->accessToken;

                $response = [];
                if($token)
                {
                    $params = [
                        'product' => $request->product,
                        'subscription_date' => $request->date ?? date_create('now')->format('Y-m-d'),
                    ];

                    $localrequest = Request::create(
                        'api/subscription',
                        'POST',
                        [], // Empty array as we will manually set the body below
                        [], // No cookies needed
                        [], // No files needed
                        [], // No server parameters needed
                        json_encode($params) // Set JSON-encoded body here
                    );

                    $localrequest->headers->set('Content-Type', 'application/json');
                    $localrequest->headers->set('appkey', 'IsbxAPRKl1Bv7ed1z3VvzGzicbm2Go8V');
                    $localrequest->headers->set('Authorization', 'Bearer ' . $token);

                    $response = app()->handle($localrequest)->getContent();
                    $response = json_decode($response);
                }

                return response()->json([
                    'access_token' => $token,
                    'response' => $response
                ]);
            }
            else{
                return response()->json([
                    'message' => 'User not found',
                ]);
            }
        }

        return response()->json([
            'message' => 'Not authorized',
        ]);
    }

    public function checkDPOPayment(Request $request)
    {
        if ($request->key == 'ashken' && $request->has('transaction_id')) {
            $transid = $request->transaction_id;
            $dpo = new DPO();
            $trans = Transaction::find($transid);
            if($trans)
            {
                $dpo->transaction_token = $trans->transaction_token;
                $dpo->company_token = $trans->payment_method->configuration['company_token'];
                $dpo->accountref = $trans->identifier;
                $statusResult = $dpo->verifyToken();

                $statusCode = simplexml_load_string($statusResult);
                dd($statusCode);
            }
        }
    }

    public function checkMpesa()
    {
        $id = '7A51YXGM';
        $transaction = Transaction::with(['payment_method'])->where('identifier',$id)->first();
        $response    = (object)$transaction->response;
        $payment     = $transaction->payment_method;
        $mpesa                    = new Mpesa();
        $mpesa->CheckoutRequestID = $response->CheckoutRequestID;
        $mpesa->consumerkey       = $payment->configuration['consumer_key'];
        $mpesa->consumersecret    = $payment->configuration['consumer_secret'];
        $mpesa->passkey           = $payment->configuration['pass_key'];
        $mpesa->shortcode         = $payment->configuration['shortcode'];
        $result                   = $mpesa->checkout_query ();

        dd($mpesa->link,$result);
    }

    public function transactionStatus()
    {
        $mpesa = new Mpesa();
        $payment = PaymentMethod::find(4);
        $mpesa->consumerkey       = $payment->configuration['consumer_key'];
        $mpesa->consumersecret    = $payment->configuration['consumer_secret'];
        $mpesa->passkey           = $payment->configuration['pass_key'];
        $mpesa->shortcode         = $payment->configuration['shortcode'];

        $token = $mpesa->generate_token();

        //dd($token);

        $shortcode = '500500';
        $identifierType = 4;
        $identifier = PaymentTypeIdentifiers::from($identifierType)->value;
        $trans_id = '7A51YXGM';
        $callbackurl = 'https://webhook.site/fc45f6e3-dd57-4f2f-9a8c-a38cd7f4e6ba';
        $trans_id  = 'SK47IDKGEV';
        $data = [
            'Initiator'              => 'dgicheru',
            'SecurityCredential'     => $mpesa->cert_encrypt('SpyEagle,1'),
            'CommandID'              => 'TransactionStatusQuery',
            //'TransactionID'          => $trans_id,
            'PartyA'                 => $shortcode,
            'IdentifierType'         => $identifier,
            'ResultURL'              => $callbackurl,
            'QueueTimeOutURL'        => $callbackurl,
            'Remarks'                => 'Paybill payment',
            'Occasion'               => 'Paybill payment',
            'OriginalConversationID' => '4fe9-4cd8-ab70-95b3e86ac48937724569'
        ];

        $client = new Client(['headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token
        ],
            'verify'=> base_path('/cacert.pem'),'http_errors'=>true]);

        $response = $client->post($mpesa->link.config('mpesa.transtat_link'), [
            'body' => json_encode($data)
        ]);

        $result = json_decode($response->getBody()->getContents());
        dd($result);

    }

    public function simulate(Request $request)
    {
        $user = User::where('email',$request->email)->limit(1)->first();
        if($user)
        {
            Auth::login($user);
            return redirect('manage/product');
        }
    }

}
