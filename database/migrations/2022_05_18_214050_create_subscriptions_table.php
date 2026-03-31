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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('subscription_group_id');
            $table->timestamp('subscription_date')->nullable();
            $table->bigInteger('reccurent_cycle')->default(0);
            $table->unsignedBigInteger('rate_id');
            $table->tinyInteger('reccuring')->default(0);
            $table->timestamp('expiry_date')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('subscription_group_id')
                    ->references('id')
                    ->on('subscription_groups')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

            $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            $table->foreign('rate_id')
                    ->references('id')
                    ->on('rates')
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
            Schema::dropIfExists('subscriptions');
        }
};
