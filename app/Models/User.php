<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'password', 'student_id', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'role' => 'student',
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
        public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
        public function placedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'original_student_id');
    }

    public function heldTokens(): HasMany
    {
        return $this->hasMany(FoodToken::class, 'current_owner_id');
    }
        public function sentTransfers(): HasMany
    {
        return $this->hasMany(TokenTransfer::class, 'sender_id');
    }

    public function receivedTransfers(): HasMany
    {
        return $this->hasMany(TokenTransfer::class, 'receiver_id');
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(UserRole::Student);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(UserRole::Staff);
    }

    public function isManager(): bool
    {
        return $this->hasRole(UserRole::Manager);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }
}