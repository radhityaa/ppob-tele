<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'group',
        'code',
        'name',
        'fee',
        'percent_fee',
        'icon_url',
        'status',
        'provider',
    ];
}
