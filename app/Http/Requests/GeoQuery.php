<?php

namespace App\Http\Requests;

use App\Models\VehicleData;
use Illuminate\Foundation\Http\FormRequest;

class GeoQuery extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $device = VehicleData::find($this->input('device_id'));

        return $device && $device->owner_id == auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'device_id' => ['required'],
            'mode' => ['integer'],
            'page' => ['integer'],
            'page_size' => ['integer', 'min:0', 'max:10'],
            'from' => ['date_format:Y-m-d H:i:s'],
            'to' => ['date_format:Y-m-d H:i:s'],
        ];
    }
}
