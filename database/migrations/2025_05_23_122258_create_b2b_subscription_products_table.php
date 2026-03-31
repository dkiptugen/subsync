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
        Schema::create('b2b_subscription_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('b2b_subscription_id');
            $table->unsignedBigInteger('product_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_subcription_products');
    }
};
