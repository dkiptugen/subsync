<?php

    namespace App\Http\Resources;

    use App\Models\Transaction;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Carbon;

    class SubscriptionResource extends JsonResource
        {


        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
            public function toArray(Request $request)
            : array
                {

                    try
                        {
                            if (isset($this->subdate))
                                {
                                    if (Carbon::parse($this->subdate)->between(Carbon::parse($this->subscription_date)->startOfDay(), Carbon::parse($this->expiry_date)))
                                        {
                                            $status = (bool)$this->status;
                                        }
                                    else
                                        {
                                            $status = false;
                                        }
                                }
                            else
                                {
                                    $status = (bool)$this->status;
                                }

                            $amount = null;

                            $end_date = Carbon::parse($this->expiry_date);
                            $check_date = Carbon::now();
                            if(!is_null($request->subscription_date))
                                $check_date = Carbon::parse($request->subscription_date);

                            if(@$this->product->type=='epaper')
                            {
                                $end_date = Carbon::parse($end_date)->endOfDay();
                            }

                            $transaction = Transaction::where('subscription_id',$this->id)->first();
                            if($transaction)
                                $amount = @$transaction->amount_paid ?? 0 ;

                            return [
                                'identifier'            => $this->identifier,
                                'product'               => $this->product->product_name,
                                'productIdentifier'     => $this->product->identifier,
                                'type'                  => $this->rate->name,
                                'period'                => $this->rate->period,
                                'subscriptionDate'      => $this->subscription_date,
                                'expiryDate'            => @$end_date->toDateTimeString() ?? $this->expiry_date,
                                'status'                => $status,
                                'recurrent'             => (bool)$this->recurring,
                                'subscriptionStatus'    => ($this->recurring == 0) ? 'N/A' : (is_null($this->unsubscription_date) ? true : false),

                                'SubscriptionActivated' => $status == 1 && (Carbon::parse($check_date)->lte($end_date) || !is_null($this->article_id)),
                                'amount'  => @$amount ?? 0,
                                'currency' => $transaction->currency,
                                'article_id'=> $this->article_id,
                                'category' => $this->rate->category,
                                'meta'  => [
                                    "id" => $this->id,
                                    'created_at' => $this->created_at,
                                    "user" =>["id"=> @auth()->user()->id ,'email'=> @auth()->user()->email]
                                ]
                            ];
                        }
                    catch (Exception $e)
                        {
                            return ['error' => $e->getMessage()];
                        }

                }
        }
