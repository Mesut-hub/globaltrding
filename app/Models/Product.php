<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'brand_id',
        'slug',
        'name',
        'summary',
        'description',
        'seo',
        'is_published',
    ];

    protected $casts = [
        'name' => 'array',
        'summary' => 'array',
        'description' => 'array',
        'seo' => 'array',
        'is_published' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}