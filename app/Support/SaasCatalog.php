<?php

namespace App\Support;

use Caydeesoft\SaasKit\Billing\Models\Plan;
use Caydeesoft\SaasKit\Features\Models\Feature;
use Caydeesoft\SaasKit\Features\Models\PlanFeature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SaasCatalog
{
    public function brand(): array
    {
        return (array) config('saas-product.brand', []);
    }

    public function navigation(): array
    {
        return (array) config('saas-product.navigation', []);
    }

    public function metrics(): array
    {
        return (array) config('saas-product.metrics', []);
    }

    public function features(): Collection
    {
        if (! $this->hasCatalogTables()) {
            return $this->configuredFeatures();
        }

        $features = Feature::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Feature $feature): array => [
                'key' => $feature->key,
                'name' => $feature->name,
                'description' => $feature->description,
            ]);

        return $features->isNotEmpty() ? $features : $this->configuredFeatures();
    }

    public function plans(): Collection
    {
        if (! $this->hasCatalogTables()) {
            return $this->configuredPlans();
        }

        $plans = Plan::query()
            ->where('active', true)
            ->orderBy('amount')
            ->get();

        if ($plans->isEmpty()) {
            return $this->configuredPlans();
        }

        $planFeatures = PlanFeature::query()
            ->with('feature')
            ->whereIn('plan_id', $plans->modelKeys())
            ->where('enabled', true)
            ->get()
            ->groupBy('plan_id');

        return $plans->map(fn (Plan $plan): array => $this->fromPlanModel(
            $plan,
            $planFeatures->get($plan->getKey(), collect()),
        ));
    }

    protected function configuredFeatures(): Collection
    {
        return collect(config('saas-product.features', []))
            ->map(fn (array $feature): array => [
                'key' => (string) $feature['key'],
                'name' => (string) $feature['name'],
                'description' => (string) ($feature['description'] ?? ''),
            ])
            ->values();
    }

    protected function configuredPlans(): Collection
    {
        $features = $this->configuredFeatures()->keyBy('key');

        return collect(config('saas-product.plans', []))
            ->map(function (array $plan) use ($features): array {
                $planFeatures = collect($plan['features'] ?? [])
                    ->map(function (array $settings, string $featureKey) use ($features): array {
                        $feature = $features->get($featureKey, [
                            'key' => $featureKey,
                            'name' => $featureKey,
                            'description' => '',
                        ]);

                        return [
                            'key' => $featureKey,
                            'name' => $feature['name'],
                            'description' => $feature['description'],
                            'label' => (string) ($settings['label'] ?? $feature['name']),
                            'limit' => $settings['limit'] ?? null,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'key' => (string) $plan['key'],
                    'name' => (string) $plan['name'],
                    'summary' => (string) ($plan['summary'] ?? ''),
                    'badge' => (string) ($plan['badge'] ?? ''),
                    'amount' => (int) $plan['amount'],
                    'currency' => strtoupper((string) ($plan['currency'] ?? 'usd')),
                    'interval' => (string) ($plan['interval'] ?? 'monthly'),
                    'trial_days' => (int) ($plan['trial_days'] ?? 0),
                    'popular' => (bool) ($plan['popular'] ?? false),
                    'cta' => (string) ($plan['cta'] ?? 'Get started'),
                    'audience' => (string) ($plan['audience'] ?? ''),
                    'price' => $this->formatPrice((int) $plan['amount'], (string) ($plan['currency'] ?? 'usd')),
                    'features' => $planFeatures,
                ];
            })
            ->values();
    }

    protected function fromPlanModel(Plan $plan, Collection $planFeatures): array
    {
        $metadata = (array) ($plan->metadata ?? []);

        return [
            'key' => $plan->key,
            'name' => $plan->name,
            'summary' => (string) ($metadata['summary'] ?? ''),
            'badge' => (string) ($metadata['badge'] ?? ''),
            'amount' => (int) $plan->amount,
            'currency' => strtoupper((string) $plan->currency),
            'interval' => $plan->interval,
            'trial_days' => (int) ($plan->trial_days ?? 0),
            'popular' => (bool) ($metadata['popular'] ?? false),
            'cta' => (string) ($metadata['cta'] ?? 'Get started'),
            'audience' => (string) ($metadata['audience'] ?? ''),
            'price' => $this->formatPrice((int) $plan->amount, (string) $plan->currency),
            'features' => $planFeatures
                ->map(fn (PlanFeature $planFeature): array => [
                    'key' => $planFeature->feature?->key ?? '',
                    'name' => $planFeature->feature?->name ?? '',
                    'description' => $planFeature->feature?->description ?? '',
                    'label' => (string) (($planFeature->value ?? [])['label'] ?? $planFeature->feature?->name ?? ''),
                    'limit' => $planFeature->limit,
                ])
                ->filter(fn (array $feature): bool => $feature['name'] !== '')
                ->values()
                ->all(),
        ];
    }

    protected function hasCatalogTables(): bool
    {
        try {
            if (config('database.default') === 'sqlite') {
                $database = (string) config('database.connections.sqlite.database');

                if ($database !== ':memory:' && ! file_exists($database)) {
                    return false;
                }
            }

            return Schema::hasTable('saas_kit_plans')
                && Schema::hasTable('saas_kit_features')
                && Schema::hasTable('saas_kit_plan_features');
        } catch (Throwable) {
            return false;
        }
    }

    protected function formatPrice(int $amount, string $currency): string
    {
        return strtoupper($currency).' '.number_format($amount / 100);
    }
}
