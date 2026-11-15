<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_token_id')->constrained('food_tokens')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['receiver_id', 'status']);
            $table->index(['sender_id', 'status']);
            $table->index('food_token_id');
        });

        DB::statement("ALTER TABLE token_transfers ADD CONSTRAINT token_transfers_status_check CHECK (status IN ('pending', 'accepted', 'rejected', 'expired', 'cancelled'))");

        DB::statement('ALTER TABLE token_transfers ADD CONSTRAINT token_transfers_sender_not_receiver CHECK (sender_id <> receiver_id)');

        DB::statement("ALTER TABLE token_transfers ADD CONSTRAINT token_transfers_responded_consistent CHECK ((status IN ('accepted', 'rejected', 'cancelled')) = (responded_at IS NOT NULL))");

        DB::statement('ALTER TABLE token_transfers ADD CONSTRAINT token_transfers_expiry_after_request CHECK (expires_at > requested_at)');

        DB::statement("CREATE UNIQUE INDEX token_transfers_one_pending_per_token ON token_transfers (food_token_id) WHERE status = 'pending'");

        DB::statement("CREATE INDEX token_transfers_pending_expiry ON token_transfers (expires_at) WHERE status = 'pending'");
    }

    public function down(): void
    {
        Schema::dropIfExists('token_transfers');
    }
};