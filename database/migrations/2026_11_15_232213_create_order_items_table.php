<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('food_item_id')->constrained('food_items')->restrictOnDelete();
            $table->string('item_name', 150);
            $table->unsignedSmallInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->unique(['order_id', 'food_item_id']);
            $table->index('food_item_id');
        });

        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_quantity_range CHECK (quantity BETWEEN 1 AND 20)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_unit_price_positive CHECK (unit_price > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};