<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InquiryReply extends Model
{
    protected $fillable = [
        'inquiry_request_id',
        'to_email',
        'subject',
        'body',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function inquiry()
    {
        return $this->belongsTo(InquiryRequest::class, 'inquiry_request_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}