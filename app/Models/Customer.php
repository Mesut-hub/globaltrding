<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guard = 'customer';

    const STATUS_DRAFT     = 'draft';
    const STATUS_ACTIVE    = 'active';
    const STATUS_BLOCKED   = 'blocked';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'customer_code', 'company_name', 'full_commercial_name', 'registration_number',
        'phone', 'website', 'email', 'username', 'password', 'must_change_password',
        'status', 'registration_sent_at', 'blocked_at', 'blocked_reason',
        'suspended_until', 'suspended_reason', 'notes', 'created_by',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'registration_sent_at' => 'datetime',
            'blocked_at' => 'datetime',
            'suspended_until' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(CompanyActivityLog::class);
    }

    public function notificationContacts()
    {
        return $this->contacts()->where('receives_notifications', true)->whereNotNull('email');
    }

    public function isDraft(): bool { return $this->status === self::STATUS_DRAFT; }
    public function isActive(): bool { return $this->status === self::STATUS_ACTIVE; }
    public function isBlocked(): bool { return $this->status === self::STATUS_BLOCKED; }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED
            && (! $this->suspended_until || $this->suspended_until->isFuture());
    }
}