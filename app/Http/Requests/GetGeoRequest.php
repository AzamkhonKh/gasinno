<?php

namespace App\Http\Requests;

use App\Models\VehicleData;
use App\Rules\checkDeviceToken;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class GetGeoRequest extends FormRequest
{
    /**
     * @var mixed
     */
    private $device_id;

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
            'lat' => ['required','numeric'],
            'long' => ['required','numeric'],
            'speed' => ['required','numeric'],
            'datetime' => ['required','numeric'],
            'fual_gas' => ['required','numeric'],
            'relay_state' => ['required','numeric','in:1,0'],
            'restored' => ['numeric','in:1,0'],
            'label' => 'string',
        ];
    }
}
