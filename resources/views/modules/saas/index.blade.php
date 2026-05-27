@extends('includes.body')

@section('content')
    <div class="col-12">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card card-border-nation">
                    <div class="card-body">
                        <div class="text-muted small">Plans</div>
                        <h2 class="mb-0">{{ $metrics['plans'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-border-nation">
                    <div class="card-body">
                        <div class="text-muted small">Features</div>
                        <h2 class="mb-0">{{ $metrics['features'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-border-nation">
                    <div class="card-body">
                        <div class="text-muted small">Entry plan</div>
                        <h2 class="mb-0">{{ $metrics['entry_price'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-border-nation mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title my-0 text-nation">{{ $brand['name'] ?? 'SaaS Product' }}</h3>
                    <p class="text-muted mb-0">{{ $brand['description'] ?? '' }}</p>
                </div>
                <a href="{{ route('landing') }}" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-external-link-alt me-2"></i> Landing page
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($plans as $plan)
                        <div class="col-lg-4">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="text-muted small">{{ $plan['badge'] }}</div>
                                        <h4 class="mb-1">{{ $plan['name'] }}</h4>
                                        <div class="fw-bold">{{ $plan['price'] }} / {{ $plan['interval'] }}</div>
                                    </div>
                                    @if($plan['popular'])
                                        <span class="badge bg-success">Popular</span>
                                    @endif
                                </div>
                                <p class="text-muted mt-3">{{ $plan['summary'] }}</p>
                                <ul class="ps-3 mb-0">
                                    @foreach($plan['features'] as $feature)
                                        <li>{{ $feature['label'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Backend Features</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped">
                        <thead class="bg-nation text-white">
                        <tr>
                            <th>Key</th>
                            <th>Name</th>
                            <th>Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($features as $feature)
                            <tr>
                                <td><code>{{ $feature['key'] }}</code></td>
                                <td>{{ $feature['name'] }}</td>
                                <td>{{ $feature['description'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
