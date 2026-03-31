<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rules\Password;

    class AddCorporateUserRequest extends FormRequest
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
                        'name'                  => ['required'],
                        'email'                 => ['required'],
                        'password'              => ['required_with:password_confirmation', 'same:password_confirmation', Password::default()],
                        'password_confirmation' => ['required', 'min:6']
                    ];
                }
        }
