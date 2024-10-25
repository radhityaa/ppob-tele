<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;
    protected $with = ['user'];
    protected $guarded = [];
    protected $casts = [
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'invoice';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
