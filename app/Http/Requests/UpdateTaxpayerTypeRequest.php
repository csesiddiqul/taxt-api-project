<?php

namespace App\Http\Requests;

use App\Traits\ApiErrorResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxpayerTypeRequest extends FormRequest
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
            'TaxpayerTypeID' => [
                'required',
                'string',
                'max:10',
                Rule::unique('TaxpayerType', 'TaxpayerTypeID')->ignore($this->route('tax_payer_type'), 'TaxpayerTypeID'),
            ],
            'TaxpayerType' => 'required|string|max:120',
        ];
    }
}
