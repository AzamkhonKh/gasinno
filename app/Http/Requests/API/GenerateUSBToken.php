<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class GenerateUSBToken extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->checkRole('administrator');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => ['required', 'exist:users,id'],
            'usb_token' => ['string', 'max:255'],
            'usb_key_validated' => ['date_format:Y-m-d H:i:s'],
        ];
    }
}
