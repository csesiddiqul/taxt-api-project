<?php

namespace App\Http\Requests;

use App\Traits\ApiErrorResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankAccRequest extends FormRequest
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
            'BankNo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('BankAcc', 'BankNo')->ignore($this->route('bank_account'), 'BankNo'),
            ],
            'BankName' => 'required|string|max:120',
            'Branch' => 'required|string|max:120',
            'AccountsNo' => 'required|string|max:120',
        ];
    }
}
