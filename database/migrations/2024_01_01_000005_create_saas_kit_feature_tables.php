<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_kit_features', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('saas_kit_plan_features', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plan_id')->constrained('saas_kit_plans')->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained('saas_kit_features')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('limit')->nullable();
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['plan_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_kit_plan_features');
        Schema::dropIfExists('saas_kit_features');
    }
};
