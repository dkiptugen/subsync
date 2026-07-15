@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title my-0 text-nation">Registration Report</h3>
                <form action="{{ route('report.subscriber') }}" method="POST" class="mb-0">
                    @csrf
                    <input type="hidden" name="startdate" value="{{ $filters['startdate']->toDateString() }}">
                    <input type="hidden" name="enddate" value="{{ $filters['enddate']->toDateString() }}">
                    <button type="submit" class="btn btn-sm btn-outline-nation">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </form>
            </div>
            <div class="card-body">
                <form action="{{ route('report.subscriber_form') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="startdate" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="startdate" id="startdate" class="form-control @error('startdate') is-invalid @enderror" value="{{ old('startdate', $filters['startdate']->toDateString()) }}">
                        @error('startdate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="enddate" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="enddate" id="enddate" class="form-control @error('enddate') is-invalid @enderror" value="{{ old('enddate', $filters['enddate']->toDateString()) }}">
                        @error('enddate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end justify-content-end">
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
