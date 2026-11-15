<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->string('token_code', 12)->unique();
            $table->foreignId('current_owner_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('current_owner_id');
        });

        DB::statement('ALTER TABLE food_tokens ADD CONSTRAINT food_tokens_code_min_length CHECK (char_length(token_code) >= 6)');
    }

    public function down(): void
    {
        Schema::dropIfExists('food_tokens');
    }
};