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
                                <section class="page-hero">
<h5 class="card-title mb-0 text-nation">Daily Revenue</h5>
                </section>
<div class="card report-panel h-100">

                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="revenueDailyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                                <section class="page-hero">
<h5 class="card-title mb-0 text-nation">Revenue By Channel</h5>
                </section>
<div class="card report-panel h-100">

                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="revenueChannelChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<div class="row mt-4">
<div class="card report-panel report-results-card">

            <div class="card-body">
                <div class="table-responsive">
                    <table id="revenue-report-table" class="table table-condensed table-striped table-hover w-100">
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
                    </table>
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

            window.renderDataTable('#revenue-report-table', {
                ajax: {
                    url: "{{ route('report.revenue_datatable') }}",
                    data: reportFilters
                },
                columns: [
                    {data: 'pos', orderable: false, searchable: false},
                    {data: 'identifier'},
                    {data: 'receipt'},
                    {data: 'name', orderable: false},
                    {data: 'email', orderable: false},
                    {data: 'product', orderable: false},
                    {data: 'channel'},
                    {data: 'amount_paid'},
                    {data: 'transaction_date'},
                    {data: 'status'}
                ],
                order: [[8, 'desc']],
                fixedHeader: true,
                responsive: true
            });

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
