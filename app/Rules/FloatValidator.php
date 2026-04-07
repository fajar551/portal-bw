<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FloatValidator implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        // return !(bool) preg_match("/^[\\d]+(\\.[\\d]{1,2})?\$/i", $value);
        return (bool) preg_match("/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ":Attribute must be in decimal format: ### or ###.##";
    }
}
