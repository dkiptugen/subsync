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
        Schema::create('currency_convertors', function (Blueprint $table) {
            $table->id();
            $table->string('currency');
            $table->double('amount');
            $table->double('dollar_amount');
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('region_id');
            $table->date('startdate');
            $table->date('enddate');
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('region_id')
                ->references('id')
                ->on('regions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency_convertors');
    }
};
