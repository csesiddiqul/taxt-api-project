<?php

namespace App\Http\Requests\Auth;

use App\Traits\ApiErrorResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    use ApiErrorResponse;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'regex:/^(\+8801[3-9]\d{8}|01[3-9]\d{8})$/',  // Validates Bangladeshi phone numbers
            ],
            'password' => 'required|string',  // Ensure password is a string and required
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'The phone number field is required.',
            'phone.regex' => 'The phone number must be a valid Bangladeshi phone number.',
            'phone.exists' => 'The phone number does not exist in our records.',
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
        ];
    }
}
