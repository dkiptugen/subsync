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
                Schema::create('transactions', function (Blueprint $table)
                    {
                        $table->id();

                        $table->string('identifier')->index();
                        $table->unsignedBigInteger('subscription_id');
                        $table->unsignedBigInteger('payment_method_id');
                        //$table->unsignedBigInteger('cart_id')->default(0)->index();
                        $table->string('channel');
                        $table->string('receipt');
                        $table->string('initiator')->comment('user whose account is used to pay');
                        $table->string('coupon_code')->nullable();
                        $table->decimal('discount')->default(0);
                        $table->decimal('total_amount');
                        $table->decimal('amount');
                        $table->tinyInteger('status')->default(0);
                        $table->unsignedBigInteger('user_id');
                        $table->dateTime('transaction_date')->nullable();
                        $table->decimal('amount_paid')->default(0.00)->nullable();
                        $table->enum('type', ['initial', 'recurrent'])->default('initial');
                        $table->longText('response')->nullable();
                        $table->string('transaction_code')->nullable();
                        $table->timestamps();
                        $table->foreign('payment_method_id')
                              ->references('id')
                              ->on('payment_methods')
                              ->cascadeOnDelete()
                              ->cascadeOnUpdate();
                        $table->foreign('subscription_id')
                              ->references('id')
                              ->on('subscriptions')
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
                Schema::dropIfExists('transactions');
            }
    };
