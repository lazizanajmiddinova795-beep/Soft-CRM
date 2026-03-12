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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('status')->default('active'); // active, forced_stop, finished
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->decimal('cost_price', 12, 2);
            $table->decimal('client_price', 12, 2);
            $table->decimal('operator_share_percentage', 5, 2);
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_id')->unique(); // ID with prefix
            $table->foreignId('user_id')->constrained('users')->comment('Operator who created it');
            $table->foreignId('service_id')->constrained('services');
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_address');
            $table->string('file_path')->nullable(); // .pfc file
            $table->string('status')->default('pending'); // pending, processing, approved, rejected, cancelled
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('Who performed this');
            $table->foreignId('contract_id')->nullable()->constrained(); // Null if standalone
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_by')->constrained('users');
            $table->foreignId('assigned_to')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->string('status')->default('pending'); // pending, done, failed
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->integer('xp_reward')->default(0);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('action'); // Model Created, Updated, Deleted, Button Clicked
            $table->nullableMorphs('auditable');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('services');
        Schema::dropIfExists('shifts');
    }
};
