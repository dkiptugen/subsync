@php
    $isOwner = auth()->user()->type === 'owner';
    $dashboardRoute = $isOwner ? 'dashboard.index' : 'client_dashboard.index';
    $dashboardPattern = $isOwner ? 'dashboard.*' : 'client_dashboard.*';

    $menuSections = $isOwner ? [
        [
            'id' => 'catalog',
            'title' => 'Catalog & Billing',
            'icon' => 'package',
            'items' => [
                ['title' => 'Products', 'route' => 'product.index', 'active' => 'product.index', 'icon' => 'package'],
                ['title' => 'Subscription Types', 'route' => 'rate_type.index', 'active' => 'rate_type.*', 'icon' => 'layers'],
                ['title' => 'Rates', 'route' => 'product.rate.index', 'params' => [0], 'active' => 'product.rate.*', 'icon' => 'sliders'],
                ['title' => 'Coupons', 'route' => 'coupon.index', 'active' => 'coupon.*', 'icon' => 'tag'],
                ['title' => 'Payment Methods', 'route' => 'payment_method.index', 'active' => 'payment_method.*', 'icon' => 'credit-card'],
                ['title' => 'Currency', 'route' => 'currency.index', 'active' => 'currency.*', 'icon' => 'repeat'],
                ['title' => 'Sites', 'route' => 'site.index', 'active' => 'site.*', 'icon' => 'globe'],
                ['title' => 'Email Templates', 'route' => 'email_template.index', 'active' => 'email_template.*', 'icon' => 'mail'],
            ],
        ],
        [
            'id' => 'subscriptions',
            'title' => 'Subscription Operations',
            'icon' => 'refresh-cw',
            'items' => [
                ['title' => 'Subscriptions', 'route' => 'subscription.index', 'active' => 'subscription.index', 'icon' => 'refresh-cw'],
                ['title' => 'Transactions', 'route' => 'subscription.transaction.index', 'params' => [0], 'active' => 'subscription.transaction.*', 'icon' => 'credit-card'],
                ['title' => 'Approvals', 'route' => 'subscription-approval.index', 'active' => 'subscription-approval.*', 'icon' => 'check-square'],
                ['title' => 'Subscribers', 'route' => 'product.subscriber.index', 'params' => [0], 'active' => 'product.subscriber.*', 'icon' => 'users'],
                ['title' => 'Leads', 'route' => 'lead.index', 'active' => 'lead.*', 'icon' => 'target'],
                ['title' => 'Sales Agents', 'route' => 'agents.index', 'active' => 'agents.*', 'icon' => 'user-check'],
                ['title' => 'User Whitelist', 'route' => 'whitelist.type.index', 'params' => ['user'], 'active' => 'whitelist.type.*', 'activeParams' => ['type' => 'user'], 'icon' => 'shield'],
                ['title' => 'Organization Whitelist', 'route' => 'whitelist.type.index', 'params' => ['organization'], 'active' => 'whitelist.type.*', 'activeParams' => ['type' => 'organization'], 'icon' => 'shield'],
                ['title' => 'IP Whitelist', 'route' => 'whitelist.type.index', 'params' => ['ipaddress'], 'active' => 'whitelist.type.*', 'activeParams' => ['type' => 'ipaddress'], 'icon' => 'shield'],
            ],
        ],
        [
            'id' => 'corporate',
            'title' => 'Corporate',
            'icon' => 'briefcase',
            'items' => [
                ['title' => 'Corporates', 'route' => 'organization.index', 'active' => 'organization.index', 'icon' => 'briefcase'],
                ['title' => 'Corporate Rates', 'route' => 'organization.rate.index', 'params' => [0], 'active' => 'organization.rate.*', 'icon' => 'sliders'],
                ['title' => 'Corporate Subscriptions', 'route' => 'organization.subscription.index', 'params' => [0], 'active' => 'organization.subscription.*', 'icon' => 'archive'],
                ['title' => 'Corporate Transactions', 'route' => 'organization.transaction.index', 'params' => [0], 'active' => 'organization.transaction.*', 'icon' => 'credit-card'],
                ['title' => 'Purchase Orders', 'route' => 'organization.purchase.index', 'params' => [0], 'active' => 'organization.purchase.*', 'icon' => 'file-text'],
            ],
        ],
        [
            'id' => 'administration',
            'title' => 'Administration',
            'icon' => 'settings',
            'items' => [
                ['title' => 'Users', 'route' => 'user.index', 'active' => 'user.index', 'icon' => 'users'],
                ['title' => 'Roles', 'route' => 'user.roles.index', 'params' => [0], 'active' => 'user.roles.*', 'icon' => 'shield'],
                ['title' => 'Activity Logs', 'route' => 'user.logs.index', 'params' => [0], 'active' => 'user.logs.*', 'icon' => 'file-text'],
                ['title' => 'M-Pesa Blacklist', 'route' => 'mpesa_blacklist.index', 'active' => 'mpesa_blacklist.*', 'icon' => 'alert-circle'],
                ['title' => 'Events', 'route' => 'media_events.index', 'active' => 'media_events.*', 'icon' => 'activity'],
                ['title' => 'Configuration', 'route' => 'configuration.index', 'active' => 'configuration.*', 'icon' => 'settings'],
            ],
        ],
        [
            'id' => 'reports',
            'title' => 'Reports',
            'icon' => 'bar-chart-2',
            'items' => [
                ['title' => 'Registration', 'route' => 'report.subscriber_form', 'active' => 'report.subscriber*', 'icon' => 'user-plus'],
                ['title' => 'Subscriptions', 'route' => 'report.subscription_form', 'active' => 'report.subscription*', 'icon' => 'file-text'],
                ['title' => 'Individual Accounts', 'route' => 'report.accounts_form', 'active' => 'report.accounts*', 'icon' => 'user'],
                ['title' => 'Activated Accounts', 'route' => 'report.activated_accounts_form', 'active' => 'report.activated_accounts*', 'icon' => 'check-circle'],
                ['title' => 'Revenue', 'route' => 'report.revenue_form', 'active' => 'report.revenue*', 'icon' => 'trending-up'],
            ],
        ],
        [
            'id' => 'migration',
            'title' => 'Data Migration',
            'icon' => 'upload-cloud',
            'items' => [
                ['title' => 'Rates', 'route' => 'migrates.index', 'active' => 'migrates.*', 'icon' => 'upload-cloud'],
                ['title' => 'Individual Accounts', 'route' => 'migindividuals.index', 'active' => 'migindividuals.*', 'icon' => 'upload-cloud'],
                ['title' => 'Corporate Subscriptions', 'route' => 'migorganizations.index', 'active' => 'migorganizations.*', 'icon' => 'upload-cloud'],
                ['title' => 'Corporate Users', 'route' => 'migorganizationusers.index', 'active' => 'migorganizationusers.*', 'icon' => 'upload-cloud'],
            ],
        ],
    ] : [
        [
            'id' => 'billing',
            'title' => 'Billing',
            'icon' => 'credit-card',
            'items' => [
                ['title' => 'Invoices', 'route' => 'client_invoice.index', 'active' => 'client_invoice.*', 'icon' => 'file-text'],
                ['title' => 'Purchase Orders', 'route' => 'client_purchase_order.index', 'active' => 'client_purchase_order.*', 'icon' => 'clipboard'],
                ['title' => 'Receipts', 'route' => 'client_receipt.index', 'active' => 'client_receipt.*', 'icon' => 'credit-card'],
            ],
        ],
        [
            'id' => 'account',
            'title' => 'Account',
            'icon' => 'briefcase',
            'items' => [
                ['title' => 'Subscriptions', 'route' => 'client_subscription.index', 'active' => 'client_subscription.*', 'icon' => 'archive'],
                ['title' => 'Users', 'route' => 'client_users.index', 'active' => 'client_users.*', 'icon' => 'users'],
            ],
        ],
        [
            'id' => 'audit',
            'title' => 'Audit',
            'icon' => 'file-text',
            'items' => [
                ['title' => 'Activity Logs', 'route' => 'user.logs.index', 'params' => [0], 'active' => 'user.logs.*', 'icon' => 'file-text'],
            ],
        ],
    ];

    $isMenuItemActive = static function (array $menuItem): bool {
        if (! request()->routeIs($menuItem['active'] ?? $menuItem['route'])) {
            return false;
        }

        foreach ($menuItem['activeParams'] ?? [] as $parameter => $value) {
            if ((string) request()->route($parameter) !== (string) $value) {
                return false;
            }
        }

        return true;
    };
