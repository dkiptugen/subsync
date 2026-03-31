<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class UpdateRate extends FormRequest
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
                        "rate_type_id" => ["required"],
                        "cost"         => ["required", "numeric"],
                        "currency"     => ["required"],
                        "product_id"   => ["required", "integer"],
                        "description"  => ["nullable"]
                    ];
                }
        }
