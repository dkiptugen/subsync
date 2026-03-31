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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('rate_id');
            $table->string('product');
            $table->decimal('cost');
            $table->string('currency');
            $table->longText('thumbnail');
            $table->string('rate_type');
            $table->timestamps();
            $table->foreign('cart_id')
                  ->references('id')
                  ->on('carts')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreign('rate_id')
                ->references('id')
                ->on('rates')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
};
