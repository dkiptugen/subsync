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
            $table->string('currency')->nullable()->after('status');
            $table->string('reserved_currency')->default('USD')->after('currency');
            $table->string('reserved_currency_amount')->nullable()->after('reserved_currency');
            $table->double('amount')->nullable()->after('reserved_currency_amount');
            $table->unsignedBigInteger('region_id')->default('0')->after('status');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_transactions', function (Blueprint $table) {
           $table->dropColumn('currency');
           $table->dropColumn('currency');
           $table->dropColumn('reserved_currency');
           $table->dropColumn('reserved_currency_amount');
           $table->dropColumn('amount');
           $table->dropColumn('region_id');
        });
    }
};
