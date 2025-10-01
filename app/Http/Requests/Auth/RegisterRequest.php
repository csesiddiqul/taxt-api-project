<?php
namespace App\Http\Requests\Auth;
use App\Traits\ApiErrorResponse;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => [
                'required',
                'regex:/^(\+8801[3-9]\d{8}|01[3-9]\d{8})$/',
                function ($attribute, $value, $fail) {
                    if (\App\Models\User::where('phone', operator: normalizePhone($value))->exists()) {
                        $fail('phone number is already registered. The user name field is required');
                    }
                },
            ],
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
            ],
            'password_confirmation' => 'required|same:password',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name cannot exceed 255 characters.',

            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'The phone number must be a valid Bangladeshi number.',

            'email.required' => 'The email field is required.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'The password field is required.',
            'password_confirmation.required' => 'The password confirmation field is required.',
            'password_confirmation.same' => 'The password confirmation does not match the password.',
        ];
    }
}
