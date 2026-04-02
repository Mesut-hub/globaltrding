<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketInstrument extends Model
{
    protected $fillable = [
        'slug',
        'evds_series',
        'unit',
        'sort_order',
        'is_active',
        'name',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
    ];

    public function points()
    {
        return $this->hasMany(MarketPoint::class);
    }
}