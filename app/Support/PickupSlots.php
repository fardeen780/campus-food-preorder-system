<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class PickupSlots
{
    public const MIN_LEAD_MINUTES = 15;

    public const DAYS_AHEAD = 1;

    public static function grouped(?CarbonInterface $now = null): array
    {
        $result = [];

        foreach (self::build($now) as $value => [$dayLabel, $timeLabel]) {
            $result[$dayLabel][$value] = $timeLabel;
        }

        return $result;
    }

    public static function isValid(string $value, ?CarbonInterface $now = null): bool
    {
        return array_key_exists($value, self::build($now));
    }

    public static function parse(string $value): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d H:i', $value, config('app.timezone'));
    }

    private static function build(?CarbonInterface $now): array
    {
        $now = CarbonImmutable::instance($now ?? now())->setTimezone(config('app.timezone'));

        $open = Setting::get('cafeteria_open_time');
        $close = Setting::get('cafeteria_close_time');
        $step = max(5, Setting::integer('pickup_slot_minutes'));
        $earliest = $now->addMinutes(self::MIN_LEAD_MINUTES);

        $slots = [];

        for ($d = 0; $d <= self::DAYS_AHEAD; $d++) {
            $day = $now->startOfDay()->addDays($d);
            $time = $day->setTimeFromTimeString($open);
            $end = $day->setTimeFromTimeString($close);

            while ($time->lessThanOrEqualTo($end)) {
                if ($time->greaterThanOrEqualTo($earliest)) {
                    $slots[$time->format('Y-m-d H:i')] = [
                        $d === 0 ? 'Today' : 'Tomorrow',
                        $time->format('g:i A'),
                    ];
                }

                $time = $time->addMinutes($step);
            }
        }

        return $slots;
    }
}