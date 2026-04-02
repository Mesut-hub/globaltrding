<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminReset extends Command
{
    protected $signature = 'admin:reset
        {email : Admin email}
        {--password= : Optional password (if omitted you will be prompted)}
        {--name=Administrator : Optional name}';

    protected $description = 'Create or reset an administrator user for Filament access';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $name = (string) $this->option('name');

        $password = (string) ($this->option('password') ?: '');
        if ($password === '') {
            $password = (string) $this->secret('Enter new password');
        }

        if (mb_strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }

        /** @var \App\Models\User $user */
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->name = $user->name ?: $name;
        $user->password = Hash::make($password);

        if (Schema::hasColumn('users', 'is_admin')) {
            $user->is_admin = true;
        }

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $user->email_verified_at = now();
        }

        $user->save();

        $this->info("Admin user ready: {$user->email}");
        return self::SUCCESS;
    }
}