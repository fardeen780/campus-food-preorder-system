<?php

namespace App\Models;

use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'food_item_id',
        'quantity',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class)->withTrashed();
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(
            fn () => (string) BigDecimal::of($this->foodItem->price)
                ->multipliedBy($this->quantity)
                ->toScale(2)
        );
    }
}