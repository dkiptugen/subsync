@extends('includes.body')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Plugins</h1>
            <p class="text-muted mb-0">Upload a plugin archive, install it, and manage enabled modules from one place.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-4">
                        <section class="page-hero">
<h2 class="h5 mb-0">Install Plugin</h2>
            </section>
<div class="card h-100">

                <div class="card-body">
                    <form method="POST" action="{{ route('plugins.upload') }}" enctype="multipart/form-data" class="d-grid gap-3">
                        @csrf

                        <div>
                            <label for="package" class="form-label">Plugin archive</label>
                            <input
                                id="package"
                                name="package"
                                type="file"
                                class="form-control"
                                accept=".zip,.tar,application/zip,application/x-tar"
                                required
                            >
                            <div class="form-text">Accepted formats: <code>.zip</code> and <code>.tar</code>.</div>
                        </div>

                        <div class="form-check">
                            <input
                                id="enable"
                                name="enable"
                                type="checkbox"
                                value="1"
                                class="form-check-input"
                                checked
                            >
                            <label class="form-check-label" for="enable">
                                Install and enable immediately
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Upload Plugin
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
                        <section class="page-hero d-flex align-items-center justify-content-between">
<h2 class="h5 mb-0">Installed Plugins</h2>
                    <span class="badge bg-secondary">{{ count($plugins) }}</span>
            </section>
<div class="card h-100">

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="plugins-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Provider</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($plugins as $plugin)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $plugin['name'] }}</div>
                                            <div class="text-muted small">{{ $plugin['directory'] }}</div>
                                        </td>
                                        <td>
                                            @if ($plugin['enabled'])
                                                <span class="badge bg-success">Enabled</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Disabled</span>
                                            @endif

                                            @if (($plugin['licensed'] ?? true) === false)
                                                <span class="badge bg-danger">License required</span>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $plugin['provider'] ?: 'No provider' }}</code>
                                        </td>
                                        <td class="text-end">
                                            @if ($plugin['enabled'])
                                                <form method="POST" action="{{ route('plugins.disable') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="plugins" value="{{ $plugin['directory'] }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Disable
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('plugins.install') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="plugins" value="{{ $plugin['directory'] }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        Install & Enable
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No plugins found yet. Upload a plugin archive to get started.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new window.Datatable('#plugins-table', {});
        });
    </script>
@endsection
