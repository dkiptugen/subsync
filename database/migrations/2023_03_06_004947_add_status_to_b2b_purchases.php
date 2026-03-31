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
        Schema::table('b2b_purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('cc_approver_id') ->after("user_id");
            $table->unsignedBigInteger('finance_approver_id') ->after("cc_approver_id");
            $table->tinyInteger('status')->default(0) ->after("finance_approver_id");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b2b_purchases', function (Blueprint $table) {
            //
        });
    }
};
