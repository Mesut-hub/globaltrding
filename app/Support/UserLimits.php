<?php

namespace App\Support;

use App\Models\User;

class UserLimits
{
    public static function bypass(User $user): bool
    {
        return (bool) ($user->is_admin ?? false);
    }

    public static function max(User $user, string $key, int $default): int
    {
        if (self::bypass($user)) {
            return PHP_INT_MAX;
        }

        return (int) data_get($user->limits, $key, $default);
    }

    public static function canCreate(User $user, string $resourceKey, int $currentCount, int $defaultMax): bool
    {
        if (self::bypass($user)) {
            return true;
        }

        $max = self::max($user, "max_{$resourceKey}", $defaultMax);

        return $max === 0 ? false : $currentCount < $max;
    }
}