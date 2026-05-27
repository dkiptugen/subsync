<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_kit_roles', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key');
            $table->string('guard_name')->default('web');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['key', 'guard_name']);
        });

        Schema::create('saas_kit_permissions', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key');
            $table->string('guard_name')->default('web');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['key', 'guard_name']);
        });

        Schema::create('saas_kit_role_permissions', static function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('saas_kit_roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('saas_kit_permissions')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('saas_kit_model_has_roles', static function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('saas_kit_roles')->cascadeOnDelete();
            $table->string('model_type');
            $table->string('model_id');
            $table->timestamps();
            $table->primary(['role_id', 'model_type', 'model_id']);
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_kit_model_has_roles');
        Schema::dropIfExists('saas_kit_role_permissions');
        Schema::dropIfExists('saas_kit_permissions');
        Schema::dropIfExists('saas_kit_roles');
    }
};
