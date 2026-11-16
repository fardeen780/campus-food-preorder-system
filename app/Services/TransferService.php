<?php

namespace App\Services;

use App\Enums\TransferStatus;
use App\Enums\UserRole;
use App\Exceptions\TransferException;
use App\Models\ActivityLog;
use App\Models\FoodToken;
use App\Models\Order;
use App\Models\Setting;
use App\Models\TokenTransfer;
use App\Models\User;
use App\Notifications\TransferNotification;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function transferBlockReason(FoodToken $token, User $sender, ?CarbonInterface $now = null): ?string
    {
        $order = $token->order;
        $now ??= now();

        if ((int) $token->current_owner_id !== (int) $sender->id) {
            return 'You do not currently hold this token.';
        }

        if (! $order->order_status->isActive()) {
            return 'This order is '.strtolower($order->order_status->label()).', so its token cannot be transferred.';
        }

        if ($order->pickup_datetime->lessThan($now->copy()->addMinute())) {
            return 'The pickup time is too close or has already passed.';
        }

        $pending = TokenTransfer::query()
            ->where('food_token_id', $token->id)
            ->where('status', TransferStatus::Pending->value)
            ->exists();

        if ($pending) {
            return 'A transfer request for this token is already waiting for a response.';
        }

        return null;
    }

    public function send(User $sender, int $tokenId, string $studentId): TokenTransfer
    {
        $this->expireDue();

        try {
            return DB::transaction(function () use ($sender, $tokenId, $studentId) {
                $peek = FoodToken::query()->findOrFail($tokenId);
                $order = Order::query()->lockForUpdate()->findOrFail($peek->order_id);
                $token = FoodToken::query()->lockForUpdate()->findOrFail($tokenId);
                $token->setRelation('order', $order);

                $now = now();

                $reason = $this->transferBlockReason($token, $sender, $now);
                if ($reason !== null) {
                    throw new TransferException($reason);
                }

                $receiver = User::query()
                    ->where('student_id', $studentId)
                    ->where('role', UserRole::Student->value)
                    ->where('is_active', true)
                    ->first();

                if (! $receiver) {
                    throw new TransferException('No active student was found with that student ID.');
                }

                if ((int) $receiver->id === (int) $sender->id) {
                    throw new TransferException('You cannot transfer a token to yourself.');
                }

                $expiresAt = $now->copy()->addHours(max(1, Setting::integer('transfer_expiry_hours')));
                if ($order->pickup_datetime->lessThan($expiresAt)) {
                    $expiresAt = $order->pickup_datetime->copy();
                }

                $transfer = TokenTransfer::create([
                    'food_token_id' => $token->id,
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'requested_at' => $now,
                    'expires_at' => $expiresAt,
                ]);

                $this->tell(
                    $receiver, $transfer, 'requested', 'New Food Token Transfer Request',
                    $sender->name.' (ID '.$sender->student_id.') wants to transfer a food token to you. '.$this->describe($order),
                    'student.transfers.received'
                );

                ActivityLog::record('transfer.requested', $sender, $transfer,
                    'Token transfer requested for order '.$order->order_number, ['receiver_id' => $receiver->id]);

                return $transfer;
            });
        } catch (UniqueConstraintViolationException) {
            throw new TransferException('A transfer request for this token is already waiting for a response.');
        }
    }

    public function accept(User $receiver, int $transferId): TokenTransfer
    {
        $this->expireDue();

        return DB::transaction(function () use ($receiver, $transferId) {
            [$order, $token, $transfer] = $this->lockAll($transferId);

            if ((int) $transfer->receiver_id !== (int) $receiver->id) {
                throw new TransferException('Only the intended recipient can respond to this request.');
            }

            $this->assertPending($transfer);

            if ($transfer->expires_at->isPast()) {
                throw new TransferException('This request has expired.');
            }

            if (! $order->order_status->isActive()) {
                throw new TransferException('This order is '.strtolower($order->order_status->label()).', so the token can no longer be transferred.');
            }

            if ($order->pickup_datetime->isPast()) {
                throw new TransferException('The pickup time has already passed.');
            }

            if ((int) $token->current_owner_id !== (int) $transfer->sender_id) {
                throw new TransferException('The sender no longer holds this token.');
            }

            if (! $receiver->is_active || ! $receiver->isStudent()) {
                throw new TransferException('Your account cannot receive tokens.');
            }

            $token->forceFill(['current_owner_id' => $receiver->id])->save();

            $transfer->forceFill([
                'status' => TransferStatus::Accepted,
                'responded_at' => now(),
            ])->save();

            $this->tell(
                $transfer->sender, $transfer, 'accepted', 'Token transfer accepted',
                $receiver->name.' accepted your food token transfer. '.$this->describe($order)
                    .' They can now collect it. You remain responsible for any outstanding payment.',
                'student.transfers.sent'
            );

            ActivityLog::record('transfer.accepted', $receiver, $transfer,
                'Token for order '.$order->order_number.' transferred', ['sender_id' => $transfer->sender_id]);

            return $transfer;
        });
    }

    public function reject(User $receiver, int $transferId): TokenTransfer
    {
        $this->expireDue();

        return DB::transaction(function () use ($receiver, $transferId) {
            [$order, , $transfer] = $this->lockAll($transferId);

            if ((int) $transfer->receiver_id !== (int) $receiver->id) {
                throw new TransferException('Only the intended recipient can respond to this request.');
            }

            $this->assertPending($transfer);

            $transfer->forceFill([
                'status' => TransferStatus::Rejected,
                'responded_at' => now(),
            ])->save();

            $this->tell(
                $transfer->sender, $transfer, 'rejected', 'Token transfer rejected',
                $receiver->name.' rejected your food token transfer. '.$this->describe($order).' The token stays with you.',
                'student.transfers.sent'
            );

            ActivityLog::record('transfer.rejected', $receiver, $transfer, 'Token transfer rejected for order '.$order->order_number);

            return $transfer;
        });
    }

    public function cancel(User $sender, int $transferId): TokenTransfer
    {
        $this->expireDue();

        return DB::transaction(function () use ($sender, $transferId) {
            [$order, , $transfer] = $this->lockAll($transferId);

            if ((int) $transfer->sender_id !== (int) $sender->id) {
                throw new TransferException('Only the sender can cancel this request.');
            }

            $this->assertPending($transfer);

            $transfer->forceFill([
                'status' => TransferStatus::Cancelled,
                'responded_at' => now(),
            ])->save();

            $this->tell(
                $transfer->receiver, $transfer, 'cancelled', 'Token transfer request cancelled',
                $sender->name.' cancelled the food token transfer request. '.$this->describe($order),
                'student.transfers.received'
            );

            ActivityLog::record('transfer.cancelled', $sender, $transfer, 'Token transfer cancelled for order '.$order->order_number);

            return $transfer;
        });
    }

    public function expireDue(): int
    {
        $ids = TokenTransfer::query()
            ->where('status', TransferStatus::Pending->value)
            ->where('expires_at', '<=', now())
            ->pluck('id');

        $count = 0;

        foreach ($ids as $id) {
            DB::transaction(function () use ($id, &$count) {
                $transfer = TokenTransfer::query()->lockForUpdate()->find($id);

                if (! $transfer || ! $transfer->isPending() || $transfer->expires_at->isFuture()) {
                    return;
                }

                $transfer->forceFill(['status' => TransferStatus::Expired])->save();

                $order = $transfer->foodToken->order;

                $this->tell(
                    $transfer->sender, $transfer, 'expired', 'Token transfer request expired',
                    'Your request to '.$transfer->receiver->name.' was not answered in time. '.$this->describe($order).' The token is still yours.',
                    'student.transfers.sent'
                );

                $this->tell(
                    $transfer->receiver, $transfer, 'expired', 'Token transfer request expired',
                    'The request from '.$transfer->sender->name.' expired. '.$this->describe($order),
                    'student.transfers.received'
                );

                $count++;
            });
        }

        return $count;
    }

    private function lockAll(int $transferId): array
    {
        $peek = TokenTransfer::query()->with('foodToken')->findOrFail($transferId);

        $order = Order::query()->lockForUpdate()->findOrFail($peek->foodToken->order_id);
        $token = FoodToken::query()->lockForUpdate()->findOrFail($peek->food_token_id);
        $transfer = TokenTransfer::query()->lockForUpdate()->findOrFail($transferId);

        return [$order, $token, $transfer];
    }

    private function assertPending(TokenTransfer $transfer): void
    {
        if (! $transfer->isPending()) {
            throw new TransferException('This request is no longer pending. It is '.strtolower($transfer->status->label()).'.');
        }
    }

    private function describe(Order $order): string
    {
        $order->loadMissing(['items', 'outlet']);

        $items = $order->items->map(fn ($i) => $i->quantity.' x '.$i->item_name)->implode(', ');

        return 'Food: '.$items.' ('.$order->outlet->name.'), order '.$order->order_number
            .', pickup '.$order->pickup_datetime->format('D j M, g:i A').'.';
    }

    private function tell(User $to, TokenTransfer $transfer, string $kind, string $title, string $message, string $route): void
    {
        $to->notify(new TransferNotification(
            $transfer->id,
            $kind,
            $title,
            $message,
            route($route, [], false)
        ));
    }
}