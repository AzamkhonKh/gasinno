<?php

namespace App\Http\Requests\API;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'phone' => ['required', 'regex:/\([0-9]{2}\)[0-9]{3}-[0-9]{2}-[0-9]{2}/'],
            'firstname' => ['string'],
            'lastname' => ['string'],
            'password' => ['required', 'string'],
            'email' => ['email'],
            'c_password' => ['required', 'same:password'],
        ];
    }
}
