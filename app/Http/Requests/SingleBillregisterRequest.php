<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SingleBillregisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => 'required|integer',
            'year1' => 'required|integer',
            'period' => 'required|string',
            'issue_date' => 'nullable|date',
            'last_date' => 'nullable|date',
            'holdingno' => 'required|string',
            'clientno' => 'required|string',
        ];
    }
}
