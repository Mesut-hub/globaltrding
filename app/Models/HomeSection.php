<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'key',
        'title',
        'blocks',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'title'     => 'array',
            'blocks'    => 'array',
            'sort_order'      => 'integer',
            'is_active' => 'boolean',
        ];
    }
}