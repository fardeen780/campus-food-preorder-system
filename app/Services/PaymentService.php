<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentRecordStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function record(
        Order $order,
        string|int $amount,
        PaymentMethod $method,
        User $recordedBy,
        ?string $reference = null,
    ): Payment {
        $amount = $this->parseAmount($amount);

        try {
            $payment = DB::transaction(function () use ($order, $amount, $method, $recordedBy, $reference) {
                $locked = Order::query()->lockForUpdate()->findOrFail($order->getKey());

                if ($locked->order_status === OrderStatus::Cancelled) {
                    throw new PaymentException('Payments cannot be recorded for a cancelled order.');
                }

                $newPaid = BigDecimal::of($this->successfulTotal($locked))->plus($amount);

                if ($newPaid->isGreaterThan($locked->total_amount)) {
                    throw new PaymentException('This payment is more than the amount still due.');
                }

                $payment = (new Payment)->forceFill([
                    'order_id' => $locked->id,
                    'amount' => (string) $amount,
                    'method' => $method,
                    'status' => PaymentRecordStatus::Successful,
                    'reference' => $reference,
                    'recorded_by' => $recordedBy->id,
                    'paid_at' => now(),
                ]);
                $payment->save();

                $this->syncOrder($locked);

                return $payment;
            });
        } catch (UniqueConstraintViolationException) {
            throw new PaymentException('This transaction reference has already been recorded.');
        }

        $order->refresh();

        return $payment;
    }

    public function refund(Payment $payment): Payment
    {
        $refunded = DB::transaction(function () use ($payment) {
            $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);
            $fresh = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());

            if ($fresh->status !== PaymentRecordStatus::Successful) {
                throw new PaymentException('Only successful payments can be refunded.');
            }

            $fresh->forceFill(['status' => PaymentRecordStatus::Refunded])->save();

            $this->syncOrder($order);

            return $fresh;
        });

        $payment->refresh();

        return $refunded;
    }

    private function parseAmount(string|int $amount): BigDecimal
    {
        try {
            $value = BigDecimal::of($amount);
        } catch (MathException) {
            throw new PaymentException('The amount is not a valid number.');
        }

        if ($value->isLessThanOrEqualTo(0)) {
            throw new PaymentException('The amount must be greater than zero.');
        }

        if ($value->getScale() > 2) {
            throw new PaymentException('The amount can have at most two decimal places.');
        }

        return $value->toScale(2);
    }

    private function successfulTotal(Order $order): string
    {
        return (string) Payment::query()
            ->where('order_id', $order->id)
            ->where('status', PaymentRecordStatus::Successful->value)
            ->sum('amount');
    }

    private function syncOrder(Order $order): void
    {
        $paid = BigDecimal::of($this->successfulTotal($order))->toScale(2);

        if ($paid->isEqualTo($order->total_amount)) {
            $status = PaymentStatus::Paid;
        } elseif ($order->order_status === OrderStatus::Completed) {
            $status = PaymentStatus::Due;
        } elseif ($paid->isZero()) {
            $status = PaymentStatus::Unpaid;
        } else {
            $status = PaymentStatus::PartiallyPaid;
        }

        $order->forceFill([
            'paid_amount' => (string) $paid,
            'payment_status' => $status,
        ])->save();
    }
}