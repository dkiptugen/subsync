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
        Schema::table('transactions', function (Blueprint $table) {
            try{
                $table->text('redirect_url')->change();
                $table->text('back_url')->change();
            }
            catch (\Exception $exception){
                Log::error($exception->getMessage());
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('redirect_url', 255)->change();
            $table->string('back_url', 255)->change();
        });
    }
};
