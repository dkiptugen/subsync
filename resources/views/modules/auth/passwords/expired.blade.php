@extends('includes.auth_layout')

@section('content')
    <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
        <div class="d-table-cell align-middle">
            <div class="card">


                <div class="card-body">
                    <div class="m-sm-4">
                        <div class="text-center">
                            <img src="{{ $logo }}" width="154" alt="">
                        </div>


                    <form class="form form-horizontal create-form" method="POST" action="{{ route('password.post_expired') }}">
                        @csrf

                        <div class="mb-3{{ $errors->has('current_password') ? ' has-error' : '' }}">
                            <label for="current_password" class="control-label">Current Password</label>
                            <input id="current_password" type="password" class="form-control" name="current_password" required="">

                            @if ($errors->has('current_password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('current_password') }}</strong>
                                </span>
                            @endif

                        </div>

                        <div class="mb-3{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="control-label">New Password</label>
                            <input id="password" type="password" class="form-control" name="password" required="">

                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif

                        </div>

                        <div class="mb-3{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label for="password-confirm" class="control-label">Confirm New Password</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required="">
                            @if ($errors->has('password_confirmation'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                </span>
                            @endif

                        </div>

                        <div class="mb-3 ">
                            <button type="submit" class="btn btn-nation w-100">
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
