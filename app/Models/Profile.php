<?php

namespace App\Models;

use App\Traits\HandleResponse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
class Profile extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HandleResponse;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'confirm_password',
        'phone',
        'role',
        'google_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'confirm_password',
        'role',
        'remember_token',
        'email_verified_at',
        'google_id',
        "created_at",
        "updated_at"
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function updateProfileInfoValidation($request, $user)
    {
        $rules = [
            'name' => 'required|string|max:30',
            'email' => 'required|string|email|max:50|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|size:11|regex:/^01[0-9]{9}$/|unique:users,phone,' . $user->id,
            'address' => 'nullable|string|max:100',
        ];

        $messages = [
            'name.required' => 'حقل الاسم مطلوب',
            'name.max' => 'يجب ألا يزيد الاسم عن 30 حرفًا',
            'email.required' => 'حقل البريد الإلكتروني مطلوب',
            'email.email' => 'يجب إدخال بريد إلكتروني صحيح',
            'email.max' => 'يجب ألا يزيد البريد الإلكتروني عن 50 حرفًا',
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقًا',
            'phone.size' => 'يجب أن يتكون رقم الهاتف من 11 رقمًا',
            'phone.regex' => 'يجب أن يبدأ رقم الهاتف بـ 01 ويتبعه 9 أرقام',
            'phone.unique' => 'هذا الرقم مسجل مسبقًا',
            'address.max' => 'يجب ألا يزيد العنوان عن 100 حرف'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function updatePasswordValidation($request)
    {
        $rules = [
            'current_password' => 'required|string|min:8',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',
        ];

        $messages = [
            'current_password.required' => 'حقل كلمة المرور الحالية مطلوب',
            'current_password.min' => 'يجب ألا يقل كلمة المرور الحالية عن 8 حرفًا',
            'password.required' => 'حقل كلمة المرور مطلوب',
            'password.min' => 'يجب ألا يقل كلمة المرور عن 8 حرفًا',
            'confirm_password.required' => 'حقل تأكيد كلمة المرور مطلوب',
            'confirm_password.min' => 'يجب ألا يقل تأكيد كلمة المرور عن 8 حرفًا',
            'confirm_password.same' => 'كلمة المرور وتأكيد كلمة المرور يجب أن تكون متطابقة',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }




}
