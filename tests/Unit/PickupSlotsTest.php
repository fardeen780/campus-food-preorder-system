<?php

namespace Tests\Unit;

use App\Support\PickupSlots;
use Tests\TestCase;

class PickupSlotsTest extends TestCase
{
    public function test_it_parses_a_pickup_date_and_time(): void
    {
        $result = PickupSlots::parse('2026-11-23 10:30');

        $this->assertSame('2026-11-23', $result->format('Y-m-d'));
        $this->assertSame('10:30', $result->format('H:i'));
    }

    public function test_it_parses_another_pickup_time(): void
    {
        $result = PickupSlots::parse('2026-11-23 14:45');

        $this->assertSame('2026-11-23', $result->format('Y-m-d'));
        $this->assertSame('14:45', $result->format('H:i'));
    }
}
