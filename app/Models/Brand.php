<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'is_published',
    ];

    protected $casts = [
        'name' => 'array',
        'is_published' => 'boolean',
    ];
}