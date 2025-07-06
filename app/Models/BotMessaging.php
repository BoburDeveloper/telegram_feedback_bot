<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotMessaging extends Model
{
    protected $fillable = [
           'telegraph_bot_id',
           'message',
           'image',
           'caption',
           'created_at',
           'updated_at',
    ];
}
