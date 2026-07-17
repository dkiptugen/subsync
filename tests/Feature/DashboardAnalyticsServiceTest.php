<?php

namespace Tests\Feature;

use App\Services\DashboardAnalyticsService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardAnalyticsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('transactions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->unsignedTinyInteger('status');
            $table->dateTime('transaction_date')->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0);
        });

        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->dateTime('subscription_date')->nullable();
            $table->date('unsubscription_date')->nullable();
            $table->boolean('reccuring')->default(false);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('transactions');

        parent::tearDown();
    }

    public function test_it_returns_all_time_cumulative_paid_revenue_for_the_latest_twelve_months(): void
    {
        DB::table('transactions')->insert([
            ['id' => '01J00000000000000000000001', 'status' => 1, 'transaction_date' => '2025-07-15 12:00:00', 'amount_paid' => 100],
            ['id' => '01J00000000000000000000002', 'status' => 2, 'transaction_date' => '2025-07-20 12:00:00', 'amount_paid' => 500],
            ['id' => '01J00000000000000000000003', 'status' => 1, 'transaction_date' => '2025-08-10 12:00:00', 'amount_paid' => 50],
            ['id' => '01J00000000000000000000004', 'status' => 1, 'transaction_date' => '2026-06-10 12:00:00', 'amount_paid' => 25],
            ['id' => '01J00000000000000000000005', 'status' => 1, 'transaction_date' => '2026-07-10 12:00:00', 'amount_paid' => 75],
            ['id' => '01J00000000000000000000006', 'status' => 1, 'transaction_date' => '2026-07-20 12:00:00', 'amount_paid' => 900],
        ]);

        $analytics = app(DashboardAnalyticsService::class)->get(CarbonImmutable::parse('2026-07-17 12:00:00'));
        $chart = $analytics['cumulativeRevenue'];

        $this->assertCount(12, $chart['labels']);
        $this->assertSame('Aug 2025', $chart['labels'][0]);
        $this->assertSame('Jul 2026', $chart['labels'][11]);
        $this->assertSame(150.0, $chart['values'][0]);
        $this->assertSame(175.0, $chart['values'][10]);
        $this->assertSame(250.0, $chart['current']);
    }

    public function test_it_calculates_monthly_churn_from_recurring_subscriptions_active_at_month_start(): void
    {
        DB::table('subscriptions')->insert([
            ['id' => '01J00000000000000000000001', 'subscription_date' => '2025-01-01 12:00:00', 'unsubscription_date' => null, 'reccuring' => true],
            ['id' => '01J00000000000000000000002', 'subscription_date' => '2025-02-01 12:00:00', 'unsubscription_date' => '2025-08-10', 'reccuring' => true],
            ['id' => '01J00000000000000000000003', 'subscription_date' => '2025-03-01 12:00:00', 'unsubscription_date' => '2026-07-01', 'reccuring' => true],
            ['id' => '01J00000000000000000000004', 'subscription_date' => '2025-08-05 12:00:00', 'unsubscription_date' => '2026-07-10', 'reccuring' => true],
            ['id' => '01J00000000000000000000005', 'subscription_date' => '2025-01-01 12:00:00', 'unsubscription_date' => '2025-08-15', 'reccuring' => false],
        ]);

        $analytics = app(DashboardAnalyticsService::class)->get(CarbonImmutable::parse('2026-07-17 12:00:00'));
        $chart = $analytics['churnRate'];

        $this->assertCount(12, $chart['labels']);
        $this->assertSame('Aug 2025', $chart['labels'][0]);
        $this->assertSame(33.33, $chart['values'][0]);
        $this->assertSame(0.0, $chart['values'][1]);
        $this->assertSame(66.67, $chart['current']);
    }
}
