<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RateResource extends JsonResource
    {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
        public function toArray(Request $request): array
            {

                return [
                    'id'               => $this->id,
                    'name'             => $this->name,
                    'category'         => $this->category,
                    'swahili_name'     => $this->swahili_name,
                    'cost'             => $this->cost,
                    'strike_price'     => $this->strike_price,
                    'currency'         => $this->currency,
                    'section'          => $this->sections,
                    'period'           => ( ($this->product->type=="epaper") && ($this->editions > 0) ) ? $this->editions : $this->period,
                    'editions'         => $this->editions,
                    'apple_product_id' => $this->apple_product_id,
                    'listorder'        => $this->listorder,
                    'best_value'       => $this->best_value,
                    'expiry_date'      => $this->expiry_date
                ];
            }
    }
