<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cart_items ALTER COLUMN release_id TYPE bigint USING NULLIF(release_id, \'\')::bigint');
        } else {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->unsignedBigInteger('release_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cart_items ALTER COLUMN release_id TYPE varchar(255) USING release_id::varchar');
        } else {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->string('release_id')->nullable()->change();
            });
        }
    }
};
