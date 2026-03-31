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
        Schema::table('b2b_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->default(0)->after('b2b_purchase_id');
            $table->unsignedBigInteger('product_id')->default(0)->after('organization_id');
            $table->unsignedBigInteger('rate_id')->default(0)->after('product_id');
            $table->tinyInteger('status')->default(0)->after('rate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_transactions', function (Blueprint $table) {
            $table->dropColumn('organization_id');
            $table->dropColumn('product_id');
            $table->dropColumn('rate_id');
            $table->dropColumn('status');
        });
    }
};
