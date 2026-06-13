<?php
return [
    'required' => 'Le champ :attribute est obligatoire.',
    'email'    => ':attribute doit être une adresse e-mail valide.',
    'min'      => [
        'string' => ':attribute doit contenir au moins :min caractères.',
    ],
    'max'      => [
        'string' => ':attribute ne peut pas contenir plus de :max caractères.',
    ],
    'unique'   => 'Ce :attribute est déjà utilisé.',
    'confirmed'=> 'La confirmation de :attribute ne correspond pas.',
    'accepted' => ':attribute doit être accepté.',
    // ... etc
    'attributes' => [
        'full_name'    => 'Nom complet',
        'vat_number'   => 'Numéro de TVA',
        'first_name'   => 'Prénom',
        'last_name'    => 'Nom',
        'mobile_phone' => 'Téléphone mobile',
        'email'        => 'E-mail',
        'password'     => 'Mot de passe',
        'message'      => 'Message',
        'company'      => 'Entreprise',
        'phone'        => 'Téléphone',
        'subject'      => 'Sujet',
        'country'      => 'Pays',
    ],
];