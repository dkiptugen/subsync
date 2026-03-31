@extends('auth.layout')
@section('content')
    <div class="row h-100">
        <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
            <div class="d-table-cell align-middle">

                <div class="text-center mt-4">
                    <h2>{{ __('Login') }}</h2>
                    <p class="lead">
                        Sign in to your account to continue
                    </p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="m-sm-4">

                            <form method="post" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group">
                                    <label> {{ __('Username/Email') }}</label>
                                    <input class="form-control form-control-lg  @error('username') is-invalid @enderror   @error('email') is-invalid @enderror" type="text" name="email" value="{{ old('email') }}" placeholder="Enter your email or username"/>
                                    @error('username')
                                    <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                    @enderror
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Password') }}</label>
                                    <input class="form-control form-control-lg @error('password') is-invalid @enderror"
                                           type="password" name="password" value="{{ old('password') }}"
                                           placeholder="Enter your password"/>
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                    @enderror
                                    <div class="mt-2">
                                        <small>
                                            <a href="{{ url('changepass') }}">Forgot password?</a>
                                        </small>
                                    </div>
                                </div>

                                <div>
                                    <div class="form-check align-items-center">
                                        <input type="checkbox" class="form-check-input" value="1" name="remember">
                                        <label class="form-check-label">Remember me next time</label>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-lg btn-primary">Sign in</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
