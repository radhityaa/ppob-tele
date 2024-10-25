<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationState extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'step',
        'name',
        'phone',
        'shop_name',
        'expires_at',
    ];
}
