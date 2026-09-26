<?php

namespace App\Models;

use App\Enums\OrderStatusStage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT  = 'sent';

    public static function quantityUnitOptions(): array
    {
        return ['pcs' => 'PCs', 'kg' => 'Kg', 'g' => 'g', 'ton' => 'Ton'];
    }

    public static function deliveryPointOptions(): array
    {
        return [
            'port_mersin'  => 'Port — Mersin',
            'port_ambarli' => 'Port — Ambarlı',
            'port_gebze'   => 'Port — Gebze',
            'other'        => 'Other / DAP — customer location',
        ];
    }

    protected $fillable = [
        'customer_id', 'order_number', 'order_date', 'delivered_date', 'balanced_finished_date',
        'product_name', 'description', 'quantity', 'quantity_unit', 'payment_term',
        'is_contracted', 'delivery_time', 'shipping_term', 'delivery_point', 'delivery_address',
        'status', 'sent_at', 'status_last_sent_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'delivered_date' => 'date',
            'balanced_finished_date' => 'date',
            'is_contracted' => 'boolean',
            'sent_at' => 'datetime',
            'status_last_sent_at' => 'datetime',
            'quantity' => 'decimal:3',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function responsiblePersons()
    {
        return $this->belongsToMany(CustomerContact::class, 'order_responsible_persons');
    }

    public function statusUpdates()
    {
        return $this->hasMany(OrderStatusUpdate::class)->orderBy('stage_date')->orderBy('id');
    }

    public function isSent(): bool { return $this->status === self::STATUS_SENT; }

    public function isDelivered(): bool
    {
        return $this->statusUpdates()->where('stage_key', OrderStatusStage::DELIVERED->value)->exists();
    }

    public function currentStageKey(): ?string
    {
        return $this->statusUpdates()->latest('stage_date')->value('stage_key');
    }
}