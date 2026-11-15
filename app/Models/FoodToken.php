<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodToken extends Model
{
    protected $fillable = [
        'token_code',
        'current_owner_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function currentOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_owner_id');
    }
        public function transfers(): HasMany
    {
        return $this->hasMany(TokenTransfer::class);
    }

    public static function generateCode(int $length = 8): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (static::where('token_code', $code)->exists());

        return $code;
    }
}