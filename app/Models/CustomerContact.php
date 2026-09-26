<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    const ROLE_OWNER = 'owner';
    const ROLE_CONTACT_PERSON = 'contact_person';
    const ROLE_OTHER = 'other';

    public static function roleOptions(): array
    {
        return [
            self::ROLE_OWNER => 'Owner',
            self::ROLE_CONTACT_PERSON => 'Contact person',
            self::ROLE_OTHER => 'Other',
        ];
    }

    protected $fillable = [
        'customer_id', 'role', 'name', 'phone', 'email', 'receives_notifications', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['receives_notifications' => 'boolean'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_responsible_persons');
    }
}