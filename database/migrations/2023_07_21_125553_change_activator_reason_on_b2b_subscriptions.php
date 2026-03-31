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
        Schema::table('b2b_subscriptions', function (Blueprint $table)
            {
                $table->longText('activator_reason')->nullable()->change();
                $table->unsignedBigInteger('activator_id')->default(0)->change();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
        {
            //
        }
};
