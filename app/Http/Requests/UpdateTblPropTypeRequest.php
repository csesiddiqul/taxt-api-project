<?php

namespace App\Http\Requests;

use App\Traits\ApiErrorResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTblPropTypeRequest extends FormRequest
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
            'PropTypeID' => [
                'required',
                'string',
                'max:10',
                Rule::unique('Tbl_PropType', 'PropTypeID')->ignore($this->route('tbl_prop_type'), 'PropTypeID'),
            ],
            'PropertyType' => 'required|string|max:120',
        ];
    }
}
