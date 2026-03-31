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
        Schema::table('user_whitelists', function (Blueprint $table) {
            $table->string('tag')->nullable()->after('reason')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_whitelists', function (Blueprint $table) {
            $table->dropColumn('tag');
        });
    }
};
