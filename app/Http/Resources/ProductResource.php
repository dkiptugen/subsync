<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "site" => $this->site_name,
            "url" => $this->site_url,
            "region" => $this->region->name,
            "currency" => $this->region->currency,
            'currency_code' => $this->region->currency_code,
            "product" => $this->products->map(function($x){
                $data['identifier']= $x->identifier;
                $data['name']= $x->product_name;
                $data['type'] = $x->type;
                $data['premium'] = (bool)$x->is_premium;
                $data['bundle'] = (bool)$x->is_bundled;
                $data['payment_methods']= $x->payment_methods->map->only(['identifier','name','type','icon']);
                $data['rate']= $x->rates->map->only(['id','apple_product_id','name','swahili_name','cost','currency','period','editions','status']);
                $data['includes'] = $x->children->map->only(['identifier','product_name','type']);
                $data['description'] = $x->description_points;
                $data['article_cost'] = $x->article_cost;
                return $data;
            }),
        ];
    }
}
