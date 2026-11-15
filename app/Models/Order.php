<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'recipient_name',
        'recipient_whatsapp',
        'shipping_address',
        'shipping_cost',
        'courier_name',
        'shipping_service',
        'tracking_number',
        'courier_status',
        'courier_manifest',
        'delivery_date',
        'delivery_time',
        'total_amount',
        'dp_amount',
        'remaining_amount',
        'status',
        'delivery_proof',
        'arrival_proof',
        'payment_proof',
        'order_source',
        'cancellation_reason',
        'refund_account_number',
        'refund_proof',
        'refund_status',
    ];

    protected $casts = [
        'courier_manifest' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
