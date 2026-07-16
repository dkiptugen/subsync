<?php

namespace App\Libs;

use App\Enums\PaymentTypeIdentifiers;
use App\Traits\Helper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Mpesa
    {
        use Helper;

        public $link;
        public $cert;
        public $timestamp;
        public $consumerkey;
        public $consumersecret;
        public $validation_url;
        public $confirmation_url;
        public $shortcode;
        public $passkey;
        public $msisdn;
        public $cshortcode;
        public $amount;
        public $desc;
        public $ref;
        public $CheckoutRequestID;
        public $type;
        public $initiator;
        public $credential;
        public $TransID;
        public $receiver;
        public $receiverType;
        public $remark;
        public $ocassion;
        public $CommandID;
        public $sender_type;
        public $receiver_type;
        public $from;
        public $to;
        public $accountref;
        public $identifier;
        public $conversionID;
        public $qrversion = 1;
        public $trxcode;

        public $merchantname;
        public $qrformat;
        public $qrtype;
        public $email = 'mkimani@ke.nationmedia.com';
        public $logo  = '';
        public $items;
        public $invoice_no;
        public $name;
        public $billingPeriod;
        public $invoice_name;
        public $due_date;
        public $size;

        public function __construct($env = 'production')
            {
                //Log::info($env);
                $this->timestamp = date('YmdHis');

                if ($env == 'production')
                    {
                        $this->link = 'https://api.safaricom.co.ke';
                        $this->cert = app_path('Resources/Mpesa_public_cert.cer');
                    }
                else
                    {
                        $this->link = 'https://sandbox.safaricom.co.ke';
                        $this->cert = app_path('Resources/Mpesa_public_sandbox_cert.cer');
                    }
            }

        public function generate_token(): string
            {
                return (string) Http::withBasicAuth($this->consumerkey, $this->consumersecret)
                    ->acceptJson()
                    ->connectTimeout(3)
                    ->timeout(10)
                    ->retry([100, 500, 1000])
                    ->get($this->link.config('mpesa.token_link'))
                    ->throw()
                    ->json('access_token');
            }

    /**
     * @param $plaintext
     * @return string
     */
        public function cert_encrypt($plaintext)
            {
                $cert      = $this->cert;
                $fp        = fopen($cert, "r");
                $publicKey = fread($fp, filesize($cert));
                fclose($fp);
                openssl_get_publickey($publicKey);
                openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
                return base64_encode($encrypted);
            }


    /**
     * @return object|array
     */
        public function stkpush()
        : object|array|string
            {
                try
                    {

                        $password  = base64_encode(string: $this->shortcode . $this->passkey . $this->timestamp);
                        $type      = ($this->type == 'TILL') ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline';
                        $shortcode = ($this->type == 'TILL') ? $this->cshortcode : $this->shortcode;
                        $data      = [
                            'BusinessShortCode' => $this->shortcode,
                            'Password'          => $password,
                            'Timestamp'         => $this->timestamp,
                            'TransactionType'   => $type,
                            'Amount'            => $this->amount,
                            'PartyA'            => $this->msisdn,
                            'PartyB'            => $shortcode,
                            'PhoneNumber'       => $this->msisdn,
                            'CallBackURL'       => route('mpesa.stk_push_request'),
                            'AccountReference'  => $this->ref,
                            'TransactionDesc'   => $this->desc
                        ];

                        $token     = $this->generate_token();
                        $req = $this->invoke_server($this->link . config('mpesa.checkout_processlink'), $data, $token, 'post');

                        return $req;

                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }
            }

    /**
     * @return object
     */
        public function checkout_query()
        : object
            {
                try
                    {
                        $password = base64_encode($this->shortcode . $this->passkey . $this->timestamp);
                        $data     = [
                            'BusinessShortCode' => $this->shortcode,
                            'Password'          => $password,
                            'Timestamp'         => $this->timestamp,
                            'CheckoutRequestID' => $this->CheckoutRequestID
                        ];
                        $response = $this->invoke_server($this->link . config('mpesa.checkout_querylink'), $data, $this->generate_token());
                        if(is_string($response))
                            $response = json_decode($response);
                        return $response;
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }

            }

    /**
     * @return object|array
     */
        public function refund()
        : object|array
            {
                try
                    {
                        $data = [
                            'Initiator'              => $this->initiator,
                            'SecurityCredential'     => $this->cert_encrypt($this->credential),
                            'CommandID'              => 'TransactionReversal',
                            'TransactionID'          => $this->TransID,
                            'Amount'                 => $this->amount,
                            'ReceiverParty'          => $this->receiver,
                            'RecieverIdentifierType' => PaymentTypeIdentifiers::from($this->receiverType)->value,
                            'ResultURL'              => config('mpesa.reversalURL'),
                            'QueueTimeOutURL'        => config('mpesa.reversalURL'),
                            'Remarks'                => $this->remark,
                            'Occasion'               => $this->ocassion
                        ];
                        return $this->invoke_server($this->link . config('mpesa.reversal_link'), $data, $this->generate_token());
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }
            }

    /**
     * @return array|object|void
     */
        public function balance()
            {
                try
                    {
                        $data = [
                            'Initiator'          => $this->initiator,
                            'SecurityCredential' => $this->cert_encrypt($this->credential),
                            'CommandID'          => 'AccountBalance',
                            'PartyA'             => $this->shortcode,
                            'IdentifierType'     => PaymentTypeIdentifiers::from($this->type)->value,
                            'Remarks'            => $this->remark,
                            'QueueTimeOutURL'    => config('mpesa.accountbalcallback'),
                            'ResultURL'          => config('mpesa.accountbalcallback')
                        ];

                        return $this->invoke_server($this->link . config('mpesa.balance_link'), $data, $this->generate_token());
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }
            }

    /**
     * @return array|object|void
     */
        public function RegisterURL()
            {

                try
                    {
                        $data = [
                            'ValidationURL'   => $this->validation_url,
                            'ConfirmationURL' => $this->confirmation_url,
                            'ResponseType'    => 'Cancelled',
                            'ShortCode'       => $this->shortcode
                        ];
                        return $this->invoke_server($this->link . config('mpesa.c2b_regiterUrl'), $data, $this->generate_token());

                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }
            }

    /**
     * @return array|object|void
     */
        public function b2b()
            {
                try
                    {
                        $data = [
                            'Initiator'              => $this->initiator,
                            'SecurityCredential'     => $this->cert_encrypt($this->credential),
                            'CommandID'              => $this->CommandID,
                            'SenderIdentifierType'   => PaymentTypeIdentifiers::from($this->sender_type)->value,
                            'RecieverIdentifierType' => PaymentTypeIdentifiers::from($this->receiver_type)->value,
                            'Amount'                 => $this->amount,
                            'PartyA'                 => $this->from,
                            'PartyB'                 => $this->to,
                            'AccountReference'       => $this->accountref,
                            'Remarks'                => $this->remark,
                            'QueueTimeOutURL'        => config('mpesa.b2bcallback'),
                            'ResultURL'              => config('mpesa.b2bcallback')
                        ];
                        return $this->invoke_server($this->link . config('mpesa.b2b_link'), $data, $this->generate_token());
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }

            }

    /**
     * @return array|object|void
     */
        public function b2c()
            {
                try
                    {
                        $data = [
                            'InitiatorName'      => $this->initiator,
                            'SecurityCredential' => $this->cert_encrypt($this->credential),
                            'CommandID'          => $this->CommandID,
                            'Amount'             => $this->amount,
                            'PartyA'             => $this->shortcode,
                            'PartyB'             => $this->msisdn,
                            'Remarks'            => $this->remark,
                            'QueueTimeOutURL'    => config('mpesa.b2ccallback'),
                            'ResultURL'          => config('mpesa.b2ccallback'),
                            'Occasion'           => $this->ocassion
                        ];
                        return $this->invoke_server($this->link . config('mpesa.b2c_link'), $data, $this->generate_token());
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }

            }

    /**
     * @return array|object|void
     */
        public function transactionstatus()
            {
                try
                    {
                        $data = [
                            'Initiator'              => $this->initiator,
                            'SecurityCredential'     => $this->cert_encrypt($this->credential),
                            'CommandID'              => 'TransactionStatusQuery',
                            'TransactionID'          => $this->TransID,
                            'PartyA'                 => $this->msisdn,
                            'IdentifierType'         => PaymentTypeIdentifiers::from($this->identifier)->value,
                            'ResultURL'              => config('mpesa.transtatURL'),
                            'QueueTimeOutURL'        => config('mpesa.transtatURL'),
                            'Remarks'                => $this->remark,
                            'Occasion'               => $this->ocassion,
                            'OriginalConversationID' => $this->conversionID
                        ];


                        return $this->invoke_server($this->link . config('mpesa.transtat_link'), $data, $this->generate_token());
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }

            }

    /**
     * @return array|object|void
     */
        public function qr()
            {
                try
                    {
                        $data = [
                            "QRVersion"    => $this->qrversion,
                            "Size"         => $this->size,
                            "TrxCode"      => $this->trxcode, //BG,WA,PB,SM,SB
                            "CPI"          => $this->shortcode,
                            "MerchantName" => $this->merchantname,
                            "Amount"       => $this->amount,
                            "RefNo"        => $this->ref,
                            "QRFormat"     => $this->qrformat, //1: image, 2: QR String, 3: Binary, 4: PDF
                            "QRType"       => $this->qrtype// S : Static, D : Dynamic
                        ];

                        return $this->invoke_server($this->link . config('mpesa.qrcode'), $data, $this->generate_token());
                        //return $this->invoke_server("http://example.info", $data, $this->generate_token());
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }
            }

        public function billManagerOptin($state = 0)
            {
                try
                    {
                        $data = [
                            "shortcode"       => $this->shortcode,
                            "logo"            => $this->logo,
                            "email"           => $this->email,
                            "officialContact" => $this->msisdn,
                            "sendReminders"   => 1,
                            "callbackUrl"     => config('mpesa.billManagerOptinURL')
                        ];
                        $link = ($state == 0) ? config('mpesa.billMOptinLink') : config('mpesa.billMChangeOptinLink');

                        return $this->invoke_server($this->link . $link, $data, $this->generate_token());
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }
            }

        public function billManagerSingleInvoice()
            {
                try
                    {
                        foreach ($this->items as $item)
                            {
                                $invoiceItem[] = [
                                    "itemName" => $item['item_name'],
                                    "amount"   => $item['item_amount']
                                ];
                            }
                        $data = [
                            "externalReference" => $this->invoice_no,
                            "billedFullName"    => $this->name,
                            "billedPhoneNumber" => $this->msisdn,
                            "billedPeriod"      => $this->billingPeriod,
                            "invoiceName"       => $this->invoice_name,
                            "dueDate"           => $this->due_date,
                            "accountReference"  => $this->ref,
                            "amount"            => $this->amount,
                            "invoiceItems"      => $invoiceItem
                        ];


                        return $this->invoke_server($this->link . config('mpesa.billMSingleInvoice'), $data, $this->generate_token());
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }
            }

        public function billManagerCancelSingleInvoice($data)
            {
                try
                    {
                        return $this->invoke_server($this->link . config('mpesa.billMCancelSingleIn'), $data, $this->generate_token());
                    }
                catch (HttpException $e)
                    {
                        Log::error($e->getMessage());
                    }
            }
    }
