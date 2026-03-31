<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
    {
    /**
     * Run the migrations.
     *
     * @return void
     */
        public function up()
            {
                Schema::table('products', function (Blueprint $table)
                    {
                        $table->enum('type', ['paywall', 'epaper', 'bundle'])->default('epaper')->after('product_link');
                        //$table->longText('product_ids')->nullable()->;
                    });
            }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
        public function down()
            {
                Schema::table('products', function (Blueprint $table)
                    {
                        $table->dropColumn('type');
                        //$table->dropColumn('product_ids');
                    });
            }
    };
