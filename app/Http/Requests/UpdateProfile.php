<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 *
 */
class UpdateProfile extends FormRequest
    {
        public mixed $password;

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
                    'name'         => ['required'],
                    'email'        => ['required'],
                    'surname'      => ['nullable'],
                    'phone_number' => ['nullable'],
                ];
            }
    }
