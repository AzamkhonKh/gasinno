<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class QRAssignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => ['integer','exists:users,id'],
            'qr_token' => ['required','string','exists:vehicle_data,qr_text']
        ];
    }
}
