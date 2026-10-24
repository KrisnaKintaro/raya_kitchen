<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'max_qty_per_day',
        'max_orders_per_hour',
        'operational_time_start',
        'operational_time_end'
    ];
}
