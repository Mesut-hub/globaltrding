<?php

namespace App\Support;

use App\Models\User;

class FilamentPermissions
{
    public static function allowed(?User $user, string $key): bool
    {
        if (! $user) {
            return false;
        }

        // Safety: never allow if key wasn't set on the Resource
        $key = trim($key);
        if ($key === '') {
            return false;
        }

        // Admins bypass
        if ($user->is_admin ?? false) {
            return true;
        }

        // Non-admin must be editor-enabled
        if (! (bool) data_get($user->limits, 'can_publish', false)) {
            return false;
        }

        return (bool) data_get($user->limits, "permissions.$key", false);
    }
}