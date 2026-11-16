<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\ActivityLog;
use App\Models\FoodItem;
use App\Models\FoodToken;
use App\Models\Order;
use App\Models\User;
use App\Support\PickupSlots;
use Brick\Math\BigDecimal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function place(User $student, string $pickup): Collection
    {
        if (! PickupSlots::isValid($pickup)) {
            throw new OrderException('Please choose a valid pickup time.');
        }

        $pickupAt = PickupSlots::parse($pickup);

        return DB::transaction(function () use ($student, $pickupAt) {
            $cart = $student->cartItems()->lockForUpdate()->get();

            if ($cart->isEmpty()) {
                throw new OrderException('Your cart is empty.');
            }

            $foods = FoodItem::withTrashed()
                ->with(['outlet', 'category'])
                ->whereIn('id', $cart->pluck('food_item_id'))
                ->get()
                ->keyBy('id');

            foreach ($cart as $line) {
                $food = $foods[$line->food_item_id];

                if (! $food->isOrderable() || ! $food->category?->is_active) {
                    throw new OrderException($food->name.' is no longer available. Please remove it from your cart.');
                }
            }

            $orders = collect();

            $byOutlet = $cart->groupBy(fn ($line) => $foods[$line->food_item_id]->outlet_id);

            foreach ($byOutlet as $outletId => $lines) {
                $total = BigDecimal::zero();

                foreach ($lines as $line) {
                    $total = $total->plus(
                        BigDecimal::of($foods[$line->food_item_id]->price)->multipliedBy($line->quantity)
                    );
                }

                $order = Order::create([
                    'original_student_id' => $student->id,
                    'outlet_id' => $outletId,
                    'pickup_datetime' => $pickupAt,
                    'total_amount' => (string) $total->toScale(2),
                ]);

                foreach ($lines as $line) {
                    $food = $foods[$line->food_item_id];

                    $order->items()->create([
                        'food_item_id' => $food->id,
                        'item_name' => $food->name,
                        'quantity' => $line->quantity,
                        'unit_price' => $food->price,
                    ]);
                }

                $order->token()->create([
                    'token_code' => FoodToken::generateCode(),
                    'current_owner_id' => $student->id,
                ]);

                $order->refresh();

                ActivityLog::record('order.placed', $student, $order, 'Order '.$order->order_number.' placed');

                $orders->push($order);
            }

            $student->cartItems()->delete();

            return $orders;
        });
    }

    public function cancellationBlockReason(Order $order, User $student): ?string
    {
        if ((int) $order->original_student_id !== (int) $student->id) {
            return 'Only the student who placed this order can cancel it.';
        }

        if (! in_array($order->order_status, [OrderStatus::Pending, OrderStatus::Confirmed], true)) {
            return 'This order can no longer be cancelled because it is '.strtolower($order->order_status->label()).'.';
        }

        if (! $order->isCancellationWindowOpen()) {
            return 'Cancellation closed at '.$order->cancellation_deadline->format('g:i A')
                .'. Orders can only be cancelled until 30 minutes before pickup.';
        }

        if (BigDecimal::of($order->paid_amount)->isPositive()) {
            return 'A payment has already been recorded for this order. Please ask cafeteria staff for help.';
        }

        $token = $order->token;

        if ($token && $token->transfers()->whereIn('status', ['pending', 'accepted'])->exists()) {
            return 'This order cannot be cancelled because its token has been, or is being, transferred.';
        }

        return null;
    }

    public function cancel(User $student, Order $order): Order
    {
        return DB::transaction(function () use ($student, $order) {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            $token = FoodToken::query()->where('order_id', $locked->id)->lockForUpdate()->first();
            $locked->setRelation('token', $token);

            $reason = $this->cancellationBlockReason($locked, $student);

            if ($reason !== null) {
                throw new OrderException($reason);
            }

            $locked->forceFill([
                'order_status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
            ])->save();

            ActivityLog::record('order.cancelled', $student, $locked, 'Order '.$locked->order_number.' cancelled by student');

            return $locked;
        });
    }
}