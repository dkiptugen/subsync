<?php

namespace App\Libs;


use Illuminate\Support\Facades\Log;
use Mtownsend\XmlToArray\XmlToArray;
use function simplexml_load_string;


class DPO
    {

        public const API_ENDPOINT = 'https://secure.3gdirectpay.com/API/v6/';//'https://secure.3gdirectpay.com/payv3.php?ID=';
        public $PAY_URL;
        public $redirect_url, $back_url;
        public $company_token;
        public $amount;
        public $currency;
        public $companyref;
        public $firstname;
        public $accountref;
        public $lastname;
        public $email;
        public $service;
        public $renewal = 0;
        public $renewal_interval;
        public $phone;
        public $country;
        public $country_code;
        public $transaction_token;
        public $subscription_token;

        public function __construct()
            {

                $this->PAY_URL = "https://secure.3gdirectpay.com/payv3.php?ID=";//config('custom.DPO.PAYMENT_URL');
            }

        public function verifyToken()
            {

                $xml_request = '<API3G>
                                  <CompanyToken>' . $this->company_token . '</CompanyToken>
                                  <Request>verifyToken</Request>
                                  <TransactionToken>' . $this->transaction_token . '</TransactionToken>
                                  <ACCref>' . $this->accountref . '</ACCref>
                                </API3G>';
                //dd($xml_request);
                return self::genericPost($xml_request);
                //$this->email($this->company_token,$this->transaction_token);
            }

        public function genericPost($xml_request)
            {

                $ch = curl_init();

                if (!$ch)
                    {
                        die("Couldn't initialize a cURL handle");
                    }
                curl_setopt($ch, CURLOPT_URL, self::API_ENDPOINT);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);

                $result = curl_exec($ch);

                curl_close($ch);
                return $result;
            }


        public function Checkout()
            {

                if ((int)$this->renewal == 0)
                    {

                        $xml_request = '<?xml version="1.0" encoding="utf-8"?>
                                            <API3G>
                                                <CompanyToken>' . $this->company_token . '</CompanyToken>
                                                <Request>createToken</Request>
                                                <Transaction>
                                                    <PaymentAmount>' . $this->amount . '</PaymentAmount>
                                                    <PaymentCurrency>' . $this->currency . '</PaymentCurrency>
                                                    <CompanyRef>' . $this->accountref . '</CompanyRef>
                                                    <RedirectURL>' . $this->redirect_url . '</RedirectURL>
                                                    <BackURL>' . $this->back_url . '</BackURL>
                                                    <customerEmail>' . $this->email . '</customerEmail>
                                                    <CompanyRefUnique>0</CompanyRefUnique>
                                                    <PTL>10</PTL>
                                                    <PTLtype>minutes</PTLtype>
                                                </Transaction>
                                                <Services>';
                        foreach ($this->service as $service)
                            {
                                $xml_request .= '
                                                  <Service>
                                                    <ServiceType>' . $service['type'] . '</ServiceType>
                                                    <ServiceDescription>' . $service['description'] . '</ServiceDescription>
                                                    <ServiceDate>' . $service['date'] . '</ServiceDate>
                                                    <ServiceRef>' . $service['ref'] . '</ServiceRef>
                                                  </Service>
                                                ';
                            }
                        $xml_request .= '       </Services>
                                            </API3G>';

                    }
                else
                    {

                        $xml_request = '<?xml version="1.0" encoding="utf-8"?>
                                            <API3G>
                                                <CompanyToken>' . $this->company_token . '</CompanyToken>
                                                <Request>createToken</Request>
                                                <Transaction>
                                                    <PaymentAmount>' . $this->amount . '</PaymentAmount>
                                                    <PaymentCurrency>' . strtoupper($this->currency) . '</PaymentCurrency>
                                                    <CompanyRef>' . $this->accountref . '</CompanyRef>
                                                    <RedirectURL>' . $this->redirect_url . '</RedirectURL>
                                                    <BackURL>' . $this->back_url . '</BackURL>
                                                    <customerEmail>' . $this->email . '</customerEmail>
                                                    <CompanyRefUnique>0</CompanyRefUnique>
                                                    <CompanyAccRef>' . $this->accountref . '</CompanyAccRef>
                                                    <PTL>10</PTL>
                                                    <PTLtype>minutes</PTLtype>
                                                    <AllowRecurrent>1</AllowRecurrent>
                                                </Transaction>
                                                    <Services>';
                        foreach ($this->service as $service)
                            {
                                $xml_request .= '
                                                  <Service>
                                                   <ServiceType>' . $service['type'] . '</ServiceType>
                                                    <ServiceDescription>' . $service['description'] . '</ServiceDescription>
                                                    <ServiceDate>' . $service['date'] . '</ServiceDate>
                                                    <ServiceRef>' . $service['ref'] . '</ServiceRef>
                                                  </Service>
                                                ';
                            }

                        $xml_request .= '</Services>
                                           </API3G>';

                    }
                //Log::info($xml_request);

                return $this->invokeDPOBroker($xml_request);


            }

        public function invokeDPOBroker($xml_request)
            {

                $ch = curl_init();

                if (!$ch)
                    {
                        die("Couldn't initialize a cURL handle");
                    }
                curl_setopt($ch, CURLOPT_URL, self::API_ENDPOINT);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);

                $result = curl_exec($ch);

                curl_close($ch);

               // Log::info($result);

                return $this->processTokenResponse($result);
            }

        public function processTokenResponse($xmldoc)
            {

                //Log::error($xmldoc);
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($xmldoc);
                if ($xml === false)
                    {
                        Log::error('DPO INVALID RESPONSE  => '.$xmldoc);
//                        echo 'Failed to parse XML string. Errors:' . PHP_EOL;
//                        foreach (libxml_get_errors() as $error)
//                            {
//                                echo $error->message . PHP_EOL;
//                            }
                        libxml_clear_errors();
                        return null;
                    }
                else
                    {
                        // XML parsing was successful
                        $result = (string)$xml->Result;


                        return $result == '000' ? ['iframe' => url($this->PAY_URL . $xml->TransToken), 'token' => $xml->TransToken, 'TransRef' => $xml->TransRef] : $xmldoc;
                    }

               // Log::error($xml);
                //dd($xml);


            }

        public function retrieveTokenSub()
            {

                $xml_request = '<API3G>
                                <CompanyToken>' . $this->company_token . '</CompanyToken>
                                <Request>getSubscriptionToken</Request>
                                <SearchCriteria>1</SearchCriteria>
                                <SearchCriteriaValue>' . $this->email . '</SearchCriteriaValue>
                                </API3G>';

                return self::recurrentDPOBroker($xml_request);
            }

        public function recurrentDPOBroker($xml_request)
            {

                $result = self::genericPost($xml_request);

                return $result;
            }

        public function processReccurrentTokenResponse($xmldoc)
            {

                $xml = simplexml_load_string($xmldoc);

                return $xml->Result == '000' ? self::getReccurrentToken() : 0;
            }

        public function getReccurrentToken()
            {

                $xml_request = '<API3G>
                                <CompanyToken>' . $this->company_token . '</CompanyToken>
                                <Request>createToken</Request>
                                <Transaction>
                                <PaymentAmount>' . $this->amount . '</PaymentAmount>
                                <PaymentCurrency>' . $this->currency . '</PaymentCurrency>
                                <CompanyRef>' . $this->companyref . '</CompanyRef>
                                <RedirectURL>' . $this->redirect_url . '</RedirectURL>
                                <BackURL>' . $this->back_url . '</BackURL>
                                <CompanyRefUnique></CompanyRefUnique>
                                <CompanyAccRef>' . $this->accountref . '</CompanyAccRef>
                                <customerFirstName>' . $this->firstname . '</customerFirstName>
                                <customerLastName>' . $this->lastname . '</customerLastName>
                                <customerEmail>' . $this->email . '</customerEmail>
                                <PTL>10</PTL>
                                <PTLtype>minutes</PTLtype>
                                </Transaction>
                                <Services>
                                  <Service>
                                    <ServiceType>' . $this->service[0]['type'] . '</ServiceType>
                                    <ServiceDescription>' . $this->service[0]['description'] . '</ServiceDescription>
                                    <ServiceDate>' . $this->service[0]['date'] . '</ServiceDate>
                                  </Service>
                                </Services>
                                <Additional>
                                  <BlockPayment>MO</BlockPayment>
                                  <BlockPayment>XP</BlockPayment>
                                </Additional>
                                </API3G>';

                $xml = simplexml_load_string(self::genericPost($xml_request));

                return $xml->Result == '000' ? self::newReccurentRequest() : 0;

            }

        public function newReccurentRequest()
            {

                $request = '<API3G>
                            <CompanyToken>' . $this->company_token . '</CompanyToken>
                            <Request>chargeTokenRecurrent</Request>
                            <TransactionToken>' . $this->transaction_token . '</TransactionToken>
                            <subscriptionToken>' . $this->subscription_token . '</subscriptionToken>
                            </API3G>';
                $xml     = simplexml_load_string(self::genericPost($request));
                if ($xml->Result == '000')
                    {
                        return 1;
                    }
                else
                    {
                        //Log::error($xml->ResultExplanation);

                        return 0;
                    }

            }

        public function cancel()
            {

                $xml = '<?xml version="1.0" encoding="utf-8"?>
                        <API3G>
                          <CompanyToken>' . $this->company_token . '</CompanyToken>
                          <Request>cancelToken</Request>
                          <TransactionToken>' . $this->transaction_token . '</TransactionToken>
                        </API3G>';
                $xml = simplexml_load_string(self::genericPost($xml));
                if ($xml->Result == '000')
                    {
                        return 1;
                    }
                else
                    {
                        return 0;
                    }
            }

        public function returnTerminalPush()
            {

                $push_request = '<?xml version="1.0" encoding="utf-8"?>
                                    <API3G>
                                        <Response>OK</Response>
                                    </API3G>';

                return $push_request;
            }

        public function email()
            {

                $request = '<?xml version="1.0" encoding="utf-8"?>
                        <API3G>
                          <CompanyToken>' . $this->company_token . '</CompanyToken>
                          <Request>emailToToken</Request>
                          <TransactionToken>' . $this->transaction_token . '</TransactionToken>
                        </API3G>';
                $xml     = simplexml_load_string(self::genericPost($request));
                if ($xml->Result == '000')
                    {
                        return 1;
                    }
                else
                    {
                        //Log::error($xml->ResultExplanation);
                        return 0;
                    }

            }


        public function xmltoobject($xml)
        {
            return (object)XmlToArray::convert($xml);
        }

    }

