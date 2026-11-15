<?php

namespace App\Models;

use App\Enums\TransferStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenTransfer extends Model
{
    protected $fillable = [
        'food_token_id',
        'sender_id',
        'receiver_id',
        'requested_at',
        'expires_at',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransferStatus::class,
            'requested_at' => 'datetime',
            'responded_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function foodToken(): BelongsTo
    {
        return $this->belongsTo(FoodToken::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', TransferStatus::Pending->value);
    }

    public function isPending(): bool
    {
        return $this->status === TransferStatus::Pending;
    }

    public function isExpired(): bool
    {
        return $this->isPending() && $this->expires_at->isPast();
    }
}