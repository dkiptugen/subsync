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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('transaction_date');
            $table->index('status');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('subscription_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['transaction_date', 'status']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['subscription_date', 'status']);
        });
    }
};
