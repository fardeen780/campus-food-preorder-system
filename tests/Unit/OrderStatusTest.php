<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public static function statusProvider(): array
    {
        return [
            'pending' => [OrderStatus::Pending, 'Pending', false, true],
            'confirmed' => [OrderStatus::Confirmed, 'Confirmed', false, true],
            'preparing' => [OrderStatus::Preparing, 'Preparing', false, true],
            'ready' => [OrderStatus::Ready, 'Ready for pickup', false, true],
            'completed' => [OrderStatus::Completed, 'Completed', true, false],
            'cancelled' => [OrderStatus::Cancelled, 'Cancelled', true, false],
        ];
    }

    #[DataProvider('statusProvider')]
    public function test_status_properties(
        OrderStatus $status,
        string $expectedLabel,
        bool $expectedFinal,
        bool $expectedActive
    ): void {
        $this->assertSame($expectedLabel, $status->label());
        $this->assertSame($expectedFinal, $status->isFinal());
        $this->assertSame($expectedActive, $status->isActive());
    }
}
