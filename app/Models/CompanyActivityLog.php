<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyActivityLog extends Model
{
    protected $fillable = ['customer_id', 'action', 'ip_address', 'user_agent', 'context', 'performed_by'];

    protected function casts(): array
    {
        return ['context' => 'array'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}