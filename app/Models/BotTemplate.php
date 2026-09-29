<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotTemplate extends Model
{
    protected $fillable = [
        'session_state',
        'message_text',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
