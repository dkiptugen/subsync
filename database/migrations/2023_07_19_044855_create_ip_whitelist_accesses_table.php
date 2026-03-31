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
        Schema::create('ip_whitelist_accesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ip_whitelist_id');
            $table->string('session_identifier');
            $table->text('user_agent');
            $table->timestamp('access_time');
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_whitelist_accesses');
    }
};
