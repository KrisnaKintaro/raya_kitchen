<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreHoliday extends Model
{
    protected $fillable = [
        'holiday_date',
        'description'
    ];
}
