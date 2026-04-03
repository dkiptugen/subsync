@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Add User</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('organization.user_save',$organization->id) }}" class="form form-horizontal create-form" method="post">
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
                        <label for="phone" class="control-label">Phone</label>
                        <div class="row">
                            <div class="col-4">
                                <select name="code" class="form-control">
                                    @foreach($codes as $key => $value)
                                        <option value="{{$value}}">
                                            {{ $value." (".$key.")" }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-8">
                                <input type="text" name="phone" id="phone" class="form-control" pattern="[0-9]+" placeholder="Phone number without the leading 0 e.g 722XXXXXX">
                            </div>
                        </div>

                    </div>
                    <div class="mb-3">
                        <label for="password" class="control-label">Password</label>
                        <input type="text" name="password" id="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="control-label">Confirm Password</label>
                        <input type="text" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="changepass" value="1">
                            <span class="form-check-label">
                                Change Password
                            </span>
                        </label>
{{--                        <label class="form-check form-check-inline">--}}
{{--                            <input class="form-check-input" type="checkbox" name="genuine" value="1">--}}
{{--                            <span class="form-check-label">--}}
{{--                                Genuine Email--}}
{{--                            </span>--}}
{{--                        </label>--}}
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="notify" value="1">
                            <span class="form-check-label">
                                Notify
                            </span>
                        </label>
                    </div>
                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-outline-nation ms-auto">
                            Add Organization User
                        </button>
                        <!-- /.btn btn-outline-nation -->
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
