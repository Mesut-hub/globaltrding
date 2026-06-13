<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'first_name','last_name','email','occupation','mobile_phone','primary_product_interest','preferred_language','accepted_terms',
        'company','existing_customer','location','city','street_and_number','zip_code','industries_operate','message',
        'status','reviewed_at','reviewed_by','ip','user_agent',
    ];

    protected $casts = [
        'accepted_terms' => 'boolean',
        'existing_customer' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}