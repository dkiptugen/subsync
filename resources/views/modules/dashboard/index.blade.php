@extends('includes.body')
@section('content')
    <div class="dashboard-shell">



        <section class="dashboard-hero" aria-labelledby="dashboard-title">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <span class="dashboard-kicker">Subscription command center</span>
                    <span class="dashboard-live-status" data-realtime-status>
                        <span class="dashboard-live-indicator" aria-hidden="true"></span>
                        <span data-realtime-status-label>Connecting</span>
                    </span>
                </div>
                <h1 id="dashboard-title" class="dashboard-title">Dashboard</h1>
                <p class="dashboard-copy mb-0">
                    Track revenue, approvals, subscribers, and product readiness from one operational view.
                </p>
            </div>
            <div class="dashboard-hero-actions" aria-label="Dashboard actions">
                @foreach($dashboard['quickActions'] as $action)
                    <a href="{{ $action['route'] }}" class="btn btn-light dashboard-action">
                        <i data-feather="{{ $action['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $action['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <x-dashboard.realtime :dashboard="$dashboard" />
    </div>
@endsection
@section('header')

@endsection
@section('footer')

@endsection
