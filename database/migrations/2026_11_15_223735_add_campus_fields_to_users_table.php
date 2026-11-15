<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('student');
            $table->string('student_id', 20)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->boolean('is_active')->default(true);
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('student', 'staff', 'manager', 'admin'))");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_student_needs_student_id CHECK (role <> 'student' OR student_id IS NOT NULL)");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_student_needs_student_id');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outlet_id');
            $table->dropColumn(['role', 'student_id', 'phone', 'is_active']);
        });
    }
};