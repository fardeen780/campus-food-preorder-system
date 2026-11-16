<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\OrderException;
use App\Models\ActivityLog;
use App\Models\FoodToken;
use App\Models\Order;
use App\Models\TokenTransfer;
use App\Models\User;
use App\Notifications\OrderNotification;
use Brick\Math\BigDecimal;
use Illuminate\Support\Facades\DB;

class StaffOrderService
{
    private const NEXT = [
        'pending' => 'confirmed',
        'confirmed' => 'preparing',
        'preparing' => 'ready',
    ];

    public function advance(User $staff, int $orderId, OrderStatus $to): Order
    {
        return DB::transaction(function () use ($staff, $orderId, $to) {
            $order = $this->lockForStaff($staff, $orderId);
            $current = $order->order_status;

            if ((self::NEXT[$current->value] ?? null) !== $to->value) {
                throw new OrderException(
                    'This order cannot move from '.strtolower($current->label()).' to '.strtolower($to->label()).'.'
                );
            }

            $order->forceFill(['order_status' => $to])->save();

            $messages = [
                'confirmed' => ['Order confirmed', 'was confirmed by the cafeteria.'],
                'preparing' => ['Order being prepared', 'is now being prepared.'],
                'ready' => ['Order ready for pickup', 'is ready for pickup.'],
            ];

            [$title, $text] = $messages[$to->value];
            $this->notify($order, $to->value, $title, 'Order '.$order->order_number.' '.$text);

            ActivityLog::record('order.'.$to->value, $staff, $order, 'Order '.$order->order_number.' marked '.$to->label());

            return $order;
        });
    }

    public function collect(User $staff, int $orderId, string $code): Order
    {
        $code = strtoupper((string) preg_replace('/\s+/', '', $code));

        return DB::transaction(function () use ($staff, $orderId, $code) {
            $order = $this->lockForStaff($staff, $orderId);

            $token = FoodToken::query()->where('order_id', $order->id)->lockForUpdate()->firstOrFail();

            if (! hash_equals($token->token_code, $code)) {
                ActivityLog::record('token.verify_failed', $staff, $order, 'Wrong token code entered at collection');

                throw new OrderException('That token code does not match this order.');
            }

            if ($order->order_status !== OrderStatus::Ready) {
                throw new OrderException(
                    'Only orders marked Ready can be collected. This order is '.strtolower($order->order_status->label()).'.'
                );
            }

            $fullyPaid = BigDecimal::of($order->paid_amount)->isEqualTo($order->total_amount);

            $order->forceFill([
                'order_status' => OrderStatus::Completed,
                'completed_at' => now(),
                'payment_status' => $fullyPaid ? PaymentStatus::Paid : PaymentStatus::Due,
            ])->save();

            TokenTransfer::query()
                ->where('food_token_id', $token->id)
                ->where('status', 'pending')
                ->update(['status' => 'expired']);

            $holder = User::find($token->current_owner_id);

            $this->notify($order, 'completed', 'Order collected',
                'Order '.$order->order_number.' was collected by '.($holder?->name ?? 'the token holder').'.');

            ActivityLog::record('order.collected', $staff, $order,
                'Order '.$order->order_number.' collected', ['holder_id' => $token->current_owner_id]);

            return $order;
        });
    }

    private function lockForStaff(User $staff, int $orderId): Order
    {
        $order = Order::query()->lockForUpdate()->find($orderId);

        if (! $order || $staff->outlet_id === null || (int) $order->outlet_id !== (int) $staff->outlet_id) {
            throw new OrderException('Order not found for your outlet.');
        }

        return $order;
    }

    private function notify(Order $order, string $kind, string $title, string $message): void
    {
        $original = User::find($order->original_student_id);
        $token = FoodToken::query()->where('order_id', $order->id)->first();

        $original?->notify(new OrderNotification(
            $order->id, $kind, $title, $message, route('student.orders.show', $order->id, false)
        ));

        if ($token
            && (int) $token->current_owner_id !== (int) $order->original_student_id
            && in_array($kind, ['ready', 'completed'], true)) {
            User::find($token->current_owner_id)?->notify(new OrderNotification(
                $order->id, $kind, $title, $message, route('student.tokens', [], false)
            ));
        }
    }
}