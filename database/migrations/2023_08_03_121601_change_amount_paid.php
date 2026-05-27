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
        Schema::table('b2b_subscriptions', function (Blueprint $table) {
            $table->double('amount_paid')->nullable()->change();
            $table->double('amount')->nullable()->change();
        });
        Schema::table('b2b_transactions', function (Blueprint $table) {
            $table->double('amount_paid')->nullable()->change();
            $table->double('amount')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_subscriptions', function (Blueprint $table) {
            //
        });
    }
};
