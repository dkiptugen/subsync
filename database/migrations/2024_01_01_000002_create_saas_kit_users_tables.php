<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_kit_users', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('saas_kit_tenants')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('status')->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('saas_kit_user_profiles', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('saas_kit_users')->cascadeOnDelete();
            $table->string('display_name')->nullable();
            $table->string('timezone')->nullable();
            $table->string('locale', 16)->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_kit_user_profiles');
        Schema::dropIfExists('saas_kit_users');
    }
};
