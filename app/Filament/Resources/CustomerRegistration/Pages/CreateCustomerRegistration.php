<?php

namespace App\Filament\Resources\CustomerRegistration\Pages;

use App\Filament\Resources\CustomerRegistration\CustomerRegistrationResource;
use App\Models\Customer;
use App\Services\CustomerCredentialService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateCustomerRegistration extends CreateRecord
{
    protected static string $resource = CustomerRegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $credentials = app(CustomerCredentialService::class);

        $data['customer_code'] = $credentials->generateCustomerCode();
        $data['username'] = $credentials->generateUsername($data['company_name']);
        $data['password'] = Hash::make(Str::random(32)); // placeholder — real one is set on "Send"
        $data['status'] = Customer::STATUS_DRAFT;
        $data['created_by'] = auth()->id();

        return $data;
    }
}