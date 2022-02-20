<?php

namespace App\Http\Requests\API\Device;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\VehicleData;

class sendUserDevicessTurnAction extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $device = VehicleData::find($this->input('device_id'));

        return ($device && $device->owner_id == auth()->id()) || auth()->user()->checkRole('administrator');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'owner_id' => ['exists:users,id'],
            'action' => ['required','string','in:on,off'],
        ];
    }
}
