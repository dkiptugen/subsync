@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Edit User</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('organization.user_update',[$organization->id,$user->id]) }}" class="form form-horizontal create-form" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="name" class="control-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{$user->name}}">
                    </div>
                    <div class="form-group">
                        <label for="email" class="control-label">Email</label>
                        <input type="text" name="email" id="email" class="form-control" value="{{$user->email}}">
                    </div>
                    <div class="form-group">
                        <label for="phone" class="control-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" pattern="?\+[0-9]+" placeholder="Phone number without the leading 0 e.g 722XXXXXX" value="{{$user->phone}}">
                    </div>
                    <div class="form-group">
                        <label for="password" class="control-label">Password (*Leave blank if not changing)</label>
                        <input type="text" name="password" id="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="control-label">Confirm Password</label>
                        <input type="text" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="notify" value="1"
                             @if($user->daily_notifications == 1) checked @endif >
                            <span class="form-check-label">
                                Notify
                            </span>
                        </label>
                    </div>
                    <div class="form-group d-flex">
                        <button type="submit" class="btn btn-outline-nation ml-auto">
                            Update Organization User
                        </button>
                        <!-- /.btn btn-outline-nation -->
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
