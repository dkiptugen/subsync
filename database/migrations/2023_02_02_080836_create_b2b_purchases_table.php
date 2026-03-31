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
        Schema::create('b2b_purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifier');
            $table->unsignedBigInteger('organization_id');
            $table->decimal('full_amount');
            $table->decimal('balance')->nullable();
            $table->tinyInteger('is_paid')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->text('products')->nullable();
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
        Schema::dropIfExists('b2b_purchases');
    }
};
