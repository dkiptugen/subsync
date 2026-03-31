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
            $table->unsignedBigInteger('rate_type_id')->default(0)->after('subscription_type');
            $table->string('channel')->nullable()->after('rate_type_id');
            $table->string('receipt')->nullable()->after('channel');
            $table->double('amount')->default(0)->after('receipt');
            $table->double('amount_paid')->default(0)->after('amount');
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
