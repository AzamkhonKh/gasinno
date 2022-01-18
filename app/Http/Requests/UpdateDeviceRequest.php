<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'car_id' => ['required','integer','exists:vehicle_data,id'],
            'balloon_volume' => ['required','numeric'],
            'car_number' => ['required','string','unique:vehicle_data,car_number'],
            'car_model' => ['string'],
            'owner_id' => [ 'integer'],
        ];
    }
}
