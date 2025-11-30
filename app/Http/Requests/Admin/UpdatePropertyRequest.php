<?php

namespace App\Http\Requests\Admin;

use App\Traits\HandleResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePropertyRequest extends FormRequest
{
    use HandleResponse;

    /**
     * Prepare the data for validation.
     * Convert JSON strings to arrays for features and tags
     */
    protected function prepareForValidation()
    {
        $data = [];

        // Handle features: convert JSON string to array if needed
        if ($this->has('features')) {
            $features = $this->input('features');
            if (is_string($features)) {
                $decoded = json_decode($features, true);
                $data['features'] = is_array($decoded) ? $decoded : [];
            }
        }

        // Handle tags: convert JSON string to array if needed
        if ($this->has('tags')) {
            $tags = $this->input('tags');
            if (is_string($tags)) {
                $decoded = json_decode($tags, true);
                $data['tags'] = is_array($decoded) ? $decoded : [];
            }
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function rules()
    {
        return [

            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',

            'property_type' => 'sometimes|required|string|in:house,villa,apartment,land,commercial,office',
            'type' => 'sometimes|required|string|in:sale,rent',
            'purpose' => 'sometimes|required|string|in:residential,commercial',

            'bedrooms' => 'sometimes|required|integer|min:1|max:20',
            'bathrooms' => 'sometimes|required|integer|min:1|max:15',
            'living_rooms' => 'sometimes|required|integer|min:0|max:10',
            'kitchens' => 'sometimes|required|integer|min:1|max:5',
            'balconies' => 'sometimes|required|integer|min:0|max:10',

            'area_total' => 'sometimes|required|string|max:100000',

            'floor' => 'nullable|integer|min:1',
            'total_floors' => 'nullable|integer|min:1',

            'furnishing' => 'sometimes|required|string|in:furnished,semi-furnished,unfurnished',
            'status' => 'sometimes|required|string|in:available,sold,rented',
            'is_featured' => 'sometimes|boolean',

            'features' => 'nullable|array',
            'features.*' => 'sometimes|string|max:255',

            'tags' => 'nullable|array',
            'tags.*' => 'sometimes|string|max:255',

            'price' => 'sometimes|required|string|min:0',
            'discount' => 'nullable|string|min:0|max:100',
            'discounted_price' => 'nullable|string|min:0',

            'city' => 'sometimes|required|string|max:255',
            'district' => 'sometimes|required|string|max:255',
            'street' => 'sometimes|required|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',

            'owner_id' => 'sometimes|required|exists:owners,id',
            'agency_id' => 'nullable|exists:agencies,id',

            'images' => 'nullable|array|max:10',
            'images.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Check if it's a URL
                    if (is_string($value)) {
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            return $fail('يجب أن تكون الصورة رابط URL صالح أو ملف مرفوع');
                        }
                        // Validate URL ends with image extension
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                        $extension = strtolower(pathinfo(parse_url($value, PHP_URL_PATH), PATHINFO_EXTENSION));
                        if (!in_array($extension, $imageExtensions)) {
                            return $fail('رابط الصورة يجب أن ينتهي بامتداد صورة صالح (jpeg, jpg, png, gif, webp, svg)');
                        }
                    }
                    // Check if it's an uploaded file
                    elseif ($value instanceof \Illuminate\Http\UploadedFile) {
                        if (strpos($value->getMimeType(), 'image/') !== 0) {
                            return $fail('الملف يجب أن يكون صورة فقط');
                        }
                        if ($value->getSize() > 2048 * 1024) { // 2MB in bytes
                            return $fail('يجب ألا يتجاوز حجم الصورة 2 ميجابايت');
                        }
                        $allowedMimes = ['jpeg', 'jpg', 'png', 'gif', 'webp', 'svg'];
                        if (!in_array($value->getClientOriginalExtension(), $allowedMimes)) {
                            return $fail('الصورة يجب أن تكون من الأنواع: jpeg, jpg, png, gif, webp, svg');
                        }
                    }
                    else {
                        return $fail('يجب أن تكون الصورة رابط URL أو ملف مرفوع');
                    }
                }
            ],

            "videos" => "nullable|array|max:5",
            "videos.*" => [
                "required",
                function ($attribute, $value, $fail) {
                    // Check if it's a URL
                    if (is_string($value)) {
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            return $fail('يجب أن يكون الفيديو رابط URL صالح أو ملف مرفوع');
                        }
                        // Validate URL ends with video extension
                        $videoExtensions = ['mp4', 'avi', 'wmv', 'flv', 'webm'];
                        $extension = strtolower(pathinfo(parse_url($value, PHP_URL_PATH), PATHINFO_EXTENSION));
                        if (!in_array($extension, $videoExtensions)) {
                            return $fail('رابط الفيديو يجب أن ينتهي بامتداد فيديو صالح (mp4, avi, wmv, flv, webm)');
                        }
                    }
                    // Check if it's an uploaded file
                    elseif ($value instanceof \Illuminate\Http\UploadedFile) {
                        $allowedExtensions = ['mp4', 'avi', 'wmv', 'flv', 'webm'];
                        if (!in_array($value->getClientOriginalExtension(), $allowedExtensions)) {
                            return $fail('الملف يجب أن يكون فيديو فقط');
                        }
                        if ($value->getSize() > 5120 * 1024) { // 5MB in bytes
                            return $fail('يجب ألا يتجاوز حجم الفيديو 5 ميجابايت');
                        }
                    }
                    else {
                        return $fail('يجب أن يكون الفيديو رابط URL أو ملف مرفوع');
                    }
                }
            ],

        ];
    }

    public function messages()
    {
        return [

            'title.required' => 'العنوان مطلوب.',
            'title.string' => 'العنوان يجب أن يكون نصًا.',
            'title.max' => 'العنوان يجب ألا يتجاوز 255 حرفًا.',

            'description.required' => 'الوصف مطلوب.',
            'description.string' => 'الوصف يجب أن يكون نصًا.',

            'property_type.required' => 'نوع العقار مطلوب.',
            'property_type.in' => 'نوع العقار غير صالح.',

            'type.required' => 'نوع العملية مطلوب.',
            'type.in' => 'نوع العملية يجب أن يكون بيع أو إيجار.',

            'purpose.required' => 'الغرض مطلوب.',
            'purpose.in' => 'الغرض يجب أن يكون سكني أو تجاري.',

            'bedrooms.required' => 'عدد غرف النوم مطلوب.',
            'bedrooms.integer' => 'عدد غرف النوم يجب أن يكون رقمًا صحيحًا.',
            'bedrooms.min' => 'يجب أن يحتوي العقار على غرفة نوم واحدة على الأقل.',
            'bedrooms.max' => 'عدد غرف النوم لا يمكن أن يتجاوز 20 غرفة.',

            'bathrooms.required' => 'عدد الحمامات مطلوب.',
            'bathrooms.integer' => 'عدد الحمامات يجب أن يكون رقمًا صحيحًا.',
            'bathrooms.min' => 'يجب أن يحتوي العقار على حمام واحد على الأقل.',
            'bathrooms.max' => 'عدد الحمامات لا يمكن أن يتجاوز 15 حمامًا.',

            'living_rooms.required' => 'عدد غرف المعيشة مطلوب.',
            'living_rooms.integer' => 'عدد غرف المعيشة يجب أن يكون رقمًا صحيحًا.',
            'living_rooms.min' => 'عدد غرف المعيشة لا يمكن أن يكون أقل من 0.',
            'living_rooms.max' => 'عدد غرف المعيشة لا يمكن أن يتجاوز 10 غرف.',

            'kitchens.required' => 'عدد المطابخ مطلوب.',
            'kitchens.integer' => 'عدد المطابخ يجب أن يكون رقمًا صحيحًا.',
            'kitchens.min' => 'يجب أن يحتوي العقار على مطبخ واحد على الأقل.',
            'kitchens.max' => 'عدد المطابخ لا يمكن أن يتجاوز 5 مطابخ.',

            'balconies.required' => 'عدد الشرفات مطلوب.',
            'balconies.integer' => 'عدد الشرفات يجب أن يكون رقمًا صحيحًا.',
            'balconies.min' => 'عدد الشرفات لا يمكن أن يكون أقل من 0.',
            'balconies.max' => 'عدد الشرفات لا يمكن أن يتجاوز 10 شرفات.',

            'area_total.required' => 'المساحة الإجمالية مطلوبة.',
            'area_total.max' => 'المساحة الإجمالية لا يمكن أن تتجاوز 100,000 متر مربع.',

            'floor.integer' => 'رقم الطابق يجب أن يكون رقمًا صحيحًا.',
            'floor.min' => 'رقم الطابق لا يمكن أن يكون أقل من 0 (الطابق الأرضي).',

            'total_floors.integer' => 'إجمالي الأدوار يجب أن يكون رقمًا.',
            'total_floors.min' => 'إجمالي الأدوار يجب أن يكون 1 أو أكثر.',

            'furnishing.required' => 'حالة الأثاث مطلوبة.',
            'furnishing.in' => 'حالة الأثاث غير صالحة.',

            'status.required' => 'حالة العقار مطلوبة.',
            'status.in' => 'حالة العقار غير صالحة.',

            'is_featured.boolean' => 'القيمة يجب أن تكون true أو false.',

            'features.array' => 'المميزات يجب أن تكون في شكل قائمة.',
            'features.*.string' => 'كل ميزة يجب أن تكون نصًا.',
            'features.*.max' => 'الميزة يجب ألا تتجاوز 255 حرف.',

            'tags.array' => 'العلامات يجب أن تكون في شكل قائمة.',
            'tags.*.string' => 'كل علامة يجب أن تكون نصًا.',
            'tags.*.max' => 'العلامة يجب ألا تتجاوز 255 حرف.',

            'price.required' => 'السعر مطلوب.',

            'discount.max' => 'الخصم يجب ألا يتجاوز 100%.',

            'city.required' => 'المدينة مطلوبة.',
            'city.string' => 'المدينة يجب أن تكون نصًا.',
            'city.max' => 'اسم المدينة يجب ألا يتجاوز 255 حرفًا.',

            'district.required' => 'الحي مطلوب.',
            'district.string' => 'الحي يجب أن يكون نصًا.',
            'district.max' => 'اسم الحي يجب ألا يتجاوز 255 حرفًا.',

            'street.required' => 'الشارع مطلوب.',
            'street.string' => 'الشارع يجب أن يكون نصًا.',
            'street.max' => 'اسم الشارع يجب ألا يتجاوز 255 حرفًا.',

            'landmark.string' => 'المعلم المميز يجب أن يكون نصًا.',
            'landmark.max' => 'المعلم المميز يجب ألا يتجاوز 255 حرفًا.',

            'latitude.numeric' => 'خط العرض يجب أن يكون رقمًا صحيحًا.',
            'longitude.numeric' => 'خط الطول يجب أن يكون رقمًا صحيحًا.',

            'owner_id.required' => 'معرف المالك مطلوب.',
            'owner_id.exists' => 'المالك المحدد غير موجود.',

            'agency_id.exists' => 'الوكالة المحددة غير موجودة.',

            'images.array' => 'الصور يجب أن تكون في شكل مصفوفة.',
            'images.max' => 'لا يمكن رفع أكثر من 10 صور.',

            'videos.array' => 'الفيديوهات يجب أن تكون في شكل مصفوفة.',
            'videos.max' => 'لا يمكن رفع أكثر من 5 فيديوهات.',

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
