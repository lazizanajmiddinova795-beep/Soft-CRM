<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('internal_id')->unique()->nullable(); // generated id for hr
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('operator'); // admin, operator, cashier, developer
            $table->string('face_id_token')->nullable(); // For biometric auth simulation
            $table->string('avatar')->nullable();
            $table->string('allowed_ip')->nullable(); // Geofencing IP lock
            // Payroll & Stats
            $table->decimal('salary', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->decimal('extra_percentage', 5, 2)->default(0); // 0.00 to 100.00
            $table->decimal('balance', 12, 2)->default(0); // Employee personal balance
            $table->integer('xp')->default(0); // Leveling up syndicate
            $table->string('status')->default('offline'); // online, offline, idle, active
            $table->timestamp('last_heartbeat')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
