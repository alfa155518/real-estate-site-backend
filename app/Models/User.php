<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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
        'address',
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

    public static function signupRules()
    {
        return [
            'name' => 'required|string|max:30',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',
            'phone' => 'required|string|size:11|regex:/^01[0-9]{9}$/|unique:users',
            'address' => 'nullable|string|max:100',
        ];
    }

    public static function signupMessages()
    {
        return [
            'name.required' => 'حقل الاسم مطلوب',
            'name.max' => 'يجب ألا يزيد الاسم عن 30 حرفًا',
            'email.required' => 'حقل البريد الإلكتروني مطلوب',
            'email.email' => 'يجب إدخال بريد إلكتروني صحيح',
            'email.max' => 'يجب ألا يزيد البريد الإلكتروني عن 50 حرفًا',
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقًا',
            'password.required' => 'حقل كلمة المرور مطلوب',
            'password.min' => 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل',
            'confirm_password.required' => 'حقل تأكيد كلمة المرور مطلوب',
            'confirm_password.min' => 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل',
            'confirm_password.same' => 'تأكيد كلمة المرور غير متطابق',
            'phone.required' => 'حقل رقم الهاتف مطلوب',
            'phone.size' => 'يجب أن يتكون رقم الهاتف من 11 رقمًا',
            'phone.regex' => 'يجب أن يبدأ رقم الهاتف بـ 01 ويتبعه 9 أرقام',
            'phone.unique' => 'هذا الرقم مسجل مسبقًا',
            'address.max' => 'يجب ألا يزيد العنوان عن 100 حرف'
        ];
    }

    public static function loginRules()
    {
        return [
            'email' => 'required|string|email|max:50',
            'password' => 'required|string|min:8',
        ];
    }

    public static function loginMessages()
    {
        return [
            'email.required' => 'حقل البريد الإلكتروني مطلوب',
            'email.email' => 'يجب إدخال بريد إلكتروني صحيح',
            'email.max' => 'يجب ألا يزيد البريد الإلكتروني عن 50 حرفًا',
            'password.required' => 'حقل كلمة المرور مطلوب',
            'password.min' => 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل',
        ];
    }

    public static function forgotPasswordRules()
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public static function forgotPasswordMessages()
    {
        return [
            'email.required' => 'حقل البريد الإلكتروني مطلوب',
            'email.email' => 'يجب إدخال بريد إلكتروني صحيح',
            'email.exists' => 'البريد الإلكتروني غير مسجل مسبقًا',
        ];
    }

    public static function resetPasswordRules()
    {
        return [
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ];
    }

    public static function resetPasswordMessages()
    {
        return [
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل',
            'confirm_password.required' => 'تأكيد كلمة المرور مطلوب',
            'confirm_password.same' => 'تأكيد كلمة المرور غير متطابق',
        ];
    }

}
