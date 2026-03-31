<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProduct extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "product_name" =>["required"],
            "payment_prefix" => ["required","unique:products,identifier"],
            "payment_methods" => ["required"],
            "product_link" => ["required"],
            "payment_notification_link" => ["nullable"]
        ];
    }
}
