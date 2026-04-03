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
        DB::statement('
            ALTER TABLE cart_items
            ALTER COLUMN release_id TYPE bigint
            USING release_id::bigint
        ');
        /*Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('release_id')->change();
        });*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
