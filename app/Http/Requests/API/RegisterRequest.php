<?php

namespace App\Http\Requests\API;

use App\Models\Role;
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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'string',
            'type' => ['required','string', Rule::in(Role::pluck('name')->toArray())],
            'password' => 'string',
            'c_password' => [Rule::requiredIf(function () {
                return isset($this->type);
            }), 'same:password'],
        ];
    }
}
