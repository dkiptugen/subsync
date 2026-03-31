<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2bSubscriptionResource extends JsonResource
    {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
        public function toArray(Request $request): array
            {
                $check_date = \Illuminate\Support\Carbon::now();
                if(!is_null($request->subscription_date))
                    $check_date = Carbon::parse($request->subscription_date);

                return [
                    'identifier'            => 'corporate',
                    'product'               => $this->product->product_name,
                    'productIdentifier'     => $this->product->identifier,
                    'type'                  => $this->subscription_type,
                    'period'                => Carbon::parse($this->expiry_date)->diffInDays($this->start_date),
                    'subscriptionDate'      => $this->start_date,
                    'expiryDate'            => $this->expiry_date,
                    'status'                => (bool)$this->status,
                    'recurrent'             => (bool)$this->recurring,
                    'subscriptionStatus'    => 'N/A',
                    'SubscriptionActivated' => $check_date->lte(Carbon::parse($this->expiry_date)),
                    'amount' => $this->amount ?? 0,
                    'category' => 'normal',
                    'meta'  => [
                        "id" => $this->id,
                        'created_at' => $this->created_at,
                        "user" =>["id"=> @auth()->user()->id ,'email'=>@auth()->user()->email]
                    ]
                ];
            }
    }
