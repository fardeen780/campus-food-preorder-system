<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('description', 255)->nullable();
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->jsonb('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });

        DB::statement('ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_action_format CHECK (action ~ \'^[a-z0-9_.]+$\')');
        DB::statement('ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_subject_pair CHECK ((subject_type IS NULL) = (subject_id IS NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};