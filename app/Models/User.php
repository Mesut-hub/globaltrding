<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

#[Fillable([
    'name',
    'email',
    'password',
    'is_admin',
    'limits',
    'has_product_access',
    // Account lifecycle
    'status',
    'blocked_at',
    'blocked_reason',
    'suspended_until',
    'suspended_reason',
    // Login tracking
    'last_login_at',
    'last_login_ip',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // ── Status constants ──────────────────────────────────────────────────────
    const STATUS_ACTIVE    = 'active';
    const STATUS_BLOCKED   = 'blocked';
    const STATUS_SUSPENDED = 'suspended';

    public static function allStatuses(): array
    {
        return [
            self::STATUS_ACTIVE    => 'Active',
            self::STATUS_BLOCKED   => 'Blocked',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    // ── Casts ─────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
            'limits'            => 'array',
            'has_product_access'=> 'boolean',
            'blocked_at'        => 'datetime',
            'suspended_until'   => 'datetime',
            'last_login_at'     => 'datetime',
        ];
    }

    // ── Filament Panel Access ─────────────────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return (bool) ($this->is_admin ?? false);
        }

        if ($panel->getId() === 'editor') {
            return (bool) ($this->is_admin ?? false)
                || (bool) data_get($this->limits, 'can_publish', false);
        }

        return false;
    }

    // ── Status helpers ────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_ACTIVE;
    }

    public function isBlocked(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_BLOCKED;
    }

    public function isSuspended(): bool
    {
        if (($this->status ?? self::STATUS_ACTIVE) !== self::STATUS_SUSPENDED) {
            return false;
        }

        // Auto-expire: if suspension end time has passed, treat as active
        if ($this->suspended_until && $this->suspended_until->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * The definitive access gate for product section.
     * All three conditions must be met.
     */
    public function canAccessProducts(): bool
    {
        return (bool) $this->has_product_access
            && $this->isActive()
            && ! $this->isSuspended();
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isBlocked()) {
            return 'Blocked';
        }

        if ($this->isSuspended()) {
            $until = $this->suspended_until
                ? ' until ' . $this->suspended_until->format('d M Y H:i')
                : '';
            return 'Suspended' . $until;
        }

        return 'Active';
    }

    public function getStatusColorAttribute(): string
    {
        return match (true) {
            $this->isBlocked()   => 'danger',
            $this->isSuspended() => 'warning',
            default              => 'success',
        };
    }

    // ── Limits helper (unchanged) ─────────────────────────────────────────────

    public function maxUploadMb(): int
    {
        if (($this->is_admin ?? false) === true) {
            return 512;
        }

        return (int) data_get($this->limits, 'max_upload_mb', 150);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function activityLogs(): HasMany
    {
        return $this->hasMany(CustomerActivityLog::class)
            ->orderByDesc('created_at');
    }
}