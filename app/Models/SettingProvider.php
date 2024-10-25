<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'mode',
        'type',
        'api_key',
        'private_key',
        'code',
        'username',
        'webhook_url',
        'webhook_id',
        'webhook_secret',
    ];
}
