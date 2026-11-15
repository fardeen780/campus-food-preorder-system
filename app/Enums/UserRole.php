<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Staff = 'staff';
    case Manager = 'manager';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Student',
            self::Staff => 'Cafeteria Staff',
            self::Manager => 'Manager',
            self::Admin => 'System Administrator',
        };
    }
}