<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', config('app.name').' Digital Access')</title>
    <link href="{{ asset('assets/css/app.css?v='.(file_exists(public_path('assets/css/app.css')) ? filemtime(public_path('assets/css/app.css')) : time())) }}" rel="stylesheet">
</head>
<body class="digital-access">
<nav class="digital-nav">
    <a href="{{ route('digital.auth') }}" class="digital-brand">
        @if(!empty($logo))
            <img src="{{ $logo }}" alt="{{ config('app.name') }}">
        @else
            <span>{{ config('app.name') }}</span>
        @endif
    </a>
    <div class="digital-nav__links">
        <a href="{{ route('digital.auth') }}" @class(['active' => request()->routeIs('digital.auth', 'digital.home')])>Access</a>
        @auth
            <a href="{{ route('digital.profile') }}" @class(['active' => request()->routeIs('digital.profile')])>Profile</a>
            <a href="{{ route('digital.payments') }}" @class(['active' => request()->routeIs('digital.payments')])>Payments</a>
        @endauth
    </div>
    <div class="digital-nav__actions">
        @auth
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline-dark btn-sm" type="submit">Sign out</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-dark btn-sm">Sign in</a>
        @endauth
    </div>
</nav>

<main>
    @yield('content')
</main>

<script src="{{ asset('assets/js/app.js?v='.(file_exists(public_path('assets/js/app.js')) ? filemtime(public_path('assets/js/app.js')) : time())) }}" type="module"></script>
</body>
</html>
