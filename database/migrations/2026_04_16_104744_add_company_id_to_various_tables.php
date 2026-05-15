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
        $tables = ['transactions', 'tasks', 'messages', 'shifts', 'contracts', 'services', 'clients', 'debts', 'debt_installments'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'company_id')) {
                        $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
                    }
                });
            }
        }

        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'services_json')) {
                $table->json('services_json')->nullable()->after('amount');
            }
        });
    }

    public function down(): void
    {
        $tables = ['transactions', 'tasks', 'messages', 'shifts', 'contracts', 'services', 'clients', 'debts', 'debt_installments'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'company_id')) {
                    $t->dropColumn('company_id');
                }
            });
        }
        Schema::table('contracts', function (Blueprint $t) {
            if (Schema::hasColumn('contracts', 'services_json')) {
                $t->dropColumn('services_json');
            }
        });
    }
};
