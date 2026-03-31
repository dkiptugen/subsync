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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
	        $table->string('name');
	        $table->text('subject');
	        $table->text('body');
			$table->text('products');
			$table->unsignedBigInteger ('user_id')->index();
			$table->tinyInteger ('email_type');
	        $table->tinyInteger ('status')->default (0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
