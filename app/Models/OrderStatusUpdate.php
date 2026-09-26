<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusUpdate extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['order_id', 'stage_key', 'stage_label', 'stage_date', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['stage_date' => 'date', 'created_at' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}