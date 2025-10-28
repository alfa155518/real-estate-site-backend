<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'logo',
        'location',
        'phone',
        'email',
        'opening_hours',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
    ];
}
