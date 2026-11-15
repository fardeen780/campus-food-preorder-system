<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('method', 20);
            $table->string('status', 20)->default('successful');
            $table->string('reference', 100)->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('recorded_by');
        });

        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_positive CHECK (amount > 0)');

        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_method_check CHECK (method IN ('cash', 'bkash', 'nagad'))");

        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('successful', 'failed', 'refunded'))");

        DB::statement('CREATE UNIQUE INDEX payments_method_reference_unique ON payments (method, reference) WHERE reference IS NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};