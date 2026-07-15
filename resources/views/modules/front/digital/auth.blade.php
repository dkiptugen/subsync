@extends('modules.front.digital.layout')

@section('title', 'Digital Access')

@section('content')
    <section class="digital-hero">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Digital access</li>
                </ol>
            </nav>
            <p class="digital-eyebrow">Unified authentication</p>
            <h1>One secure account for every digital product.</h1>
            <p class="digital-hero__copy">Sign in once to manage profile details, payments, subscriptions, and product access across the platform.</p>
        </div>
        <div class="digital-auth-panel">
            @auth
                <h2>Continue as {{ auth()->user()->name }}</h2>
                <p>Manage your access and payments from your digital profile.</p>
                <div class="d-grid gap-2">
                    <a class="btn btn-dark" href="{{ route('digital.profile') }}">Open profile</a>
                    <a class="btn btn-outline-dark" href="{{ route('digital.payments') }}">View payment options</a>
                </div>
            @else
                <h2>Sign in</h2>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email or username</label>
                        <input type="text" class="form-control @error('email') is-invalid @enderror @error('username') is-invalid @enderror" id="email" name="email" value="{{ old('email') ?? old('username') }}" autocomplete="email" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="current-password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Sign in</button>
                </form>
            @endauth
        </div>
    </section>

    <section class="digital-section">
        <div class="digital-section__header">
            <h2>Available products</h2>
            <a href="{{ route('digital.payments') }}" class="btn btn-outline-dark btn-sm">View payments</a>
        </div>
        <div class="digital-product-grid">
            @forelse($products as $product)
                <article class="digital-product">
                    <span>{{ $product->identifier }}</span>
                    <h3>{{ $product->product_name }}</h3>
                    <p>{{ $product->type ?? 'Digital subscription' }}</p>
                </article>
            @empty
                <p class="text-muted mb-0">No active digital products are available.</p>
            @endforelse
        </div>
    </section>
@endsection
