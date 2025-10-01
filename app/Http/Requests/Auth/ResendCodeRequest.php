<?php

namespace App\Http\Requests\Auth;

use App\Rules\Phone;
use App\Traits\ApiErrorResponse;
use Illuminate\Foundation\Http\FormRequest;

class ResendCodeRequest extends FormRequest
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
    public function rules()
    {
        return [
            'phone' => [
                'required',
                'regex:/^(\+8801[3-9]\d{8}|01[3-9]\d{8})$/',
                'exists:users,phone'
            ],
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => 'Phone number is required.',
            'phone.regex' => 'The phone number must be a valid Bangladeshi mobile number in the format +8801XXXXXXXXX or 01XXXXXXXXX.',
        ];
    }
}
