<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up(): void
            {

                Schema::create('sites', function (Blueprint $table)
                    {

                        $table->id();
                        $table->string('site_name');
                        $table->string('site_url');
                        $table->unsignedBigInteger('region_id');
                        $table->timestamps();
                        $table->foreign('region_id')->references('id')->on('regions')->cascadeOnDelete()->cascadeOnUpdate();
                    });
            }

        /**
         * Reverse the migrations.
         */
        public function down() : void
            {

                Schema::dropIfExists('sites');
            }
    };
