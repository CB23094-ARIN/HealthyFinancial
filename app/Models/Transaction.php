<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'description', 'amount', 'category', 'is_auto_categorized', 'transaction_date'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'is_auto_categorized' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}