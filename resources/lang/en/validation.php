<?php
return [
    'required' => 'The :attribute field is required.',
    'email'    => 'The :attribute must be a valid email address.',
    'min'      => [
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'max'      => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'unique'   => 'This :attribute has already been taken.',
    'confirmed'=> 'The :attribute confirmation does not match.',
    'accepted' => 'The :attribute must be accepted.',
    // ... etc
    'attributes' => [
        'full_name'    => 'Full Name',
        'vat_number'   => 'VAT Number',
        'first_name'   => 'First Name',
        'last_name'    => 'Last Name',
        'mobile_phone' => 'Mobile Phone',
        'email'        => 'Email',
        'password'     => 'Password',
        'message'      => 'Message',
        'company'      => 'Company',
        'phone'        => 'Phone',
        'subject'      => 'Subject',
        'country'      => 'Country',
    ],
];