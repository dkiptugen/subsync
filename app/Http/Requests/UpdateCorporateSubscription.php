<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class UpdateCorporateSubscription extends FormRequest
        {
        /**
         * Determine if the user is authorized to make this request.
         */
            public function authorize()
            : bool
                {

                    return true;
                }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
         */
            public function rules()
            : array
                {

                    return [
                        'organization' => ['required'],
                        'product'      => ['required'],
                        'startdate'    => ['required'],
                        'users'        => ['required'],
                        'amount'       => ['nullable'],
                        'receipt'      => ['nullable'],
                        'channel'      => ['nullable'],
                    ];
                }
        }
