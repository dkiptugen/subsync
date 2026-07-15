@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Assign User</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('organization.subscription.assign_user',[$organization->id,$subscription->id]) }}" class="form form-horizontal create-form" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="control-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="control-label">Email</label>
                        <input type="text" name="email" id="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="control-label">Password</label>
                        <input type="text" name="password" id="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="control-label">Confirm Password</label>
                        <input type="text" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>
                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-outline-nation ms-auto">
                            Assign Organizational User
                        </button>
                        <!-- /.btn btn-outline-nation -->
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
