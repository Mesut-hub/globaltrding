<?php

namespace App\Services;

use App\Mail\CustomerPasswordResetMail;
use App\Mail\CustomerRegistrationMail;
use App\Models\CompanyActivityLog;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerCredentialService
{
    public function generateCustomerCode(): string
    {
        // Excludes 0/O/1/I to avoid visual ambiguity when read aloud or typed.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $year = now()->format('y');

        do {
            $suffix = '';
            for ($i = 0; $i < 5; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = "GT-{$year}-{$suffix}";
        } while (Customer::withTrashed()->where('customer_code', $code)->exists());

        return $code;
    }

    public function generateUsername(string $companyName): string
    {
        $base = Str::of($companyName)->slug('')->lower()->limit(16, '')->toString();
        $base = $base !== '' ? $base : 'customer';

        $username = $base;
        $i = 1;
        while (Customer::withTrashed()->where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        return $username;
    }

    public function generatePassword(): string
    {
        return Str::password(12, symbols: false);
    }

    /**
     * Save button: create/update as draft only, contact anyone.
     */
    public function saveDraft(array $companyData, array $contacts, ?Customer $existing = null): Customer
    {
        $customer = $existing ?? new Customer();
        $customer->fill($companyData);

        if (! $customer->exists) {
            $customer->customer_code = $this->generateCustomerCode();
            $customer->username = $this->generateUsername($companyData['company_name']);
            $customer->password = Hash::make(Str::random(32)); // placeholder until sent
            $customer->status = Customer::STATUS_DRAFT;
        }

        $customer->save();

        $customer->contacts()->delete();
        foreach ($contacts as $i => $c) {
            if (blank($c['name'] ?? null)) continue;
            $customer->contacts()->create([
                'role' => $c['role'] ?? 'other',
                'name' => $c['name'],
                'phone' => $c['phone'] ?? null,
                'email' => $c['email'] ?? null,
                'receives_notifications' => $c['receives_notifications'] ?? true,
                'sort_order' => $i,
            ]);
        }

        return $customer;
    }

    /**
     * Save & send: generates real credentials, activates the account, emails every notified contact.
     */
    public function sendRegistration(Customer $customer, ?int $performedBy = null): void
    {
        $plainPassword = $this->generatePassword();

        $customer->password = Hash::make($plainPassword);
        $customer->must_change_password = true;
        $customer->status = Customer::STATUS_ACTIVE;
        $customer->registration_sent_at = now();
        $customer->save();

        $recipients = $customer->notificationContacts()->get();

        foreach ($recipients as $contact) {
            Mail::to($contact->email)->send(new CustomerRegistrationMail($customer, $contact, $plainPassword));
        }

        $this->log($customer, 'registration_sent', ['recipients' => $recipients->pluck('email')->all()], $performedBy);
    }

    /**
     * Send an update notification to all contacts that are set to receive notifications.
     */
    public function sendUpdateNotification(Customer $customer, ?int $performedBy = null): void
    {
        $recipients = $customer->notificationContacts()->get();
        $failed = [];

        foreach ($recipients as $contact) {
            try {
                Mail::to($contact->email)->send(new \App\Mail\CustomerDetailsUpdatedMail($customer, $contact));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Details-updated email failed', ['customer_id' => $customer->id, 'to' => $contact->email, 'error' => $e->getMessage()]);
                $failed[] = $contact->email;
            }
        }

        $this->log($customer, 'details_updated_sent', ['recipients' => $recipients->pluck('email')->all(), 'failed' => $failed], $performedBy);
    }

    /**
     * Same routine whether triggered by the customer's own "Reset" form (after identity match)
     * or by the admin's "Reset password" button in the dashboard.
     */
    public function resetPassword(Customer $customer, ?int $performedBy = null): void
    {
        $plainPassword = $this->generatePassword();

        $customer->password = Hash::make($plainPassword);
        $customer->must_change_password = true;
        $customer->save();

        $recipients = $customer->notificationContacts()->get();

        foreach ($recipients as $contact) {
            Mail::to($contact->email)->send(new CustomerPasswordResetMail($customer, $contact, $plainPassword));
        }

        $this->log($customer, 'password_reset', [
            'recipients' => $recipients->pluck('email')->all(),
            'triggered_by' => $performedBy ? 'admin' : 'customer',
        ], $performedBy);
    }

    public function log(Customer $customer, string $action, array $context = [], ?int $performedBy = null): void
    {
        CompanyActivityLog::create([
            'customer_id' => $customer->id,
            'action' => $action,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'context' => $context,
            'performed_by' => $performedBy,
        ]);
    }
}