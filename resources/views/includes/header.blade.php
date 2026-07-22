@php use Illuminate\Support\Facades\Auth; @endphp
    <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $title??'' }}</title>
    <link href="{{ asset('/assets/css/app.css?v=1.0.6') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    @include('includes.meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('includes.broadcasting-meta')
    @yield('header')

</head>

<body>
<div class="wrapper">

@include('includes.sidebar')

    <div class="main">
        <nav class="navbar navbar-expand navbar-light navbar-bg">
            <a class="sidebar-toggle js-sidebar-toggle">
                <i class="hamburger align-self-center"></i>
            </a>

            <div class="navbar-collapse collapse">
                <ul class="navbar-nav navbar-align">
                    <li class="nav-item dropdown"
                        id="notification-center"
                        data-user-id="{{ Auth::id() }}"
                        data-index-url="{{ route('notifications.index') }}"
                        data-read-url="{{ route('notifications.read_all') }}">
                        <button class="nav-icon dropdown-toggle btn" type="button" id="alertsDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open notifications">
                            <div class="position-relative">
                                <i class="align-middle" data-feather="bell"></i>
                                <span class="indicator d-none" data-notification-count>0</span>
                            </div>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">
                            <div class="dropdown-menu-header notification-menu-header">
                                <span data-notification-heading>Notifications</span>
                                <button type="button" class="btn btn-link btn-sm" data-notifications-read-all>
                                    Mark all read
                                </button>
                            </div>
                            <div class="list-group" data-notification-list>
                                <div class="notification-empty">
                                    <i data-feather="bell" aria-hidden="true"></i>
                                    <span>No notifications yet</span>
                                </div>
                            </div>
                            <div class="dropdown-menu-footer">
                                <a href="{{ route('profile.index') }}" class="text-muted">View profile settings</a>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                            <i class="align-middle" data-feather="settings"></i>
                        </a>

                        <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                            <img src="{{ asset('assets/img/avatar.png') }}" class="avatar img-fluid rounded me-1" alt="{{ Auth::user()->name }}" /> <span class="text-dark">{{ Auth::user()->name }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('profile.index') }}"><i class="align-middle me-1" data-feather="user"></i> Profile</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">
                                    <i class="align-middle me-1" data-feather="log-out"></i> Log out
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
