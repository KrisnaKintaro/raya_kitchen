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
        'delivery_date',
        'delivery_time',
        'total_amount',
        'dp_amount',
        'remaining_amount',
        'status',
        'order_source',
        'cancellation_reason',
        'refund_account_number',
        'refund_proof',
        'refund_status',
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
