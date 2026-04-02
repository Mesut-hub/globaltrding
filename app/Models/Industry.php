<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'cover_image_path',
        'blocks',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'excerpt' => 'array',
        'blocks' => 'array',
        'is_published' => 'boolean',
    ];
}