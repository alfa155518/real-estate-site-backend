<?php

namespace App\Http\Requests;

use App\Traits\HandleResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateUsersSupport extends FormRequest
{
    use HandleResponse;



    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(Request $request): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'priority' => 'nullable|in:low,medium,high',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'images' => 'nullable',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'nullable|in:pending,in_progress,resolved'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'حقل الاسم مطلوب.',
            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email' => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالح.',
            'phone.required' => 'حقل الهاتف مطلوب.',
            'priority.in' => 'يجب أن تكون الأولوية واحدة من: منخفضة، متوسطة، عالية، عاجلة.',
            'subject.required' => 'حقل الموضوع مطلوب.',
            'subject.max' => 'يجب ألا يزيد طول الموضوع عن ٢٥٥ حرفاً.',
            'message.required' => 'حقل الرسالة مطلوب.',
            'images.*.image' => 'يجب أن يكون كل ملف صورة.',
            'images.*.mimes' => 'يجب أن يكون كل ملف من نوع: jpeg، png، jpg، gif، webp.',
            'images.*.max' => 'يجب ألا يزيد حجم كل صورة عن ٢ ميجابايت.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->error($validator->errors()->first(), 422)
        );
    }

}