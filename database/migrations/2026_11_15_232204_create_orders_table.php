<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SEQUENCE order_number_seq START 1001');

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)
                ->unique()
                ->default(DB::raw("('ORD-' || nextval('order_number_seq'))"));
            $table->foreignId('original_student_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->timestamp('pickup_datetime');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('order_status', 20)->default('pending');
            $table->string('payment_status', 20)->default('unpaid');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['original_student_id', 'created_at']);
            $table->index(['outlet_id', 'order_status', 'pickup_datetime']);
            $table->index('payment_status');
        });

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_order_status_check CHECK (order_status IN ('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'))");

        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_amounts_valid CHECK (total_amount > 0 AND paid_amount >= 0 AND paid_amount <= total_amount)');

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_status_consistent CHECK (
            (payment_status = 'unpaid' AND paid_amount = 0)
            OR (payment_status = 'partially_paid' AND paid_amount > 0 AND paid_amount < total_amount)
            OR (payment_status = 'paid' AND paid_amount = total_amount)
            OR (payment_status = 'due' AND paid_amount < total_amount)
        )");

        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_pickup_after_created CHECK (pickup_datetime > created_at)');

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_timestamps_consistent CHECK (
            ((order_status = 'cancelled') = (cancelled_at IS NOT NULL))
            AND ((order_status = 'completed') = (completed_at IS NOT NULL))
        )");
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        DB::statement('DROP SEQUENCE IF EXISTS order_number_seq');
    }
};