<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'startdate' => ['nullable', 'date'],
            'enddate' => ['nullable', 'date', 'after_or_equal:startdate'],
            'product' => ['nullable', 'array'],
            'product.*' => ['integer', Rule::exists('products', 'id')],
            'ratetype' => ['nullable', 'array'],
            'ratetype.*' => ['integer', Rule::exists('rate_types', 'id')],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
