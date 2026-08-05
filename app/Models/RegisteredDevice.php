<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegisteredDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'device_model',
        'device_fingerprint',
        'user_agent',
        'registered_at',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'last_used_at'  => 'datetime',
        'is_active'     => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
