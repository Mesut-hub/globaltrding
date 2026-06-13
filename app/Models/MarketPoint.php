<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketPoint extends Model
{
    protected $fillable = [
        'market_instrument_id',
        'date',
        'value',
    ];

    protected $casts = [
        'date' => 'date',
        'value' => 'decimal:6',
    ];

    public function instrument()
    {
        return $this->belongsTo(MarketInstrument::class, 'market_instrument_id');
    }
}