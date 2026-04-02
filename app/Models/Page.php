<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
        'seo',
        'is_published',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'seo' => 'array',
        'is_published' => 'boolean',
    ];
}