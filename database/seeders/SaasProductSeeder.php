<?php

namespace Database\Seeders;

use Caydeesoft\SaasKit\Billing\Models\Plan;
use Caydeesoft\SaasKit\Features\Models\Feature;
use Caydeesoft\SaasKit\Features\Models\PlanFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SaasProductSeeder extends Seeder
{
    public function run(): void
    {
        $features = collect(config('saas-product.features', []))
            ->mapWithKeys(function (array $feature): array {
                $model = Feature::query()->updateOrCreate(
                    ['key' => $feature['key']],
                    [
                        'name' => $feature['name'],
                        'description' => $feature['description'] ?? null,
                        'metadata' => $feature['metadata'] ?? [],
                    ],
                );

                return [$model->key => $model];
            });

        foreach (config('saas-product.plans', []) as $plan) {
            $planFeatures = collect($plan['features'] ?? []);

            $model = Plan::query()->updateOrCreate(
                ['key' => $plan['key']],
                [
                    'name' => $plan['name'],
                    'interval' => $plan['interval'] ?? 'monthly',
                    'amount' => $plan['amount'],
                    'currency' => $plan['currency'] ?? config('saas-kit.billing.currency', 'usd'),
                    'trial_days' => $plan['trial_days'] ?? config('saas-kit.billing.trial_days'),
                    'features' => $planFeatures->map(fn (array $settings): string => (string) ($settings['label'] ?? 'Included'))->all(),
                    'limits' => $planFeatures
                        ->filter(fn (array $settings): bool => isset($settings['limit']))
                        ->map(fn (array $settings): int => (int) $settings['limit'])
                        ->all(),
                    'active' => true,
                    'metadata' => Arr::only($plan, ['summary', 'badge', 'popular', 'cta', 'audience']),
                ],
            );

            $planFeatures->each(function (array $settings, string $featureKey) use ($features, $model): void {
                $feature = $features->get($featureKey);

                if ($feature === null) {
                    return;
                }

                PlanFeature::query()->updateOrCreate(
                    [
                        'plan_id' => $model->getKey(),
                        'feature_id' => $feature->getKey(),
                    ],
                    [
                        'enabled' => true,
                        'limit' => $settings['limit'] ?? null,
                        'value' => ['label' => (string) ($settings['label'] ?? $feature->name)],
                    ],
                );
            });
        }
    }
}
