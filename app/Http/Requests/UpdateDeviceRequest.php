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
            'id' => ['required','integer','exists:vehicle_data,id'],
            'balloon_volume' => ['required','numeric'],
            'car_number' => ['required','string','max:10','unique:vehicle_data,car_number'],
            'year' => ['integer'],
            'car_model' => ['string'],
            'owner_id' => [ 'integer','exists:users,id'],
            'texosmotr_valid_till' => ['date_format:Y-m-d'],
            'strxovka_valid_till' => ['date_format:Y-m-d'],
            'tonirovka_valid_till' => [ 'date_format:Y-m-d'],
            'doverenost_valid_till' => [ 'date_format:Y-m-d'],
        ];
    }
}
