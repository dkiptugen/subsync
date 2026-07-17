<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    private const MONTH_COUNT = 12;

    /**
     * @return array{
     *     cumulativeRevenue: array{labels: array<int, string>, values: array<int, float>, current: float},
     *     churnRate: array{labels: array<int, string>, values: array<int, float>, current: float}
     * }
     */
    public function get(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $months = $this->monthsEndingAt($now);
        $periodStart = $months[0]->startOfMonth();

        $cumulativeRevenue = $this->cumulativeRevenue($months, $periodStart, $now);
        $churnRate = $this->churnRate($months, $periodStart, $now);

        return [
            'cumulativeRevenue' => $this->chartData($months, $cumulativeRevenue),
            'churnRate' => $this->chartData($months, $churnRate),
        ];
    }

    /**
     * @param  array<int, CarbonImmutable>  $months
     * @return array<int, float>
     */
    private function cumulativeRevenue(array $months, CarbonImmutable $periodStart, CarbonImmutable $now): array
    {
        $runningTotal = (float) Transaction::query()
            ->where('status', 1)
            ->whereNotNull('transaction_date')
            ->where('transaction_date', '<', $periodStart)
            ->sum('amount_paid');

        $monthlyRevenue = $this->monthlyAggregate(
            Transaction::query()
                ->where('status', 1)
                ->whereBetween('transaction_date', [$periodStart, $now])
                ->toBase(),
            'transaction_date',
            'SUM(amount_paid)'
        );

        return array_map(function (CarbonImmutable $month) use (&$runningTotal, $monthlyRevenue): float {
            $runningTotal += $monthlyRevenue[$month->format('Y-m')] ?? 0.0;

            return round($runningTotal, 2);
        }, $months);
    }

    /**
     * @param  array<int, CarbonImmutable>  $months
     * @return array<int, float>
     */
    private function churnRate(array $months, CarbonImmutable $periodStart, CarbonImmutable $now): array
    {
        $activeAtMonthStart = Subscription::query()
            ->where('reccuring', 1)
            ->whereNotNull('subscription_date')
            ->where('subscription_date', '<', $periodStart)
            ->where(static function ($query) use ($periodStart): void {
                $query->whereNull('unsubscription_date')
                    ->orWhere('unsubscription_date', '>=', $periodStart->toDateString());
            })
            ->count();

        $newSubscriptions = $this->monthlyAggregate(
            Subscription::query()
                ->where('reccuring', 1)
                ->whereBetween('subscription_date', [$periodStart, $now])
                ->toBase(),
            'subscription_date',
            'COUNT(*)'
        );

        $churnedSubscriptions = $this->monthlyAggregate(
            Subscription::query()
                ->where('reccuring', 1)
                ->whereBetween('unsubscription_date', [$periodStart->toDateString(), $now->toDateString()])
                ->toBase(),
            'unsubscription_date',
            'COUNT(*)'
        );

        return array_map(function (CarbonImmutable $month) use (&$activeAtMonthStart, $newSubscriptions, $churnedSubscriptions): float {
            $monthKey = $month->format('Y-m');
            $churned = (int) ($churnedSubscriptions[$monthKey] ?? 0);
            $rate = $activeAtMonthStart > 0
                ? round(($churned / $activeAtMonthStart) * 100, 2)
                : 0.0;

            $activeAtMonthStart = max(
                0,
                $activeAtMonthStart + (int) ($newSubscriptions[$monthKey] ?? 0) - $churned
            );

            return $rate;
        }, $months);
    }

    /**
     * @return array<string, float>
     */
    private function monthlyAggregate(Builder $query, string $dateColumn, string $aggregate): array
    {
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$dateColumn})"
            : "TO_CHAR({$dateColumn}, 'YYYY-MM')";

        return $query
            ->selectRaw("{$monthExpression} AS month, {$aggregate} AS aggregate_value")
            ->groupByRaw($monthExpression)
            ->pluck('aggregate_value', 'month')
            ->map(static fn ($value): float => (float) $value)
            ->all();
    }

    /**
     * @return array<int, CarbonImmutable>
     */
    private function monthsEndingAt(CarbonImmutable $now): array
    {
        $firstMonth = $now->startOfMonth()->subMonths(self::MONTH_COUNT - 1);

        return array_map(
            static fn (int $offset): CarbonImmutable => $firstMonth->addMonths($offset),
            range(0, self::MONTH_COUNT - 1)
        );
    }

    /**
     * @param  array<int, CarbonImmutable>  $months
     * @param  array<int, float>  $values
     * @return array{labels: array<int, string>, values: array<int, float>, current: float}
     */
    private function chartData(array $months, array $values): array
    {
        return [
            'labels' => array_map(static fn (CarbonImmutable $month): string => $month->format('M Y'), $months),
            'values' => $values,
            'current' => $values[array_key_last($values)] ?? 0.0,
        ];
    }
}
