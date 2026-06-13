<?php
return [
    'required' => ':attribute alanı zorunludur.',
    'email'    => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'min'      => [
        'string' => ':attribute en az :min karakter olmalıdır.',
    ],
    'max'      => [
        'string' => ':attribute en fazla :max karakter olabilir.',
    ],
    'unique'   => 'Bu :attribute zaten kullanılmaktadır.',
    'confirmed'=> ':attribute onayı eşleşmiyor.',
    'accepted' => ':attribute kabul edilmelidir.',
    // ... etc
    'attributes' => [
        'full_name'    => 'Ad Soyad',
        'vat_number'   => 'Vergi Numarası',
        'first_name'   => 'Ad',
        'last_name'    => 'Soyad',
        'mobile_phone' => 'Cep Telefonu',
        'email'        => 'E-posta',
        'password'     => 'Şifre',
        'message'      => 'Mesaj',
        'company'      => 'Şirket',
        'phone'        => 'Telefon',
        'subject'      => 'Konu',
        'country'      => 'Ülke',
    ],
];