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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('kra_pin')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->text('additional_info')->nullable();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->CascadeOnDelete()
                  ->CascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizations');
    }
};
