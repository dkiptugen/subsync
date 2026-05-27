<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_kit_plans', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->string('interval')->index();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('usd');
            $table->unsignedInteger('trial_days')->nullable();
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('saas_kit_coupons', static function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('type');
            $table->unsignedInteger('value');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redemptions')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('saas_kit_subscriptions', static function (Blueprint $table): void {
            $table->id();
            $table->string('billable_type');
            $table->string('billable_id');
            $table->foreignId('plan_id')->constrained('saas_kit_plans')->restrictOnDelete();
            $table->string('status')->default('active')->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_subscription_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['billable_type', 'billable_id']);
        });

        Schema::create('saas_kit_usage_records', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')->constrained('saas_kit_subscriptions')->cascadeOnDelete();
            $table->string('feature_key');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['subscription_id', 'feature_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_kit_usage_records');
        Schema::dropIfExists('saas_kit_subscriptions');
        Schema::dropIfExists('saas_kit_coupons');
        Schema::dropIfExists('saas_kit_plans');
    }
};
