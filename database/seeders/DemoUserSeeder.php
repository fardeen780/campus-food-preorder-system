<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = (string) config('app.demo_password');

        if (app()->isProduction() && ($password === 'password' || strlen($password) < 10)) {
            throw new \RuntimeException('Set a strong DEMO_PASSWORD (at least 10 characters) before seeding in production.');
        }

        $outlets = [
            ['JMI', 'Rice, curry and main meals', '🍛'],
            ['Salad Bar', 'Fresh salads and bowls', '🥗'],
            ['CP Fried Chicken', 'Fried chicken and burgers', '🍗'],
            ['Tea Stall', 'Tea, coffee and snacks', '☕'],
            ['Juice Bar', 'Fresh juices and shakes', '🥤'],
        ];

        foreach ($outlets as [$name, $description, $icon]) {
            Outlet::firstOrCreate(['name' => $name], ['description' => $description, 'icon' => $icon]);
        }

        $jmi = Outlet::where('name', 'JMI')->first();

        $accounts = [
            ['System Admin', 'admin@iub.test', UserRole::Admin, null, null],
            ['Cafeteria Manager', 'manager@iub.test', UserRole::Manager, null, null],
            ['JMI Staff', 'staff@iub.test', UserRole::Staff, null, $jmi->id],
            ['Student One', 'student1@iub.test', UserRole::Student, '2210001', null],
            ['Student Two', 'student2@iub.test', UserRole::Student, '2210002', null],
        ];

        foreach ($accounts as [$name, $email, $role, $studentId, $outletId]) {
            User::firstOrNew(['email' => $email])->forceFill([
                'name' => $name,
                'password' => $password,
                'role' => $role,
                'student_id' => $studentId,
                'phone' => '01700000000',
                'outlet_id' => $outletId,
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();
        }
    }
}