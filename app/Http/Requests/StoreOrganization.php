<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganization extends FormRequest
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
                    'name'              =>  ['required','unique:organizations,name'],
                    'address'           =>  ['required'],
                    'phone_number'      =>  ['required'],
                    'admin_name'        =>  ['required'],
                    'admin_email'       =>  ['required'],
                    'kra_pin'           =>  ['required'],
                    'registration_no'   =>  ['required']
                ];
    }
}
