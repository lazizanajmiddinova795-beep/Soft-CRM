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
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('amount');
            $table->decimal('operator_share_percentage', 5, 2)->default(0)->after('cost_price');
            $table->string('custom_type')->nullable()->after('operator_share_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'operator_share_percentage', 'custom_type']);
        });
    }
};
