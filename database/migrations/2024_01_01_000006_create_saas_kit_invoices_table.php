<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_kit_invoices', static function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->string('customer_type');
            $table->string('customer_id');
            $table->foreignId('subscription_id')->nullable()->constrained('saas_kit_subscriptions')->nullOnDelete();
            $table->string('currency', 3)->default('usd');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('discount_total')->default(0);
            $table->unsignedInteger('tax_total')->default(0);
            $table->unsignedInteger('total');
            $table->json('lines');
            $table->string('status')->default('open')->index();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('provider_payment_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['customer_type', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_kit_invoices');
    }
};
