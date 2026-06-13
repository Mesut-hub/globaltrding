<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerActivityLog extends Model
{
    // ── Action constants ──────────────────────────────────────────────────────
    const ACTION_LOGIN            = 'login';
    const ACTION_LOGOUT           = 'logout';
    const ACTION_LOGIN_FAILED     = 'login_failed';
    const ACTION_BLOCKED          = 'blocked';
    const ACTION_UNBLOCKED        = 'unblocked';
    const ACTION_SUSPENDED        = 'suspended';
    const ACTION_UNSUSPENDED      = 'unsuspended';
    const ACTION_ACCESS_GRANTED   = 'access_granted';
    const ACTION_ACCESS_REVOKED   = 'access_revoked';
    const ACTION_FORCE_LOGOUT     = 'force_logout';
    const ACTION_PASSWORD_RESET   = 'password_reset_sent';
    const ACTION_STATUS_CHANGED   = 'status_changed';

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'context',
        'performed_by',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // ── Human-readable labels ─────────────────────────────────────────────────

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_LOGIN            => 'Login',
            self::ACTION_LOGOUT           => 'Logout',
            self::ACTION_LOGIN_FAILED     => 'Failed Login Attempt',
            self::ACTION_BLOCKED          => 'Account Blocked',
            self::ACTION_UNBLOCKED        => 'Account Unblocked',
            self::ACTION_SUSPENDED        => 'Account Suspended',
            self::ACTION_UNSUSPENDED      => 'Suspension Lifted',
            self::ACTION_ACCESS_GRANTED   => 'Product Access Granted',
            self::ACTION_ACCESS_REVOKED   => 'Product Access Revoked',
            self::ACTION_FORCE_LOGOUT     => 'Force Logged Out',
            self::ACTION_PASSWORD_RESET   => 'Password Reset Email Sent',
            self::ACTION_STATUS_CHANGED   => 'Status Changed',
            default                       => ucwords(str_replace('_', ' ', $this->action)),
        };
    }
}