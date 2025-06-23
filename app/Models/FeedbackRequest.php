<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackRequest extends Model
{
    protected $fillable = [
           'full_name',
           'username',
           'chat_id',
           'message',
           'bot_id',
           'is_answered',
           'created_at',
           'updated_at',
    ];
}
