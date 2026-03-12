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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->index()->nullable();
            $table->string('address')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('debt_amount', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('service_id')->constrained('clients')->nullOnDelete();
        });

        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->date('deadline')->nullable();
            $table->string('type')->default('one-time'); // one-time, installment
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, partially_paid, paid
            $table->timestamps();
        });

        Schema::create('debt_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained('debts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->string('status')->default('pending'); // pending, paid
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_installments');
        Schema::dropIfExists('debts');
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
        Schema::dropIfExists('clients');
    }
};
