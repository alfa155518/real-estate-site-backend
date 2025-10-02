<?php

namespace App\Http\Requests;

use App\Traits\HandleResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateReviewRequest extends FormRequest
{
    use HandleResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'property_id' => 'nullable|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:500',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'التقييم مطلوب',
            'rating.integer' => 'التقييم يجب أن يكون رقمًا صحيحًا',
            'rating.min' => 'التقييم يجب أن يكون على الأقل 1',
            'rating.max' => 'التقييم يجب أن يكون على الأكثر 5',
            'comment.required' => 'التعليق مطلوب',
            'comment.string' => 'التعليق يجب أن يكون نصًا',
            'comment.min' => 'التعليق يجب أن يكون على الأقل 10 أحرف',
            'comment.max' => 'التعليق يجب ألا يزيد عن 500 حرف',
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