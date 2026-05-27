<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_kit_webhook_events', static function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->string('event_type')->index();
            $table->string('provider_event_id');
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->string('status')->default('received')->index();
            $table->text('failure_reason')->nullable();
            $table->timestamp('received_at')->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_kit_webhook_events');
    }
};
