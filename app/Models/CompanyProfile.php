<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'company_name',
        'established_year',
        'background_history',
        'total_products_made',
        'whatsapp_number',
        'instagram_handle',
        'image_url',
    ];
}