@endphp

<nav id="sidebar" class="sidebar js-sidebar" aria-label="Primary navigation">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand px-4" href="{{ route($dashboardRoute) }}">
            <img class="img-fluid" src="{{ asset('assets/img/logo-dark.png') }}" alt="Unified Sub">
        </a>

        <ul id="sidebar-navigation" class="sidebar-nav">
            <li class="sidebar-header">Workspace</li>
            <li @class(['sidebar-item', 'active' => request()->routeIs($dashboardPattern)])>
                <a href="{{ route($dashboardRoute) }}"
                   class="sidebar-link"
                   @if(request()->routeIs($dashboardPattern)) aria-current="page" @endif>
                    <i class="align-middle" data-feather="home" aria-hidden="true"></i>
                    <span class="align-middle">Dashboard</span>
                </a>
            </li>

            @foreach($menuSections as $section)
                @php
                    $sectionIsActive = collect($section['items'])->contains($isMenuItemActive);
                    $collapseId = 'sidebar-section-'.$section['id'];
                @endphp
                <li @class(['sidebar-item', 'sidebar-section', 'active' => $sectionIsActive])>
                    <a href="#{{ $collapseId }}"
                       class="sidebar-link sidebar-section-toggle"
                       data-bs-toggle="collapse"
                       role="button"
                       aria-expanded="{{ $sectionIsActive ? 'true' : 'false' }}"
                       aria-controls="{{ $collapseId }}">
                        <i class="align-middle" data-feather="{{ $section['icon'] }}" aria-hidden="true"></i>
                        <span class="align-middle">{{ $section['title'] }}</span>
                        <i class="sidebar-section-chevron" data-feather="chevron-down" aria-hidden="true"></i>
                    </a>
                    <ul id="{{ $collapseId }}"
                        @class(['sidebar-dropdown', 'list-unstyled', 'collapse', 'show' => $sectionIsActive])
                        data-bs-parent="#sidebar-navigation">
                        @foreach($section['items'] as $menuItem)
                            @php
                                $itemIsActive = $isMenuItemActive($menuItem);
                            @endphp
                            <li @class(['sidebar-item', 'active' => $itemIsActive])>
                                <a class="sidebar-link"
                                   href="{{ route($menuItem['route'], $menuItem['params'] ?? []) }}"
                                   @if($itemIsActive) aria-current="page" @endif>
                                    <i class="align-middle" data-feather="{{ $menuItem['icon'] }}" aria-hidden="true"></i>
                                    <span class="align-middle">{{ $menuItem['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach

            @if($isOwner)
                @php
                    $pluginMenus = app(\App\Core\Menu\MenuManager::class)->get('sidebar');
                    $saasMenus = app(\Caydeesoft\MenuManager\Services\MenuBuilder::class)->build('saas');
                    $containsActivePlugin = function (array $items) use (&$containsActivePlugin): bool {
                        foreach ($items as $item) {
                            if (! empty($item['route']) && request()->routeIs($item['route'], $item['route'].'.*')) {
                                return true;
                            }

                            if ($containsActivePlugin($item['children'] ?? [])) {
                                return true;
                            }
                        }

                        return false;
                    };
                    $extensionsAreActive = request()->routeIs('plugins.*')
                        || $containsActivePlugin($pluginMenus)
                        || collect($saasMenus)->contains(static fn (array $item): bool => ! empty($item['route'])
                            && request()->routeIs($item['route'], $item['route'].'.*'));
                @endphp
                <li @class(['sidebar-item', 'sidebar-section', 'active' => $extensionsAreActive])>
                    <a href="#sidebar-section-extensions"
                       class="sidebar-link sidebar-section-toggle"
                       data-bs-toggle="collapse"
                       role="button"
                       aria-expanded="{{ $extensionsAreActive ? 'true' : 'false' }}"
                       aria-controls="sidebar-section-extensions">
                        <i class="align-middle" data-feather="grid" aria-hidden="true"></i>
                        <span class="align-middle">Extensions</span>
                        <i class="sidebar-section-chevron" data-feather="chevron-down" aria-hidden="true"></i>
                    </a>
                    <ul id="sidebar-section-extensions"
                        @class(['sidebar-dropdown', 'list-unstyled', 'collapse', 'show' => $extensionsAreActive])
                        data-bs-parent="#sidebar-navigation">
                        @foreach($pluginMenus as $item)
                            @if(($item['type'] ?? 'link') === 'title')
                                <li class="sidebar-header">{{ $item['title'] }}</li>
                                @continue
                            @endif

                            @if(($item['type'] ?? 'link') === 'divider')
                                <li class="sidebar-item"><hr class="my-2"></li>
                                @continue
                            @endif

                            @include('includes.sidebar-menu-item', ['item' => $item])
                        @endforeach

                        @foreach($saasMenus as $item)
                            @if(($item['type'] ?? 'item') === 'header')
                                <li class="sidebar-header">{{ $item['title'] }}</li>
                                @continue
                            @endif

                            <li class="sidebar-item">
                                <a class="sidebar-link" href="{{ route($item['route'], $item['params'] ?? []) }}">
                                    @if(! empty($item['icon']))
                                        <i class="align-middle" data-feather="{{ $item['icon'] }}" aria-hidden="true"></i>
                                    @endif
                                    <span class="align-middle">{{ $item['title'] }}</span>
                                </a>
                            </li>
                        @endforeach

                        <li @class(['sidebar-item', 'active' => request()->routeIs('plugins.*')])>
                            <a class="sidebar-link" href="{{ route('plugins.index') }}">
                                <i class="align-middle" data-feather="package" aria-hidden="true"></i>
                                <span class="align-middle">Plugins</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
    </div>
</nav>
