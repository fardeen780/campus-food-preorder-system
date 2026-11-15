<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Brick\Math\BigDecimal;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const CANCELLATION_WINDOW_MINUTES = 30;

    protected $fillable = [
        'original_student_id',
        'outlet_id',
        'pickup_datetime',
        'total_amount',
    ];

    protected $attributes = [
        'order_status' => 'pending',
        'payment_status' => 'unpaid',
        'paid_amount' => 0,
    ];

    protected function casts(): array
    {
        return [
            'pickup_datetime' => 'datetime',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'order_status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function originalStudent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_student_id');
    }

    public function financiallyResponsibleStudent(): BelongsTo
    {
        return $this->originalStudent();
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function token(): HasOne
    {
        return $this->hasOne(FoodToken::class);
    }
        public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function dueAmount(): Attribute
    {
        return Attribute::get(
            fn () => (string) BigDecimal::of($this->total_amount)
                ->minus($this->paid_amount)
                ->toScale(2)
        );
    }

    protected function cancellationDeadline(): Attribute
    {
        return Attribute::get(
            fn () => $this->pickup_datetime->copy()->subMinutes(self::CANCELLATION_WINDOW_MINUTES)
        );
    }

    public function isCancellationWindowOpen(?CarbonInterface $at = null): bool
    {
        return ($at ?? now())->lessThan($this->cancellation_deadline);
    }
}