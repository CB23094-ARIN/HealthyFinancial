<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = ['user_id', 'category', 'monthly_limit', 'month_year'];

    protected $casts = [
        'month_year' => 'date',
    ];
}