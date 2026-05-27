<?php

declare(strict_types=1);

use Caydeesoft\SaasKit\Invoices\Services\SimplePdfRenderer;
use Caydeesoft\SaasKit\Payments\Adapters\StripePaymentGateway;
use Caydeesoft\SaasKit\Tenancy\Models\Tenant;
use Caydeesoft\SaasKit\Tenancy\Resolvers\HeaderTenantResolver;
use Caydeesoft\SaasKit\Tenancy\Strategies\NoopTenantIsolationStrategy;
use Caydeesoft\SaasKit\Users\Models\SaasUser;

return [
    'package' => [
        'name' => 'caydeesoft/saas-kit',
        'version' => env('SAAS_KIT_VERSION', '0.1.0'),
    ],

    'tenant' => [
        'model' => Tenant::class,
        'resolver' => HeaderTenantResolver::class,
        'isolation_strategy' => NoopTenantIsolationStrategy::class,
        'header' => 'X-Tenant-ID',
        'route_parameter' => 'tenant',
    ],

    'users' => [
        'model' => SaasUser::class,
    ],

    'billing' => [
        'currency' => env('SAAS_KIT_CURRENCY', 'usd'),
        'trial_days' => (int) env('SAAS_KIT_TRIAL_DAYS', 14),
    ],

    'payments' => [
        'default' => env('SAAS_KIT_PAYMENT_GATEWAY', 'stripe'),
        'gateways' => [
            'stripe' => StripePaymentGateway::class,
        ],
        'stripe' => [
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
    ],

    'invoices' => [
        'renderer' => SimplePdfRenderer::class,
        'number_prefix' => env('SAAS_KIT_INVOICE_PREFIX', 'INV'),
    ],

    'api' => [
        'enabled' => true,
        'prefix' => 'api/saas-kit',
        'middleware' => ['api'],
    ],

    'mcp' => [
        'enabled' => true,
        'protocol_version' => '2025-11-25',
        'supported_protocol_versions' => ['2025-11-25', '2025-06-18', '2025-03-26'],
        'server_name' => 'caydeesoft/saas-kit',
        'server_title' => 'SaaS Kit MCP Server',
        'server_description' => 'Read-only Laravel SaaS infrastructure tools for health checks and SEO metadata.',
        'instructions' => 'Read-only SaaS Kit tools for health checks and SEO metadata generation.',
        'allowed_origins' => array_values(array_filter([
            env('APP_URL', 'http://localhost'),
            'http://localhost',
            'http://127.0.0.1',
            'http://[::1]',
        ])),
    ],

    'seo' => [
        'site_name' => env('APP_NAME', 'SaaS Kit'),
        'title_suffix' => env('APP_NAME', 'SaaS Kit'),
        'robots' => env('SAAS_KIT_SEO_ROBOTS', 'index,follow'),
        'twitter_card' => 'summary_large_image',
    ],

    'audit' => [
        'enabled' => true,
    ],
];
