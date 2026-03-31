<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class storeSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required','unique:sites,site_name'],
            'site_url' => ['required','unique:sites,site_url'],
            'region_id' => ['required','integer']
        ];
    }
}
