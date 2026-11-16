<?php

namespace Database\Seeders;

use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\Outlet;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Rice & Meals' => 1,
            'Burgers & Chicken' => 2,
            'Salads' => 3,
            'Snacks' => 4,
            'Hot Drinks' => 5,
            'Cold Drinks' => 6,
        ];

        foreach ($categories as $name => $order) {
            FoodCategory::firstOrCreate(['name' => $name], ['sort_order' => $order]);
        }

        $items = [
            ['JMI', 'Rice & Meals', 'Chicken Biryani', 220, '🍛', 'Fragrant rice with tender chicken'],
            ['JMI', 'Rice & Meals', 'Beef Khichuri', 180, '🍲', 'Rich lentil and rice with beef'],
            ['JMI', 'Rice & Meals', 'Egg Fried Rice', 150, '🍳', 'Wok-fried rice with egg and vegetables'],
            ['JMI', 'Rice & Meals', 'Dal and Rice Combo', 120, '🍚', 'Rice, dal, vegetables and a fried egg'],
            ['Salad Bar', 'Salads', 'Chicken Caesar Salad', 200, '🥗', 'Grilled chicken, lettuce and Caesar dressing'],
            ['Salad Bar', 'Salads', 'Garden Fresh Salad', 140, '🥬', 'Seasonal vegetables with lemon dressing'],
            ['Salad Bar', 'Salads', 'Tuna Bowl', 230, '🥣', 'Tuna, corn, cucumber and greens'],
            ['CP Fried Chicken', 'Burgers & Chicken', 'Chicken Burger', 180, '🍔', 'Crispy chicken fillet in a soft bun'],
            ['CP Fried Chicken', 'Burgers & Chicken', 'Fried Chicken 2pc', 220, '🍗', 'Two pieces of crispy fried chicken'],
            ['CP Fried Chicken', 'Burgers & Chicken', 'Chicken Wings 6pc', 200, '🍗', 'Six spicy wings'],
            ['CP Fried Chicken', 'Snacks', 'French Fries', 90, '🍟', 'Golden salted fries'],
            ['Tea Stall', 'Hot Drinks', 'Milk Tea', 30, '☕', 'Classic hot milk tea'],
            ['Tea Stall', 'Hot Drinks', 'Coffee', 60, '☕', 'Fresh hot coffee'],
            ['Tea Stall', 'Snacks', 'Samosa', 20, '🥟', 'Crispy fried samosa'],
            ['Juice Bar', 'Cold Drinks', 'Orange Juice', 90, '🍊', 'Freshly squeezed orange juice'],
            ['Juice Bar', 'Cold Drinks', 'Mango Shake', 120, '🥭', 'Thick mango milkshake'],
            ['Juice Bar', 'Cold Drinks', 'Lemon Mint', 70, '🍋', 'Cooling lemon and mint drink'],
        ];

        foreach ($items as [$outletName, $categoryName, $name, $price, $icon, $description]) {
            $outlet = Outlet::where('name', $outletName)->firstOrFail();
            $category = FoodCategory::where('name', $categoryName)->firstOrFail();

            FoodItem::firstOrCreate(
                ['outlet_id' => $outlet->id, 'name' => $name],
                [
                    'category_id' => $category->id,
                    'price' => $price,
                    'icon' => $icon,
                    'description' => $description,
                ],
            );
        }
    }
}