<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateSiteRequest extends FormRequest
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
            'site_name' => ['required'],
            'site_url' => ['required'],
            'region_id' => ['required','integer']
        ];
    }
}
