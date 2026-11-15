<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('food_categories')->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('icon', 16)->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_available')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['outlet_id', 'is_available']);
            $table->index('category_id');
        });

        DB::statement('ALTER TABLE food_items ADD CONSTRAINT food_items_price_positive CHECK (price > 0)');
        DB::statement('CREATE UNIQUE INDEX food_items_outlet_name_unique ON food_items (outlet_id, lower(name)) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};