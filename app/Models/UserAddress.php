<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'recipient_whatsapp',
        'province',
        'city',
        'district',
        'postal_code',
        'street_address',
        'landmark',
        'is_primary',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
