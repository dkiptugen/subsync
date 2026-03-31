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
        Schema::table('autorenewals', function (Blueprint $table) {
            $table->dateTime('start_date');
            $table->dateTime('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('autorenewals', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('expiry_date');
        });
    }
};
