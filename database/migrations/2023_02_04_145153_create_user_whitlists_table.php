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
        Schema::create('user_whitelists', function (Blueprint $table) {
            $table->id();
            $table->morphs('whitelistable');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->text('reason')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamp('startdate');
            $table->timestamp('enddate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
        {
            Schema::dropIfExists('user_whitelists');
        }
};
