<div id="dashboard-realtime"
     data-dashboard-snapshot-url="{{ route('dashboard.snapshot') }}"
     aria-live="polite">
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

    <div class="dashboard-chart-grid">
        <section class="dashboard-panel dashboard-chart-panel" aria-labelledby="cumulative-revenue-title">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-kicker">Last 12 months</span>
                    <h2 id="cumulative-revenue-title" class="dashboard-panel-title">Cumulative Revenue</h2>
                </div>
                <div class="dashboard-chart-summary">
                    <span>Paid to date</span>
                    <strong>{{ number_format($dashboard['charts']['cumulativeRevenue']['current'], 2) }}</strong>
                </div>
            </div>

            <p class="dashboard-chart-description">Successful payments accumulated through the end of each month.</p>

            <div class="dashboard-chart-visual">
                <canvas data-dashboard-chart="cumulative-revenue"
                        data-chart-labels='@json($dashboard['charts']['cumulativeRevenue']['labels'])'
                        data-chart-values='@json($dashboard['charts']['cumulativeRevenue']['values'])'
                        role="img"
                        aria-label="Cumulative paid revenue for each of the last 12 months"></canvas>
            </div>

            <details class="dashboard-chart-data">
                <summary>View cumulative revenue data</summary>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Month</th>
                                <th scope="col" class="text-right">Paid revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboard['charts']['cumulativeRevenue']['labels'] as $index => $label)
                                <tr>
                                    <th scope="row">{{ $label }}</th>
                                    <td class="text-right">{{ number_format($dashboard['charts']['cumulativeRevenue']['values'][$index], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </section>

        <section class="dashboard-panel dashboard-chart-panel" aria-labelledby="churn-rate-title">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-kicker">Last 12 months</span>
                    <h2 id="churn-rate-title" class="dashboard-panel-title">Churn Rate</h2>
                </div>
                <div class="dashboard-chart-summary">
                    <span>Current month</span>
                    <strong>{{ number_format($dashboard['charts']['churnRate']['current'], 2) }}%</strong>
                </div>
            </div>

            <p class="dashboard-chart-description">Recurring subscriptions cancelled during the month as a share of those active at its start.</p>

            <div class="dashboard-chart-visual">
                <canvas data-dashboard-chart="churn-rate"
                        data-chart-labels='@json($dashboard['charts']['churnRate']['labels'])'
                        data-chart-values='@json($dashboard['charts']['churnRate']['values'])'
                        role="img"
                        aria-label="Monthly recurring subscription churn rate for each of the last 12 months"></canvas>
            </div>

            <details class="dashboard-chart-data">
                <summary>View churn rate data</summary>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Month</th>
                                <th scope="col" class="text-right">Churn rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboard['charts']['churnRate']['labels'] as $index => $label)
                                <tr>
                                    <th scope="row">{{ $label }}</th>
                                    <td class="text-right">{{ number_format($dashboard['charts']['churnRate']['values'][$index], 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </section>
    </div>

    <div class="dashboard-overview-grid">
        <section class="dashboard-panel" aria-labelledby="recent-transactions-title">
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

        <section class="dashboard-panel" aria-labelledby="revenue-title">
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
