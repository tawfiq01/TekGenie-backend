<?php

namespace App\Rules;

use App\Support\BdPhone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a Bangladesh mobile number per VR-02. Pair with BdPhone::normalize()
 * before persisting — this rule only checks validity, it does not mutate input.
 */
class BdMobileNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! BdPhone::isValid($value)) {
            $fail('The :attribute must be a valid Bangladeshi mobile number.');
        }
    }
}
