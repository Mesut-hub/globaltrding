<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookieConsentLog extends Model
{
    protected $fillable = [
        'session_id', 'ip_hash', 'consents',
        'locale', 'user_agent_hash', 'consented_at',
        'consent_version',
    ];

    protected $casts = [
        'consents'     => 'array',
        'consented_at' => 'datetime',
    ];
}