<?php

namespace App\Http\Requests;

use App\Traits\HandleResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePropertiesFiltration extends FormRequest
{
    use HandleResponse;
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(Request $request): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'is_featured' => 'nullable|string|in:true,false',
            'type' => 'nullable|string|in:sale,rent,all',
            'location' => 'nullable|string|max:255',
            'minPrice' => 'nullable|numeric|min:0',
            'maxPrice' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:available',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'search.required' => 'يجب أن يكون البحث عن اسم عقار',
            'minPrice.required' => 'يجب أن يكون الحد الأدنى للسعر رقماً',
            'maxPrice.required' => 'يجب أن يكون الحد الأقصى للسعر رقماً',
            'bedrooms.required' => 'يجب أن يكون عدد غرف النوم رقماً',
            'bathrooms.required' => 'يجب أن يكون عدد الحمامات رقماً',
            'minPrice.numeric' => 'يجب أن يكون الحد الأدنى للسعر رقماً',
            'maxPrice.numeric' => 'يجب أن يكون الحد الأقصى للسعر رقماً',
            'bedrooms.numeric' => 'يجب أن يكون عدد غرف النوم رقماً',
            'bathrooms.numeric' => 'يجب أن يكون عدد الحمامات رقماً',
            'minPrice.min' => 'يجب أن لا يقل الحد الأدنى للسعر عن 0',
            'maxPrice.min' => 'يجب أن لا يقل الحد الأقصى للسعر عن 0',
            'bedrooms.min' => 'يجب أن لا يقل عدد غرف النوم عن 0',
            'bathrooms.min' => 'يجب أن لا يقل عدد الحمامات عن 0',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->error($validator->errors()->first(), 422)
        );
    }
}