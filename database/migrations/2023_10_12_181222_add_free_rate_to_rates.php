<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
        {
        /**
         * Run the migrations.
         */
            public function up()
            : void
                {
                    Schema::table('rates', function (Blueprint $table)
                        {
                            $table->unsignedBigInteger('free_rate_id')->default(0);
                            $table->dateTime('free_rate_end_date')->nullable();
                        });
                }

        /**
         * Reverse the migrations.
         */
            public function down()
            : void
                {
                    Schema::table('rates', function (Blueprint $table)
                        {
                            $table->dropColumn(['free_rate_id', 'free_rate_end_date']);
                        });
                }
        };
