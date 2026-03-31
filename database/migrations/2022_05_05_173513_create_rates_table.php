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
        Schema::create('rates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('rate_type_id');
            $table->decimal('cost');
            $table->string('currency');
            $table->unsignedBigInteger('region_id');
            $table->tinyInteger('status')->default(0);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->enum('type',['individual','corporate']);
            $table->unsignedBigInteger('organization_id')->default(0);
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
            $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            $table->foreign('rate_type_id')
                ->references('id')
                ->on('rate_types')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('rates');
    }
};
