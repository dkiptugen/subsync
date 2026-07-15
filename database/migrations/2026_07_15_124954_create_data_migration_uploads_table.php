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
        Schema::create('data_migration_uploads', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('type')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('disk')->default('s3');
            $table->string('path');
            $table->string('original_name');
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('progress')->default(0);
            $table->unsignedInteger('processed_files')->default(0);
            $table->unsignedInteger('total_files')->default(1);
            $table->text('message')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_migration_uploads');
    }
};
