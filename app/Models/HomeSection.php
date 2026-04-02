<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'key',
        'is_active',
        'sort_order',
        'blocks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'blocks' => 'array',
    ];
}