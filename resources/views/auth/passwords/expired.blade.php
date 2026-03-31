@extends('auth.layout')

@section('content')
    <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
        <div class="d-table-cell align-middle">
            <div class="card">


                <div class="card-body">
                    <div class="m-sm-4">



                    <form class="form form-horizontal" method="POST" action="{{ route('auth.password.post_expired') }}">
                        @csrf

                        <div class="form-group{{ $errors->has('current_password') ? ' has-error' : '' }}">
                            <label for="current_password" class="control-label">Current Password</label>
                            <input id="current_password" type="password" class="form-control" name="current_password" required="" value="{{ old("current_password") }}">

                            @if ($errors->has('current_password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('current_password') }}</strong>
                                </span>
                            @endif

                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="control-label">New Password</label>
                            <input id="password" type="password" class="form-control" name="password" required="" value="{{ old("password") }}">

                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif

                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label for="password-confirm" class="control-label">Confirm New Password</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required="" value="{{ old("password_confirmation") }}">
                            @if ($errors->has('password_confirmation'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                </span>
                            @endif

                        </div>

                        <div class="form-group ">
                            <button type="submit" class="btn btn-blue w-100">
                            {{ __('Reset Password') }}
                            </button>
                        </div>
                    </form>
                    </div>
                </div>



            </div>
        </div>
    </div>

@endsection
