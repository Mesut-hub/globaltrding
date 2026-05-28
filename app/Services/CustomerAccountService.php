<?php

namespace App\Services;

use App\Models\CustomerActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ProductAccessApprovedMail;

class CustomerAccountService
{
    // ── Block / Unblock ───────────────────────────────────────────────────────

    public function block(User $customer, string $reason, ?User $performedBy = null): void
    {
        $oldStatus = $customer->status;

        $customer->forceFill([
            'status'         => User::STATUS_BLOCKED,
            'blocked_at'     => now(),
            'blocked_reason' => $reason,
        ])->save();

        $this->forceLogout($customer);

        $this->logActivity($customer, CustomerActivityLog::ACTION_BLOCKED, [
            'reason'     => $reason,
            'old_status' => $oldStatus,
        ], $performedBy);

        Log::info('Customer blocked', [
            'user_id'      => $customer->id,
            'email'        => $customer->email,
            'reason'       => $reason,
            'performed_by' => $performedBy?->id,
        ]);
    }

    public function unblock(User $customer, ?User $performedBy = null): void
    {
        $customer->forceFill([
            'status'         => User::STATUS_ACTIVE,
            'blocked_at'     => null,
            'blocked_reason' => null,
        ])->save();

        $this->logActivity($customer, CustomerActivityLog::ACTION_UNBLOCKED, [], $performedBy);
    }

    // ── Suspend / Unsuspend ───────────────────────────────────────────────────

    public function suspend(
        User $customer,
        string $reason,
        ?Carbon $until = null,
        ?User $performedBy = null
    ): void {
        $oldStatus = $customer->status;

        $customer->forceFill([
            'status'           => User::STATUS_SUSPENDED,
            'suspended_reason' => $reason,
            'suspended_until'  => $until,
        ])->save();

        $this->forceLogout($customer);

        $this->logActivity($customer, CustomerActivityLog::ACTION_SUSPENDED, [
            'reason'     => $reason,
            'until'      => $until?->toDateTimeString(),
            'old_status' => $oldStatus,
        ], $performedBy);
    }

    public function unsuspend(User $customer, ?User $performedBy = null): void
    {
        $customer->forceFill([
            'status'           => User::STATUS_ACTIVE,
            'suspended_reason' => null,
            'suspended_until'  => null,
        ])->save();

        $this->logActivity($customer, CustomerActivityLog::ACTION_UNSUSPENDED, [], $performedBy);
    }

    // ── Product Access ────────────────────────────────────────────────────────

    public function grantAccess(User $customer, ?User $performedBy = null): void
    {
        $customer->forceFill(['has_product_access' => true])->save();

        $this->logActivity($customer, CustomerActivityLog::ACTION_ACCESS_GRANTED, [], $performedBy);
    }

    public function revokeAccess(User $customer, ?User $performedBy = null): void
    {
        $customer->forceFill(['has_product_access' => false])->save();

        $this->forceLogout($customer);

        $this->logActivity($customer, CustomerActivityLog::ACTION_ACCESS_REVOKED, [], $performedBy);
    }

    // ── Force Logout ──────────────────────────────────────────────────────────

    public function forceLogout(User $customer, ?User $performedBy = null): void
    {
        // 1. Destroy all DB sessions for this user
        DB::table('sessions')->where('user_id', $customer->id)->delete();

        // 2. Rotate remember-token to invalidate all "remember me" cookies
        DB::table('users')
            ->where('id', $customer->id)
            ->update(['remember_token' => Str::random(60)]);

        if ($performedBy !== null) {
            $this->logActivity($customer, CustomerActivityLog::ACTION_FORCE_LOGOUT, [], $performedBy);
        }
    }

    // ── Password Reset ────────────────────────────────────────────────────────

    public function sendPasswordResetEmail(User $customer, ?User $performedBy = null): void
    {
        $token = Password::createToken($customer);

        Mail::to($customer->email)->send(new ProductAccessApprovedMail($customer, $token));

        $this->logActivity($customer, CustomerActivityLog::ACTION_PASSWORD_RESET, [], $performedBy);
    }

    // ── Activity Logging ──────────────────────────────────────────────────────

    public function logActivity(
        User $user,
        string $action,
        array $context = [],
        ?User $performedBy = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): CustomerActivityLog {
        return CustomerActivityLog::create([
            'user_id'      => $user->id,
            'action'       => $action,
            'ip_address'   => $ipAddress,
            'user_agent'   => $userAgent ? mb_substr($userAgent, 0, 500) : null,
            'context'      => empty($context) ? null : $context,
            'performed_by' => $performedBy?->id,
        ]);
    }

    // ── Auto-expire Suspension ────────────────────────────────────────────────

    /**
     * Called by middleware when a suspension is found to be expired.
     * Cleans up the suspension state and returns the now-active user.
     */
    public function expireSuspension(User $customer): void
    {
        $customer->forceFill([
            'status'           => User::STATUS_ACTIVE,
            'suspended_reason' => null,
            'suspended_until'  => null,
        ])->save();

        $this->logActivity($customer, CustomerActivityLog::ACTION_UNSUSPENDED, [
            'reason' => 'Suspension period expired automatically',
        ]);
    }
}