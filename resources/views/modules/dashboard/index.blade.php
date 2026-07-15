@extends('includes.body')
@section('content')
    <div class="dashboard-shell">
        <nav aria-label="breadcrumb" class="dashboard-breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>

        <section class="dashboard-hero" aria-labelledby="dashboard-title">
            <div>
                <span class="dashboard-kicker">Subscription command center</span>
                <h1 id="dashboard-title" class="dashboard-title">Dashboard</h1>
                <p class="dashboard-copy mb-0">
                    Track revenue, approvals, subscribers, and product readiness from one operational view.
                </p>
            </div>
            <div class="dashboard-hero-actions" aria-label="Dashboard actions">
                @foreach($dashboard['quickActions'] as $action)
                    <a href="{{ $action['route'] }}" class="btn btn-light dashboard-action">
                        <i data-feather="{{ $action['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $action['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="dashboard-metrics" aria-label="Key metrics">
            @foreach($dashboard['metrics'] as $metric)
                <a href="{{ $metric['route'] }}" class="dashboard-metric dashboard-metric-{{ $metric['tone'] }}">
                    <span class="dashboard-metric-icon">
                        <i data-feather="{{ $metric['icon'] }}" aria-hidden="true"></i>
                    </span>
                    <span class="dashboard-metric-label">{{ $metric['label'] }}</span>
                    <strong class="dashboard-metric-value">{{ $metric['value'] }}</strong>
                    <span class="dashboard-metric-helper">{{ $metric['helper'] }}</span>
                </a>
            @endforeach
        </section>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <section class="dashboard-panel h-100" aria-labelledby="recent-transactions-title">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-kicker">Payments</span>
                            <h2 id="recent-transactions-title" class="dashboard-panel-title">Recent Transactions</h2>
                        </div>
                        <a href="{{ route('subscription.index') }}" class="btn btn-outline-dark btn-sm">
                            <i data-feather="arrow-up-right" aria-hidden="true"></i>
                            View Subscriptions
                        </a>
                    </div>

                    @if($dashboard['recentTransactions']->isNotEmpty())
                        <div class="dashboard-transaction-list">
                            @foreach($dashboard['recentTransactions'] as $transaction)
                                <div class="dashboard-transaction">
                                    <div class="dashboard-transaction-main">
                                        <span class="dashboard-transaction-icon">
                                            <i data-feather="credit-card" aria-hidden="true"></i>
                                        </span>
                                        <div>
                                            <strong>{{ $transaction->receipt ?: $transaction->identifier ?: 'Pending receipt' }}</strong>
                                            <span>
                                                {{ $transaction->user?->name ?: $transaction->user?->email ?: 'Unassigned customer' }}
                                                &middot; {{ $transaction->payment_method?->name ?: 'Payment method pending' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dashboard-transaction-meta">
                                        <strong>{{ $transaction->currency ?: 'KES' }} {{ number_format((float) $transaction->amount_paid, 2) }}</strong>
                                        <span class="dashboard-status dashboard-status-{{ (int) $transaction->status }}">
                                            {{ (int) $transaction->status === 1 ? 'Paid' : ((int) $transaction->status === 2 ? 'Failed' : 'Pending') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="dashboard-empty">
                            <i data-feather="inbox" aria-hidden="true"></i>
                            <strong>No transactions yet</strong>
                            <span>Payments will appear here as soon as subscriptions begin processing.</span>
                        </div>
                    @endif
                </section>
            </div>

            <div class="col-12 col-xl-4">
                <section class="dashboard-panel h-100" aria-labelledby="revenue-title">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-kicker">Month to date</span>
                            <h2 id="revenue-title" class="dashboard-panel-title">Revenue Pulse</h2>
                        </div>
                    </div>

                    <div class="dashboard-revenue">
                        <span>Total paid</span>
                        <strong>{{ number_format($dashboard['revenue']['paid'], 2) }}</strong>
                    </div>

                    <div class="dashboard-health-list">
                        <div class="dashboard-health-item">
                            <span>Pending payments</span>
                            <strong>{{ number_format($dashboard['revenue']['pending']) }}</strong>
                        </div>
                        <div class="dashboard-health-item">
                            <span>Failed payments</span>
                            <strong>{{ number_format($dashboard['revenue']['failed']) }}</strong>
                        </div>
                        <div class="dashboard-health-item">
                            <span>Awaiting approval</span>
                            <strong>{{ number_format($dashboard['revenue']['approvals']) }}</strong>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <section class="dashboard-panel" aria-labelledby="operations-title">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-kicker">Operations</span>
                    <h2 id="operations-title" class="dashboard-panel-title">Platform Health</h2>
                </div>
            </div>

            <div class="dashboard-health-grid">
                @foreach($dashboard['operationalHealth'] as $item)
                    <div class="dashboard-health-card">
                        <span class="dashboard-health-icon">
                            <i data-feather="{{ $item['icon'] }}" aria-hidden="true"></i>
                        </span>
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ $item['value'] }}</strong>
                        <small>{{ $item['detail'] }}</small>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
@section('header')

@endsection
@section('footer')

@endsection
