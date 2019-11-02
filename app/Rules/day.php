<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class day implements Rule
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
        $days_accept = [1,2,3,4,5,6,7];
        $day = explode(".",$attribute);


        if(in_array((int)$day[1], $days_accept)){
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The day must be between 1 and 7';
    }
}
