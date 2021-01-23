<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MoreThanValue implements Rule
{
    protected $minimum;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($minimum)
    {
        $this->minimum = $minimum;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $value > $this->minimum;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be more then 0.';
    }
}
