<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    protected $fillable = [
        'whatsapp_number',
        'direction',
        'message',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];
}
