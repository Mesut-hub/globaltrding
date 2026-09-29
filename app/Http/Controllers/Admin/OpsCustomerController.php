<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerCredentialService;
use Illuminate\Http\Request;

class OpsCustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::with(['contacts' => fn ($q) => $q->orderBy('sort_order')])->latest()->get();

        $editing = $request->filled('edit') ? $customers->firstWhere('id', (int) $request->query('edit')) : null;

        return view('ops.customers.index', [
            'customers' => $customers,
            'stats' => [
                'total' => $customers->count(),
                'draft' => $customers->where('status', Customer::STATUS_DRAFT)->count(),
                'active' => $customers->where('status', Customer::STATUS_ACTIVE)->count(),
                'blocked' => $customers->whereIn('status', [Customer::STATUS_BLOCKED, Customer::STATUS_SUSPENDED])->count(),
            ],
            'showAddForm' => $request->boolean('new') || $editing !== null,
            'editing' => $editing,
        ]);
    }

    private function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'full_commercial_name' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'contacts' => ['array'],
            'contacts.*.role' => ['required', 'in:owner,contact_person,other'],
            'contacts.*.name' => ['nullable', 'string', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:50'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'preferred_locale' => ['required', 'in:en,tr,ar,fr'],
            'action' => ['nullable', 'in:save,send'],
        ];
    }

    private function hasNotifiableContact(Customer $customer): bool
    {
        return $customer->contacts()->where('receives_notifications', true)->whereNotNull('email')->exists();
    }

    public function store(Request $request, CustomerCredentialService $credentials)
    {
        $data = $request->validate($this->rules());

        $customer = $credentials->saveDraft(
            collect($data)->except(['contacts', 'action'])->toArray(),
            $data['contacts'] ?? []
        );

        if (($data['action'] ?? 'save') === 'send') {
            abort_unless($this->hasNotifiableContact($customer), 422, 'No notifiable contact with an email on file.');
            $credentials->sendRegistration($customer, auth()->id());
        }

        return redirect()->route('ops.customers.index')->with('status', "Saved {$customer->company_name}.");
    }

    public function update(Request $request, Customer $customer, CustomerCredentialService $credentials)
    {
        $data = $request->validate($this->rules());

        $credentials->saveDraft(
            collect($data)->except(['contacts', 'action'])->toArray(),
            $data['contacts'] ?? [],
            $customer
        );

        if (($data['action'] ?? 'save') === 'send' && $customer->isDraft()) {
            abort_unless($this->hasNotifiableContact($customer), 422, 'No notifiable contact with an email on file.');
            $credentials->sendRegistration($customer, auth()->id());
        }

        return redirect()->route('ops.customers.index')->with('status', "Updated {$customer->company_name}.");
    }

    public function send(Customer $customer, CustomerCredentialService $credentials)
    {
        abort_unless($this->hasNotifiableContact($customer), 422, 'No notifiable contact with an email on file.');
        $credentials->sendRegistration($customer, auth()->id());

        return back()->with('status', 'Registration sent.');
    }

    public function resetPassword(Customer $customer, CustomerCredentialService $credentials)
    {
        $credentials->resetPassword($customer, auth()->id());

        return back()->with('status', 'New password sent.');
    }

    public function suspend(Customer $customer)
    {
        $customer->update(['status' => Customer::STATUS_SUSPENDED, 'suspended_reason' => 'Suspended from admin']);

        return back()->with('status', 'Customer suspended.');
    }

    public function block(Customer $customer)
    {
        $customer->update(['status' => Customer::STATUS_BLOCKED, 'blocked_at' => now(), 'blocked_reason' => 'Blocked from admin']);

        return back()->with('status', 'Customer blocked.');
    }

    public function reactivate(Customer $customer)
    {
        $customer->update([
            'status' => Customer::STATUS_ACTIVE,
            'blocked_at' => null, 'blocked_reason' => null,
            'suspended_until' => null, 'suspended_reason' => null,
        ]);

        return back()->with('status', 'Customer reactivated.');
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->company_name;
        $customer->delete();

        return redirect()->route('ops.customers.index')->with('status', "Deleted {$name}.");
    }
}