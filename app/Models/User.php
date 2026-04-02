<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Panel;

#[Fillable(['name', 'email', 'password', 'is_admin', 'limits'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'limits' => 'array',
        ];
    }
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

    public function maxUploadMb(): int
    {
        if (($this->is_admin ?? false) === true) {
            return 512; // safe admin cap to avoid accidents; adjust if you want
        }

        return (int) data_get($this->limits, 'max_upload_mb', 150);
    }
}
