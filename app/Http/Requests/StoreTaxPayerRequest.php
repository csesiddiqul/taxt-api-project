<?php

namespace App\Http\Requests;

use App\Traits\ApiErrorResponse;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaxPayerRequest extends FormRequest
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
            'HoldingNo'      => 'required|string|max:50',
            'ClientNo'       => 'required|string|max:50|unique:Client_Information,ClientNo',
            'StreetID'       => 'required|string|max:20',
            'OwnersName'     => 'required|string|max:150',
            'FHusName'       => 'required|string|max:150',
            'BillingAddress' => 'required|string|max:150',
            'PropTypeID'     => 'required|integer',
            'PropUseID'      => 'required|integer',
            'TaxpayerTypeID' => 'required|integer',
            'OriginalValue'  => 'nullable|numeric',
            'CurrentValue'   => 'required|numeric',
            'Arrear'   => 'required|numeric',
            'Active'         => 'nullable',
            'BankNo'         => 'required|integer',
            'HoldingTax'     => 'nullable',
            'WaterTax'       => 'nullable',
            'LightingTax'    => 'nullable',
            'ConservancyTax' => 'nullable',
            'ArrStYear'      => 'required|numeric',
            'ArrStYear1'     => 'required|numeric',
            'ArrStPeriod'    => 'required',
        ];
    }
}
