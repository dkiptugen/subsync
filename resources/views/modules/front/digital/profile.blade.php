@extends('modules.front.digital.layout')

@section('title', 'Digital Profile')

@section('content')
    <section class="digital-hero digital-hero--compact">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('digital.auth') }}">Digital access</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav>
            <p class="digital-eyebrow">Profile</p>
            <h1>{{ $user->name }}</h1>
            <p class="digital-hero__copy">{{ $user->email }}{{ $user->organization ? ' · '.$user->organization->name : '' }}</p>
        </div>
        <div class="digital-summary">
            <span>Subscriptions</span>
            <strong>{{ $subscriptions->count() }}</strong>
        </div>
    </section>

    <section class="digital-section">
        <div class="digital-profile-grid">
            <div class="digital-panel">
                <h2>Account details</h2>
                <dl class="digital-details">
                    <div><dt>Name</dt><dd>{{ $user->name }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                    <div><dt>Phone</dt><dd>{{ $user->phone ?? 'Not set' }}</dd></div>
                    <div><dt>Organization</dt><dd>{{ $user->organization->name ?? 'Individual account' }}</dd></div>
                </dl>
            </div>
            <div class="digital-panel">
                <div class="digital-section__header">
                    <h2>Recent subscriptions</h2>
                    <a href="{{ route('digital.payments') }}" class="btn btn-dark btn-sm">Pay</a>
                </div>
                <div class="digital-subscriptions">
                    @forelse($subscriptions as $subscription)
                        <div class="digital-subscription">
                            <div>
                                <strong>{{ $subscription->product->product_name ?? 'Product' }}</strong>
                                <span>{{ $subscription->rate->name ?? 'Standard rate' }}</span>
                            </div>
                            <span class="badge text-bg-light">{{ $subscription->expiry_date ?? 'Active' }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No subscriptions found for this profile.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
