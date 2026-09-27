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

        return view('ops.customers.index', [
            'customers' => $customers,
            'stats' => [
                'total' => $customers->count(),
                'draft' => $customers->where('status', Customer::STATUS_DRAFT)->count(),
                'active' => $customers->where('status', Customer::STATUS_ACTIVE)->count(),
                'blocked' => $customers->whereIn('status', [Customer::STATUS_BLOCKED, Customer::STATUS_SUSPENDED])->count(),
            ],
            'showAddForm' => $request->boolean('new'),
        ]);
    }

    public function store(Request $request, CustomerCredentialService $credentials)
    {
        $data = $request->validate([
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
        ]);

        $customer = $credentials->saveDraft(
            collect($data)->except('contacts')->toArray(),
            $data['contacts'] ?? []
        );

        return redirect()->route('ops.customers.index')->with('status', "Saved {$customer->company_name} as a draft.");
    }

    public function send(Customer $customer, CustomerCredentialService $credentials)
    {
        abort_unless(
            $customer->contacts()->where('receives_notifications', true)->whereNotNull('email')->exists(),
            422,
            'No notifiable contact with an email on file.'
        );

        $credentials->sendRegistration($customer, auth()->id());

        return back()->with('status', 'Registration sent.');
    }

    public function resetPassword(Customer $customer, CustomerCredentialService $credentials)
    {
        $credentials->resetPassword($customer, auth()->id());

        return back()->with('status', 'New password sent.');
    }
}