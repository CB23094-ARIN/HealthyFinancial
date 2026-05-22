<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    protected $table = 'leaderboard';
    protected $primaryKey = 'user_id';
    protected $fillable = ['user_id', 'campus', 'points'];
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
