<div class="wrapper">
    <nav id="sidebar" class="sidebar js-sidebar">
        <div class="sidebar-content js-simplebar">
            <a class="sidebar-brand px-4" href="{{ route('dashboard.index') }}">
                <img class="img-fluid" src="{{ asset('assets/img/logo-dark.png') }}" alt="Unified Sub">
            </a>

            <ul class="sidebar-nav">
                <li class="sidebar-header">
                    Main
                </li>
                @if(auth()->user()->type =='owner')
                    <li class="sidebar-item">
                        <a href="{{ route('dashboard.index') }}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="home"></i> <span class="align-middle">Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('currency.index') }}">
                            <i class="align-middle"
                               data-feather="repeat"></i>
                            <span class="align-middle">Currency Convertor</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('payment_method.index') }}">
                            <i class="align-middle"
                               data-feather="credit-card"></i>
                            <span class="align-middle">Payment Methods</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('email_template.index') }}">
                            <i class="align-middle"
                               data-feather="mail"></i>
                            <span class="align-middle">Email Template</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('site.index') }}">
                            <i class="align-middle"
                               data-feather="globe"></i>
                            <span class="align-middle">Site</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('product.index') }}">
                            <i class="align-middle"
                               data-feather="package"></i>
                            <span class="align-middle">Product</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('rate_type.index') }}">
                            <i class="align-middle"
                               data-feather="layers"></i>
                            <span class="align-middle">Subscription Type</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('product.rate.index',0) }}">
                            <i class="align-middle"
                               data-feather="sliders"></i>
                            <span class="align-middle">Rate</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('coupon.index') }}">
                            <i class="align-middle"
                               data-feather="tag"></i>
                            <span class="align-middle">Coupon</span>
                        </a>
                    </li>
                    <li class="sidebar-header">
                        Conversion
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('lead.index') }}">
                            <i class="align-middle"
                               data-feather="target"></i>
                            <span class="align-middle">Lead Gen</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('subscription.index') }}">
                            <i class="align-middle"
                               data-feather="refresh-cw"></i>
                            <span class="align-middle">Subscription</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('subscription.transaction.index',0) }}">
                            <i class="align-middle"
                               data-feather="credit-card"></i>
                            <span class="align-middle">Transaction</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('subscription-approval.index') }}">
                            <i class="align-middle" data-feather="check-square"></i>
                            <span class="align-middle">Subscription Approval</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('product.subscriber.index',0) }}">
                            <i class="align-middle"
                               data-feather="users"></i>
                            <span class="align-middle">Subscribers</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('agents.index') }}">
                            <i class="align-middle"
                               data-feather="user-check"></i>
                            <span class="align-middle">Sales Agents</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#whitelist"
                           data-bs-toggle="collapse"
                           class="sidebar-link collapsed">
                            <i class="align-middle"
                               data-feather="shield"></i>
                            <span class="align-middle">Whitelist</span>
                        </a>
                        <ul id="whitelist"
                            class="sidebar-dropdown list-unstyled collapse ">
                            <li class="sidebar-item">
                                <a class="sidebar-link"
                                   href="{{ route('whitelist.type.index','user') }}">Users</a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link"
                                   href="{{ route('whitelist.type.index','organization') }}">Organizations</a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link"
                                   href="{{ route('whitelist.type.index','ipaddress') }}">IP Address</a>
                            </li>
                        </ul>

                    </li>
                    <li class="sidebar-header">
                        B2B
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('organization.index') }}"><i
                                class="align-middle"
                                data-feather="briefcase"></i>Corporates</a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('organization.rate.index',0) }}"><i
                                class="align-middle"
                                data-feather="sliders"></i>Corporate Rates</a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('organization.subscription.index',0) }}"><i class="align-middle"
                                                                                      data-feather="archive"></i>Corporate
                            Subscriptions</a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{-- route('organization.subscriber.index',0) --}}">
                            <i class="align-middle"
                               data-feather="users"></i>
                            <span class="align-middle">Corporate Subscribers</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('organization.transaction.index',0) }}"><i class="align-middle"
                                                                                     data-feather="credit-card"></i>Corporate
                            Transactions</a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('organization.purchase.index',0) }}"><i
                                class="align-middle"
                                data-feather="file-text"></i>Corporate Purchase Orders</a>
                    </li>
                    <li class="sidebar-header">
                        Administration
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('user.index') }}">
                            <i class="align-middle"
                               data-feather="users"></i> <span class="align-middle">Users</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('user.roles.index',0) }}">
                            <i class="align-middle" data-feather="shield"></i> <span class="align-middle">Roles</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('user.logs.index',0) }}">
                            <i class="align-middle" data-feather="file-text"></i>
                            <span class="align-middle">Logs</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('mpesa_blacklist.index') }}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="alert-circle"></i> <span
                                class="align-middle">Mpesa Blacklist</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('media_events.index') }}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="activity"></i> <span
                                class="align-middle">Events</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('configuration.index') }}">
                            <i class="align-middle "
                               data-feather="settings"></i>
                            <span class="align-middle">Configuration</span>
                        </a>
                    </li>
                    <li class="sidebar-header">
                        Reports
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('report.subscriber_form') }}">
                            <i class="align-middle" data-feather="user-plus"></i>
                            <span class="align-middle">Registration</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('report.subscription_form') }}">
                            <i class="align-middle" data-feather="file-text"></i>
                            <span class="align-middle">Subscription</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('report.accounts_form') }}">
                            <i class="align-middle" data-feather="user"></i>
                            <span class="align-middle">Individual Accounts</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('report.activated_accounts_form') }}">
                            <i class="align-middle" data-feather="check-circle"></i>
                            <span class="align-middle">Activated Accounts</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link"
                           href="{{ route('report.revenue_form') }}">
                            <i class="align-middle" data-feather="trending-up"></i>
                            <span class="align-middle">Revenue</span>
                        </a>
                    </li>
                    <li class="sidebar-header">
                        Data Migration
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('migrates.index') }}">
                            <i class="align-middle" data-feather="upload-cloud"></i> <span class="align-middle">Rates</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('migindividuals.index') }}">
                            <i class="align-middle" data-feather="upload-cloud"></i> <span
                                class="align-middle">Individual Accounts</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('migorganizations.index') }}">
                            <i class="align-middle" data-feather="upload-cloud"></i> <span class="align-middle">Corporate Subscriptions</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('migorganizationusers.index') }}">
                            <i class="align-middle" data-feather="upload-cloud"></i> <span class="align-middle">Corporate Users</span>
                        </a>
                    </li>
                    @php
                        $pluginMenus = app(\App\Core\Menu\MenuManager::class)->get('sidebar');
                    @endphp

                    @foreach($pluginMenus as $item)
                        @if(($item['type'] ?? 'link') === 'title')
                            <li class="sidebar-header">{{ $item['title'] }}</li>
                            @continue
                        @endif

                        @if(($item['type'] ?? 'link') === 'divider')
                            <li class="sidebar-item">
                                <hr class="my-2">
                            </li>
                            @continue
                        @endif

                        @include('includes.sidebar-menu-item', ['item' => $item])
                    @endforeach
                    @php
                        $saasMenus = app(\Caydeesoft\MenuManager\Services\MenuBuilder::class)->build('saas');
                    @endphp
                    @foreach($saasMenus as $item)
                        @if(($item['type'] ?? 'item') === 'header')
                            <li class="sidebar-header">{{ $item['title'] }}</li>
                            @continue
                        @endif

                        <li class="sidebar-item">
                            <a class="sidebar-link" href="{{ route($item['route'], $item['params'] ?? []) }}">
                                @if(!empty($item['icon']))
                                    <i class="align-middle" data-feather="{{ $item['icon'] }}"></i>
                                @endif
                                <span class="align-middle">{{ $item['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('plugins.index') }}">
                            <i class="align-middle" data-feather="package"></i> <span class="align-middle">Plugins</span>
                        </a>
                    </li>
                @else
                    <!-- Organization Dashboard -->
                    <li class="sidebar-item">
                        <a href="{{ route('client_dashboard.index') }}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="home"></i> <span class="align-middle">Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('client_invoice.index') }}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="clipboard"></i> <span
                                class="align-middle">Invoices</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('client_purchase_order.index') }}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="clipboard"></i> <span class="align-middle">Purchase Orders</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('client_receipt.index') }}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="clipboard"></i> <span
                                class="align-middle">Receipt</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('client_subscription.index') }}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="archive"></i> <span
                                class="align-middle">Subscriptions</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('client_users.index') }}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="user"></i> <span class="align-middle">Users</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{-- route('client_organizations.index') --}}"
                           class="sidebar-link">
                            <i class="align-middle"
                               data-feather="user"></i> <span
                                class="align-middle">Organizations</span>
                        </a>
                    </li>
                    <li class="sidebar-header">
                        Audit
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route("user.logs.index",['user'=>0]) }}">
                            <i class="align-middle" data-feather="file-text"></i> <span class="align-middle">Logs</span>
                        </a>
                    </li>
                @endif



            </ul>
        </div>
    </nav>
