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
                Schema::table('products', function (Blueprint $table)
                    {
                        $table->tinyInteger('is_bundled')->after('product_link')->default(0);
                        $table->unsignedBigInteger('parent_id')->after('is_bundled')->default(0);
                        $table->dropColumn('pass_reset_link');
                    });
            }

    /**
     * Reverse the migrations.
     */
        public function down(): void
            {
                Schema::table('products', function (Blueprint $table)
                    {
                        $table->dropColumn('is_bundled');
                        $table->dropColumn('parent_id');
                    });
            }
    };
