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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->index('identifier');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->index('identifier');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('identifier');
            $table->index('cart_id');
            $table->index('expiry_date');
        });

        Schema::table('subscription_groups', function (Blueprint $table) {
            $table->index('subdate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropIndex(['identifier']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['identifier']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['cart_id']);
            $table->dropIndex(['identifier']);
            $table->dropIndex(['subscription_date']);
            $table->dropIndex(['expiry_date']);
        });

        Schema::table('subscription_groups', function (Blueprint $table) {
            $table->dropIndex(['subdate']);
        });
    }
};
