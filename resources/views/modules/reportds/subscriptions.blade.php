@extends('includes.body')
@section('content')
    <div class="col-12">
        @include('modules.reportds.partials.filter-hero', [
            'title' => 'Subscription Report',
            'formRoute' => route('report.subscription_form'),
            'exportRoute' => route('report.subscription'),
            'filters' => $filters,
            'products' => $products,
            'rateTypes' => $rateTypes,
            'showStatus' => true,
        ])

        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card card-border-nation h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-nation">Daily Subscriptions</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="subscriptionDailyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-border-nation h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-nation">Subscription Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="subscriptionStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Subscription Results</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-hover">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Identifier</th>
                                <th>Product</th>
                                <th>Subscription Type</th>
                                <th>Amount Paid</th>
                                <th>Receipt</th>
                                <th>Sub Date</th>
                                <th>Expiry Date</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $subscription)
                                @php($transaction = $subscription->transaction->first())
                                <tr>
                                    <td>{{ $subscriptions->firstItem() + $loop->index }}</td>
                                    <td>{{ $subscription->identifier }}</td>
                                    <td>{{ $subscription->product?->product_name ?? '-' }}</td>
                                    <td>{{ $subscription->rate?->rate_type?->name ?? '-' }}</td>
                                    <td>{{ number_format((float) $subscription->transaction->sum('amount_paid'), 2) }}</td>
                                    <td>{{ $transaction?->receipt ?? '-' }}</td>
                                    <td>{{ $subscription->subscription_date ? \Illuminate\Support\Carbon::parse($subscription->subscription_date)->format('M d, Y H:i') : '-' }}</td>
                                    <td>{{ $subscription->expiry_date ? \Illuminate\Support\Carbon::parse($subscription->expiry_date)->format('M d, Y H:i') : '-' }}</td>
                                    <td>{{ trim(($subscription->user?->name ?? '').' '.($subscription->user?->surname ?? '')) ?: '-' }}</td>
                                    <td>{{ $subscription->user?->email ?? '-' }}</td>
                                    <td>{{ $subscription->status ? 'Active' : 'Inactive' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">No subscriptions found for these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $subscriptions->links() }}
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = {{ \Illuminate\Support\Js::from($chartData) }};

            new window.Chart(document.getElementById('subscriptionDailyChart'), {
                type: 'line',
                data: {
                    labels: chartData.dailySubscriptions.labels,
                    datasets: [{
                        label: 'Subscriptions',
                        data: chartData.dailySubscriptions.data,
                        borderColor: window.theme.primary,
                        backgroundColor: 'rgba(59, 125, 221, 0.12)',
                        pointBackgroundColor: window.theme.primary,
                        fill: true,
                        lineTension: 0.25
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0
                            }
                        }]
                    }
                }
            });

            new window.Chart(document.getElementById('subscriptionStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.subscriptionStatus.labels,
                    datasets: [{
                        data: chartData.subscriptionStatus.data,
                        backgroundColor: [window.theme.success, window.theme.danger],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom'
                    },
                    cutoutPercentage: 70
                }
            });
        });
    </script>
@endsection
