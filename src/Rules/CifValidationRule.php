<?php
namespace AntonioPrimera\Cif\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CifValidationRule implements ValidationRule
{
	public function validate(string $attribute, mixed $value, Closure $fail): void
	{
        if (!cif($value)->isValid())
            $fail(__('validation.cif'));
	}
}
