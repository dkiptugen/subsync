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
                <div class="card card-border-nation report-panel h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-nation">Daily Registrations</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="registrationDailyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-border-nation report-panel h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-nation">Registration Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart chart-sm">
                            <canvas id="registrationStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-border-nation report-panel report-results-card">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Registration Results</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-hover">
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
                        <tbody>
                            @forelse($subscribers as $subscriber)
                                <tr>
                                    <td>{{ $subscribers->firstItem() + $loop->index }}</td>
                                    <td>{{ trim($subscriber->name.' '.$subscriber->surname) }}</td>
                                    <td>{{ $subscriber->email }}</td>
                                    <td>{{ $subscriber->organization->name }}</td>
                                    <td>{{ $subscriber->status ? 'Active' : 'Inactive' }}</td>
                                    <td>{{ $subscriber->phone ?? '-' }}</td>
                                    <td>{{ $subscriber->providers->pluck('provider')->implode(', ') ?: 'Direct' }}</td>
                                    <td>{{ $subscriber->last_login ? \Illuminate\Support\Carbon::parse($subscriber->last_login)->format('M d, Y H:i') : '-' }}</td>
                                    <td>{{ $subscriber->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No registrations found for this date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $subscribers->links() }}
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = {{ \Illuminate\Support\Js::from($chartData) }};

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
