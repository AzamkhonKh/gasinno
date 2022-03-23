<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetRelayStateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return isset($this->device_token);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'device_id' => ['required','integer'],
            'device_token' => ['required','string'],
            'relay_state' => ['required','numeric','in:1,0'],
        ];
    }
}
