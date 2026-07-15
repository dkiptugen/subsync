@extends('includes.body')
@section('content')
    <div class="col-12">
        @include('modules.reportds.partials.filter-hero', [
            'title' => 'Revenue Report',
            'formRoute' => route('report.revenue_form'),
            'exportRoute' => route('report.revenue'),
            'filters' => $filters,
            'products' => $products,
            'rateTypes' => $rateTypes,
            'showStatus' => true,
            'statusLabels' => [
                'active' => 'Successful',
                'inactive' => 'Failed',
            ],
        ])

        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card card-border-nation report-panel h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-nation">Daily Revenue</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="revenueDailyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-border-nation report-panel h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-nation">Revenue By Channel</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="revenueChannelChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-border-nation report-panel report-results-card">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Revenue Results</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-hover">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Identifier</th>
                                <th>Receipt</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Product</th>
                                <th>Channel</th>
                                <th>Amount Paid</th>
                                <th>Transaction Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transactions->firstItem() + $loop->index }}</td>
                                    <td>{{ $transaction->identifier }}</td>
                                    <td>{{ $transaction->receipt ?? '-' }}</td>
                                    <td>{{ trim(($transaction->user?->name ?? '').' '.($transaction->user?->surname ?? '')) ?: '-' }}</td>
                                    <td>{{ $transaction->user?->email ?? '-' }}</td>
                                    <td>{{ $transaction->subscription?->product?->product_name ?? '-' }}</td>
                                    <td>{{ $transaction->channel ?? $transaction->payment_method?->name ?? '-' }}</td>
                                    <td>{{ trim(($transaction->currency ?? '').' '.number_format((float) $transaction->amount_paid, 2)) }}</td>
                                    <td>{{ $transaction->transaction_date ? \Illuminate\Support\Carbon::parse($transaction->transaction_date)->format('M d, Y H:i') : '-' }}</td>
                                    <td>{{ $transaction->status ? 'Successful' : 'Failed' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No revenue found for these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = {{ \Illuminate\Support\Js::from($chartData) }};

            new window.Chart(document.getElementById('revenueDailyChart'), {
                type: 'line',
                data: {
                    labels: chartData.dailyRevenue.labels,
                    datasets: [{
                        label: 'Revenue',
                        data: chartData.dailyRevenue.data,
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
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });

            new window.Chart(document.getElementById('revenueChannelChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.channelRevenue.labels,
                    datasets: [{
                        data: chartData.channelRevenue.data,
                        backgroundColor: [
                            window.theme.primary,
                            window.theme.success,
                            window.theme.warning,
                            window.theme.info,
                            window.theme.secondary,
                            window.theme.danger
                        ],
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
