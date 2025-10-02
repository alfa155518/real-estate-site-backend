<?php

namespace App\Utils;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReviewValidator
{
    /**
     * Validate review creation data
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws ValidationException
     */
    public static function validateCreateReview($request): array
    {
        $rules = [
            'property_id' => 'nullable|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:500',
        ];

        $messages = [
            'rating.required' => 'التقييم مطلوب',
            'rating.integer' => 'التقييم يجب أن يكون رقمًا صحيحًا',
            'rating.min' => 'التقييم يجب أن يكون على الأقل 1',
            'rating.max' => 'التقييم يجب أن يكون على الأكثر 5',
            'comment.required' => 'التعليق مطلوب',
            'comment.string' => 'التعليق يجب أن يكون نصًا',
            'comment.min' => 'التعليق يجب أن يكون على الأقل 10 أحرف',
            'comment.max' => 'التعليق يجب ألا يزيد عن 500 حرف',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
