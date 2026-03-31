<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends  Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b2b_purchase_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('b2b_purchase_id');
            $table->unsignedBigInteger('rate_id');
            $table->unsignedBigInteger('product_id');
            $table->bigInteger('accounts');
            $table->decimal('cost');
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
        Schema::dropIfExists('b2b_purchase_details');
    }
};
