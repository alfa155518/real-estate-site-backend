<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSupport extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'priority',
        'subject',
        'message',
        'images',
        'user_id',
        'status',
    ];

    protected $casts = [
        'priority' => 'string',
        'status' => 'string',
        'images' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
