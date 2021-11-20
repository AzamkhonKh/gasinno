<?php

namespace App\Rules;

use App\Models\VehicleData;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class checkDeviceToken implements Rule
{
    private $model;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $device_id)
    {
        $this->model = VehicleData::find($device_id);
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
        if (empty($this->model) || !isset($this->model->token)) return false;

        return Hash::check($value,$this->model->token);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute token does not match.';
    }
}
