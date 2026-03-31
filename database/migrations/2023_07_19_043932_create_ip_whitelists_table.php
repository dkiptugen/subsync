<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ip_whitelists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->text('ip_address');
            $table->bigInteger('concurrent_connections');
            $table->timestamp('startdate');
            $table->timestamp('enddate');
            $table->text('reason');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_whitelists');
    }
};
