@extends('includes.body')
@php
    $title = $activatedOnly ? 'Activated Accounts Report' : 'Individual Accounts Report';
    $formRoute = $activatedOnly ? route('report.activated_accounts_form') : route('report.accounts_form');
    $exportRoute = $activatedOnly ? route('report.activated_accounts') : route('report.accounts');
@endphp
@section('content')
    <div class="col-12">
        @include('modules.reportds.partials.filter-hero', [
            'title' => $title,
            'formRoute' => $formRoute,
            'exportRoute' => $exportRoute,
            'filters' => $filters,
            'products' => $products,
            'rateTypes' => $rateTypes,
            'showStatus' => true,
        ])

        <div class="row mb-4">
            <div class="col-lg-8">
                                <section class="page-hero">
<h5 class="card-title mb-0 text-nation">Daily Accounts</h5>
                </section>
<div class="card report-panel h-100">

                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="accountDailyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                                <section class="page-hero">
<h5 class="card-title mb-0 text-nation">Account Status</h5>
                </section>
<div class="card report-panel h-100">

                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="accountStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <section class="page-hero">
<h3 class="card-title my-0 text-nation">Account Results</h3>
        </section>
<div class="card report-panel report-results-card">

            <div class="card-body">
                <div class="table-responsive">
                    <table id="account-report-table" class="table table-condensed table-striped table-hover w-100">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Identifier</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Product</th>
                                <th>Subscription Type</th>
                                <th>Amount Paid</th>
                                <th>Sub Date</th>
                                <th>Expiry Date</th>
                                <th>Activated By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
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

            window.renderDataTable('#account-report-table', {
                ajax: {
                    url: "{{ $activatedOnly ? route('report.activated_accounts_datatable') : route('report.accounts_datatable') }}",
                    data: reportFilters
                },
                columns: [
                    {data: 'pos', orderable: false, searchable: false},
                    {data: 'identifier'},
                    {data: 'name', orderable: false},
                    {data: 'email', orderable: false},
                    {data: 'product', orderable: false},
                    {data: 'subscription_type', orderable: false},
                    {data: 'amount_paid', orderable: false, searchable: false},
                    {data: 'subscription_date'},
                    {data: 'expiry_date'},
                    {data: 'activated_by', orderable: false},
                    {data: 'status'}
                ],
                order: [[7, 'desc']],
                fixedHeader: true,
                responsive: true
            });

            new window.Chart(document.getElementById('accountDailyChart'), {
                type: 'line',
                data: {
                    labels: chartData.dailyAccounts.labels,
                    datasets: [{
                        label: 'Accounts',
                        data: chartData.dailyAccounts.data,
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

            new window.Chart(document.getElementById('accountStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.accountStatus.labels,
                    datasets: [{
                        data: chartData.accountStatus.data,
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
