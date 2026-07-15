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
                <div class="card report-panel h-100">
                    <div class="report-panel-title">
                        <h5>Daily Subscriptions</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="subscriptionDailyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card report-panel h-100">
                    <div class="report-panel-title">
                        <h5>Subscription Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="subscriptionStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
            <div class="card report-panel report-results-card">
                <div class="report-panel-title">
                    <h5>Subscription Results</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="subscription-report-table"
                               class="table table-condensed table-striped table-hover w-100">
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
                        </table>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = {{ \Illuminate\Support\Js::from($chartData) }};
            const reportFilters = {{ \Illuminate\Support\Js::from(request()->query()) }};

            window.renderDataTable('#subscription-report-table', {
                ajax: {
                    url: "{{ route('report.subscription_datatable') }}",
                    data: reportFilters
                },
                columns: [
                    {data: 'pos', orderable: false, searchable: false},
                    {data: 'identifier'},
                    {data: 'product', orderable: false},
                    {data: 'subscription_type', orderable: false},
                    {data: 'amount_paid', orderable: false, searchable: false},
                    {data: 'receipt', orderable: false},
                    {data: 'subscription_date'},
                    {data: 'expiry_date'},
                    {data: 'name', orderable: false},
                    {data: 'email', orderable: false},
                    {data: 'status'}
                ],
                order: [[6, 'desc']],
                fixedHeader: true,
                responsive: true
            });

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
