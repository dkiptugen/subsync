<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
    {
    /**
     * Run the validation rule.
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     */
        public function validate($attribute, $value, $parameters, $validator): void
            {
                if (!$this->passes($attribute, $value))
                    {
                        $validator->errors()->add($attribute, $this->message());
                    }
            }

        public function passes($attribute, $value)
            {
                // Define your strong password criteria here.
                // Example: At least 8 characters, at least one uppercase letter, one lowercase letter, one digit, and one special character.
                return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $value);
            }

        public function message()
            {
                return 'The :attribute must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one digit, and one special character.';
            }
    }
