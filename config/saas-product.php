<?php

return [
    'brand' => [
        'name' => env('APP_NAME', 'SubSync'),
        'tagline' => 'Subscription operations for growing media and membership teams.',
        'description' => 'Launch plans, collect payments, reconcile renewals, and manage subscriber access from one Laravel SaaS workspace.',
    ],

    'navigation' => [
        ['label' => 'Platform', 'href' => '#platform'],
        ['label' => 'Plans', 'href' => '#plans'],
        ['label' => 'Operations', 'href' => '#operations'],
    ],

    'metrics' => [
        ['label' => 'Payment channels', 'value' => '6+'],
        ['label' => 'Subscriber workflows', 'value' => '24/7'],
        ['label' => 'Plan setup', 'value' => 'Backend'],
    ],

    'features' => [
        [
            'key' => 'subscriber_management',
            'name' => 'Subscriber management',
            'description' => 'Organize individual and corporate subscribers with controlled access, whitelists, renewals, and account lifecycle tools.',
        ],
        [
            'key' => 'billing_automation',
            'name' => 'Billing automation',
            'description' => 'Run plans, coupons, checkout callbacks, invoices, approvals, and reconciliations through a single billing layer.',
        ],
        [
            'key' => 'corporate_accounts',
            'name' => 'Corporate accounts',
            'description' => 'Support B2B subscriptions, purchase orders, seats, invoice records, and customer-managed organization users.',
        ],
        [
            'key' => 'analytics_exports',
            'name' => 'Analytics and exports',
            'description' => 'Track conversions, transactions, subscriptions, churn, and operational reports without leaving the workspace.',
        ],
        [
            'key' => 'access_controls',
            'name' => 'Access controls',
            'description' => 'Use roles, permissions, audit logs, and feature gates to keep subscription operations governed as teams grow.',
        ],
        [
            'key' => 'integration_hooks',
            'name' => 'Integration hooks',
            'description' => 'Connect payment providers, webhooks, MCP tools, and product sites with Laravel-native extension points.',
        ],
    ],

    'plans' => [
        [
            'key' => 'launch',
            'name' => 'Launch',
            'summary' => 'For teams turning a single publication or membership product into a managed subscription offer.',
            'badge' => 'Start',
            'amount' => 290000,
            'currency' => 'kes',
            'interval' => 'monthly',
            'trial_days' => 14,
            'popular' => false,
            'cta' => 'Start Launch',
            'audience' => 'Single-product teams',
            'features' => [
                'subscriber_management' => ['label' => 'Up to 5,000 subscribers', 'limit' => 5000],
                'billing_automation' => ['label' => 'M-Pesa and card billing workflows'],
                'analytics_exports' => ['label' => 'Core subscription reports'],
                'access_controls' => ['label' => 'Owner and operator roles'],
            ],
        ],
        [
            'key' => 'scale',
            'name' => 'Scale',
            'summary' => 'For subscription businesses managing multiple products, approvals, finance handoffs, and retention workflows.',
            'badge' => 'Most popular',
            'amount' => 790000,
            'currency' => 'kes',
            'interval' => 'monthly',
            'trial_days' => 14,
            'popular' => true,
            'cta' => 'Choose Scale',
            'audience' => 'Growing SaaS operations',
            'features' => [
                'subscriber_management' => ['label' => 'Up to 50,000 subscribers', 'limit' => 50000],
                'billing_automation' => ['label' => 'Automated renewals, coupons, and approvals'],
                'corporate_accounts' => ['label' => 'Corporate subscriptions and seat tools'],
                'analytics_exports' => ['label' => 'Advanced revenue and churn exports'],
                'access_controls' => ['label' => 'Team permissions and audit trails'],
            ],
        ],
        [
            'key' => 'enterprise',
            'name' => 'Enterprise',
            'summary' => 'For large publishers and membership platforms needing custom integrations, governance, and high-volume operations.',
            'badge' => 'Custom',
            'amount' => 1490000,
            'currency' => 'kes',
            'interval' => 'monthly',
            'trial_days' => 30,
            'popular' => false,
            'cta' => 'Talk to Sales',
            'audience' => 'Multi-region teams',
            'features' => [
                'subscriber_management' => ['label' => 'Unlimited subscriber operations'],
                'billing_automation' => ['label' => 'Custom billing and reconciliation flows'],
                'corporate_accounts' => ['label' => 'Enterprise B2B account management'],
                'analytics_exports' => ['label' => 'Custom reports and finance exports'],
                'access_controls' => ['label' => 'Advanced roles, permissions, and audits'],
                'integration_hooks' => ['label' => 'Webhook, MCP, and product-site integrations'],
            ],
        ],
    ],
];
