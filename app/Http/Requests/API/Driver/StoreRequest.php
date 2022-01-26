<?php

namespace App\Http\Requests\API\Driver;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'avatar' => ['image','mimes:jpeg,jpg,png,gif','max:10000'],
            'name' => ['required','string','max:255'],
            'surname' => ['required','string','max:255'],
            'age' => ['required','integer','max:100'],
            'phone' => ['required', 'regex:/\([0-9]{2}\)[0-9]{3}-[0-9]{2}-[0-9]{2}/'],
            'licenseData' => ['required',function ($attribute, $value, $fail) {
                if (array_key_exists('type', $value))
                {

                    if (!is_array($value['type'])) {
                        $fail('The '.$attribute.'\' s type property is not array.');
                    }
                }
                else
                {
                    $fail('The '.$attribute.'\' s type property not found.');
                }

            }],
        ];
    }
}
