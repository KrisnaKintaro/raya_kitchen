<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
protected $fillable = [
        'company_name',
        'established_year',
        'background_history',
        'short_description',
        'total_products_made',
        'whatsapp_number',
        'email_address',
        'address',
        'map_embed_url',
        'map_external_url',  
        'instagram_handle',
        'image_url',
    ];
}
