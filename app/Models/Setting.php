<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const DEFAULTS = [
        'transfer_expiry_hours' => '24',
        'cafeteria_open_time' => '08:00',
        'cafeteria_close_time' => '18:00',
        'pickup_slot_minutes' => '15',
    ];

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $stored = Cache::rememberForever(
            'settings.all',
            fn () => static::query()->pluck('value', 'key')->all()
        );

        return $stored[$key] ?? self::DEFAULTS[$key] ?? $default;
    }

    public static function integer(string $key): int
    {
        return (int) static::get($key);
    }

    public static function put(string $key, string $value, ?User $by = null): void
    {
        $setting = static::query()->firstOrNew(['key' => $key]);
        $setting->forceFill([
            'value' => $value,
            'updated_by' => $by?->id,
        ])->save();

        Cache::forget('settings.all');
    }
}