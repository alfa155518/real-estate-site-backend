<?php

namespace App\Http\Requests\Admin;

use App\Traits\HandleResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateSettingsRequest extends FormRequest
{
    use HandleResponse;
    public function rules(Request $request): array
    {
        return [
            'logo' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        if (!in_array($value->getClientOriginalExtension(), ['jpeg', 'jpg', 'png', 'gif', 'webp', 'svg'])) {
                            $fail('الملف المرفوع يجب أن يكون صورة من الأنواع المسموحة: jpeg, jpg, png, gif, webp, svg');
                        }
                        if (strpos($value->getMimeType(), 'image/') !== 0) {
                            $fail('الملف المرفوع يجب أن يكون صورة فقط.');
                        }
                        if ($value->getSize() > 2048 * 1024) { // 2MB
                            $fail('يجب أن لا يتجاوز حجم الصورة 2 ميجابايت');
                        }
                    } elseif (is_string($value)) {

                    }
                },
            ],
            'location' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|string|max:50',
            'opening_hours' => 'required|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
        ];
    }



    public function messages(): array
    {
        return [
            "logo.image" => "الملف المرفوع يجب أن يكون صورة",
            "logo.mimes" => "يجب أن يكون نوع الملف jpeg, png, jpg, gif, أو webp",
            "logo.max" => "يجب أن لا يتجاوز حجم الصورة 2 ميجابايت",
            "location.required" => "يجب إدخال الموقع",
            "phone.required" => "يجب إدخال رقم الهاتف",
            "email.required" => "يجب إدخال البريد الإلكتروني",
            "email.email" => "يجب إدخال بريد إلكتروني صحيح",
            "opening_hours.required" => "يجب إدخال ساعات العمل",
            "phone.max" => "رقم الهاتف يجب ألا يتجاوز 20 حرفاً"
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