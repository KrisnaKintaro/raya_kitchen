<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappSession extends Model
{
    protected $fillable = [
        'whatsapp_number',
        'current_step',
        'session_data',
        'last_interaction_at',
    ];

    protected $casts = [
        'session_data' => 'array',
        'last_interaction_at' => 'datetime',
    ];
}
