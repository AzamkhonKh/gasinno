<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->checkRole('administrator');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'car_number' => ['string','unique:vehicle_data,car_number'],
            'owner_id' => [ 'integer','exists:users,id'],
            
            'balloon_volume' => ['required','numeric'],
            'year' => ['integer'],
            'car_model' => ['string'],
            'texosmotr_valid_till' => ['date_format:Y-m-d'],
            'strxovka_valid_till' => ['date_format:Y-m-d'],
            'tonirovka_valid_till' => [ 'date_format:Y-m-d'],
            'doverenost_valid_till' => [ 'date_format:Y-m-d'],
        ];
    }
}
