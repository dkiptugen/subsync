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
        Schema::create('b2b_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('identifier')->index();
            $table->longText('b2b_subscription_id');
            $table->unsignedBigInteger('b2b_purchase_id')->default(0)->index();
            $table->decimal('amount_paid')->default(0);
            $table->string('receipt')->nullable();
            $table->string('pay_channel')->nullable();
            $table->timestamp('date_paid');
            $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('b2b_transactions');
    }
};
