<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return T_ATTRIBUTE;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('PUT')) {
            return [
                'password' => [
                    'required',
                    'string',
                    Password::min(8)->max(30)->mixedCase()->numbers(),
                    'confirmed'
                ]
            ];
        } else {
            return [];
        }
    }
}
