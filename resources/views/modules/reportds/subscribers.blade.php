@extends('includes.body')
@section('content')
    <div class="col-12">
        @include('modules.reportds.partials.filter-hero', [
            'title' => 'Registration Report',
            'formRoute' => route('report.subscriber_form'),
            'exportRoute' => route('report.subscriber'),
            'filters' => $filters,
        ])

        <div class="row mb-4">
            <div class="col-lg-8">
                                <section class="page-hero">
<h5 class="card-title mb-0 text-nation">Daily Registrations</h5>
                </section>
<div class="card report-panel h-100">

                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="registrationDailyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                                <section class="page-hero">
<h5 class="card-title mb-0 text-nation">Registration Status</h5>
                </section>
<div class="card report-panel h-100">

                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="registrationStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>


<div class="card report-panel report-results-card  mt-4">

            <div class="card-body">
                <div class="table-responsive">
                    <table id="registration-report-table" class="table table-condensed table-striped table-hover w-100">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Organization</th>
                                <th>Status</th>
                                <th>Phone Number</th>
                                <th>Login Type</th>
                                <th>Last Login</th>
                                <th>Registration Date</th>
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

            window.renderDataTable('#registration-report-table', {
                ajax: {
                    url: "{{ route('report.subscriber_datatable') }}",
                    data: reportFilters
                },
                columns: [
                    {data: 'pos', orderable: false, searchable: false},
                    {data: 'name'},
                    {data: 'email'},
                    {data: 'organization', orderable: false},
                    {data: 'status'},
                    {data: 'phone', orderable: false},
                    {data: 'login_type', orderable: false},
                    {data: 'last_login'},
                    {data: 'created_at'}
                ],
                order: [[8, 'desc']],
                fixedHeader: true,
                responsive: true
            });

            new window.Chart(document.getElementById('registrationDailyChart'), {
                type: 'line',
                data: {
                    labels: chartData.dailyRegistrations.labels,
                    datasets: [{
                        label: 'Registrations',
                        data: chartData.dailyRegistrations.data,
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

            new window.Chart(document.getElementById('registrationStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.registrationStatus.labels,
                    datasets: [{
                        data: chartData.registrationStatus.data,
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
