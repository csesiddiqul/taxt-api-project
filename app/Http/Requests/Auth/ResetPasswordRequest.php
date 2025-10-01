<?php

namespace App\Http\Requests\Auth;

use App\Rules\Phone;
use App\Traits\ApiErrorResponse;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'token' => 'required|digits:6|integer',
            'phone' => [
                'required',
                'regex:/^(\+8801[3-9]\d{8}|01[3-9]\d{8})$/',
            ],
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'The phone number must be a valid Bangladeshi mobile number in the format +8801XXXXXXXXX or 01XXXXXXXXX.',
            'password.required' => 'The password field is required.',
            'password_confirmation.required' => 'The confirmed password field is required.',
            'password_confirmation.same' => 'The confirmed password and password must match.',
            'token.required' => 'Verification Code is required.',
            'token.digits' => 'Verification Code must be exactly 6 digits.',
            'token.integer' => 'Verification Code must be a valid integer.',
        ];
    }
}
