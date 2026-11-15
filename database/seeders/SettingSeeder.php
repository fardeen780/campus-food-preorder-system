<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $descriptions = [
            'transfer_expiry_hours' => 'Hours a token transfer request stays open (never longer than pickup time)',
            'cafeteria_open_time' => 'First pickup slot of the day (24-hour HH:MM)',
            'cafeteria_close_time' => 'Last pickup time of the day (24-hour HH:MM)',
            'pickup_slot_minutes' => 'Length of each pickup time slot in minutes',
        ];

        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'description' => $descriptions[$key]],
            );
        }

        Cache::forget('settings.all');
    }
}