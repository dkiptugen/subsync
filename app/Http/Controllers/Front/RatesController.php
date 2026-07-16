<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;

class RatesController extends Controller
{
    public function rates($productId=Null)
        {
            $this->data['products'] = Product::with([
                'rates' => fn ($query) => $query->where('status', 1)->with('rate_type'),
            ])->whereStatus(1)
                                             ->when($productId!=NULL,function ($query)use($productId){
                                 $query->where('id',$productId);
        })
                ->get();
            return view('modules.front.rates',$this->data);
        }
}
