<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    // Columns that are allowed to be filled by user
    protected $fillable = [
        'city_name',
        'country_code',
        'notes',
    ];
}
