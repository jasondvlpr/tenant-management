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
        Schema::table('tenants', function (Blueprint $table) {
            $table->integer('users_count')->default(0)->after('users');
            $table->integer('transactions_count')->default(0)->after('users_count');
            $table->decimal('first_deposit_amount', 15, 2)->default(0)->after('transactions_count');
            $table->decimal('redeposit_amount', 15, 2)->default(0)->after('first_deposit_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['users_count', 'transactions_count', 'first_deposit_amount', 'redeposit_amount']);
        });
    }
};
