<?php

return [
    'brand' => [
        'name' => env('APP_NAME', 'SubSync'),
        'tagline' => 'Enterprise subscription infrastructure for media companies.',
        'description' => 'Manage digital subscriptions, corporate access, payment reconciliation, renewals, and subscriber operations from one governed media platform.',
    ],

    'navigation' => [
        ['label' => 'Platform', 'href' => '#platform'],
        ['label' => 'Plans', 'href' => '#plans'],
        ['label' => 'Operations', 'href' => '#operations'],
    ],

    'metrics' => [
        ['label' => 'Used by', 'value' => 'Nation Media Group'],
        ['label' => 'Payment and access operations', 'value' => '24/7'],
        ['label' => 'Individual and corporate subscriptions', 'value' => 'Unified'],
    ],

    'features' => [
        [
            'key' => 'subscriber_management',
            'name' => 'Subscriber management',
            'description' => 'Control individual readers, corporate accounts, access whitelists, renewals, and lifecycle changes across publication products.',
        ],
        [
            'key' => 'billing_automation',
            'name' => 'Billing automation',
            'description' => 'Run plans, coupons, checkout callbacks, invoices, approvals, and reconciliation workflows through a single billing layer.',
        ],
        [
            'key' => 'corporate_accounts',
            'name' => 'Corporate accounts',
            'description' => 'Support B2B subscriptions, purchase orders, seats, invoice records, and organization-managed users for enterprise readers.',
        ],
        [
            'key' => 'analytics_exports',
            'name' => 'Analytics and exports',
            'description' => 'Track transactions, active subscriptions, churn, corporate usage, and finance reports without leaving the workspace.',
        ],
        [
            'key' => 'access_controls',
            'name' => 'Access controls',
            'description' => 'Use roles, permissions, audit logs, and approval controls to keep subscription operations governed as teams grow.',
        ],
        [
            'key' => 'integration_hooks',
            'name' => 'Integration hooks',
            'description' => 'Connect payment providers, webhooks, product sites, finance systems, and subscriber identity workflows.',
        ],
    ],

    'plans' => [
        [
            'key' => 'launch',
            'name' => 'Launch',
            'summary' => 'For media teams turning a single publication or membership product into a managed subscription offer.',
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
            'summary' => 'For publishers managing multiple products, approvals, finance handoffs, and retention workflows.',
            'badge' => 'Most popular',
            'amount' => 790000,
            'currency' => 'kes',
            'interval' => 'monthly',
            'trial_days' => 14,
            'popular' => true,
            'cta' => 'Choose Scale',
            'audience' => 'Growing media operations',
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
            'summary' => 'For large publishers and media groups needing custom integrations, governance, and high-volume operations.',
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
