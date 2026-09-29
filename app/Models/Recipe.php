<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'image_url',
        'ingredients',
        'instructions',
    ];

    protected $casts = [
        'ingredients' => 'array',
    ];
}
