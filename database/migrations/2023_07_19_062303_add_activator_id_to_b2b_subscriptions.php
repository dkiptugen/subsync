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
            $table->unsignedBigInteger('activator_id')->default(0)->after('status');
            $table->text('activator_reason')->nullable()->after('activator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('b2b_subscriptions', function (Blueprint $table) {
            $table->dropColumn('activator_id');
            $table->dropColumn('activator_reason');
        });
    }
};
