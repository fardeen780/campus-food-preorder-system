<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FoodItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'category_id',
        'name',
        'description',
        'price',
        'icon',
        'image_path',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FoodCategory::class, 'category_id');
    }

    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_available', true);
    }

    public function isOrderable(): bool
    {
        return ! $this->trashed() && $this->is_available && $this->outlet?->is_active;
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            $candidates = [];

            if ($this->image_path && ! str_contains($this->image_path, '..')) {
                $candidates[] = ltrim($this->image_path, '/');
            }

            $slug = Str::slug($this->name);

            foreach (['webp', 'jpg', 'jpeg', 'png'] as $extension) {
                $candidates[] = 'images/food/'.$slug.'.'.$extension;
            }

            foreach ($candidates as $path) {
                if (is_file(public_path($path))) {
                    return asset($path);
                }
            }

            return asset('images/food-fallback.svg');
        });
    }
}