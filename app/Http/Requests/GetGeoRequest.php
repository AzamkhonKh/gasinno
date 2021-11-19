<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetGeoRequest extends FormRequest
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
            'lat' => ['required','numeric'],
            'long' => ['required','numeric'],
            'gas' => ['required','numeric'],
            'rele' => ['required','numeric','in:1,0'],
            'label' => 'string',
        ];
    }
}
