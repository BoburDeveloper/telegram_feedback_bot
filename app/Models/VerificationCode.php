<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class VerificationCode extends Model
{
    protected $fillable = [
        'chat_id',
        'code',
        'expires_at',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
