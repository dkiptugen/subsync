@extends('includes.body')
@section('content')
    <div class="col-12">
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
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title my-0 text-nation">Subscription Report</h3>
                <form action="{{ route('report.subscription') }}" method="POST" class="mb-0">
                    @csrf
                    <input type="hidden" name="startdate" value="{{ $filters['startdate']->toDateString() }}">
                    <input type="hidden" name="enddate" value="{{ $filters['enddate']->toDateString() }}">
                    <input type="hidden" name="status" value="{{ $filters['status'] }}">
                    @foreach($filters['product'] as $productId)
                        <input type="hidden" name="product[]" value="{{ $productId }}">
                    @endforeach
                    @foreach($filters['ratetype'] as $rateTypeId)
                        <input type="hidden" name="ratetype[]" value="{{ $rateTypeId }}">
                    @endforeach
                    <button type="submit" class="btn btn-sm btn-outline-nation">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </form>
            </div>
            <div class="card-body">
                <form action="{{ route('report.subscription_form') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="startdate" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="startdate" id="startdate" class="form-control @error('startdate') is-invalid @enderror" value="{{ old('startdate', $filters['startdate']->toDateString()) }}">
                        @error('startdate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="enddate" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="enddate" id="enddate" class="form-control @error('enddate') is-invalid @enderror" value="{{ old('enddate', $filters['enddate']->toDateString()) }}">
                        @error('enddate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="product" class="form-label">Products</label>
                        <select name="product[]" id="product" class="form-control @error('product') is-invalid @enderror" multiple>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(in_array($product->id, $filters['product'], true))>{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                        @error('product')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="ratetype" class="form-label">Rate Types</label>
                        <select name="ratetype[]" id="ratetype" class="form-control @error('ratetype') is-invalid @enderror" multiple>
                            @foreach($rateTypes as $rateType)
                                <option value="{{ $rateType->id }}" @selected(in_array($rateType->id, $filters['ratetype'], true))>{{ $rateType->name }}</option>
                            @endforeach
                        </select>
                        @error('ratetype')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="">All</option>
                            <option value="active" @selected($filters['status'] === 'active')>Active</option>
                            <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-9 d-flex align-items-end justify-content-end">
                        <button type="submit" class="btn btn-sm btn-outline-nation">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </form>

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
