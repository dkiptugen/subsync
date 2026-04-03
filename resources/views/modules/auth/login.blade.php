@extends('includes.auth_layout')

@section('content')
    <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
        <div class="d-table-cell align-middle">



            <div class="card ">

                <div class="card-body border-nation">
                    <div class="m-sm-4">
                        <div class="text-center">
                            <img src="{{ $logo }}" height="100" alt="">
                        </div>
                        <form  method="POST" class="mb-2" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <div class="input-group w-100 mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="text" placeholder="Enter your email or username" class=" form-control-lg form-control @error('email') is-invalid @enderror @error('username') is-invalid @enderror" name="email" value="{{ old('email')??old('username') }}" required autocomplete="email" autofocus />
                                </div>
                                @error('email')
                                    <div class="text-center text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <div class="input-group w-100 mb-3 border-nation">
                                    <span class="input-group-text" id="basic-addon1">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" type="password" placeholder="Enter your password" />
                                </div>

                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                            </div>
                            <div class="d-flex justify-content-between mb-3 w-100">
                                <div class="form-check align-items-center">
                                    <input type="checkbox" class="form-check-input" id="remember-me" value="remember-me" name="remember-me" checked>
                                    <label class="form-check-label text-small" for="remember-me">Remember me next time.</label>
                                </div>
                                <small>
                                    <a href="{{ route('password.request') }}">Forgot password?</a>
                                    
                                </small>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn w-100 btn-nation">Sign in</button>
                            </div>
                        </form>
                        {{-- <div class="social-login-separator my-3">
                            <span>OR</span>
                        </div>
                        <div class="btn-group btn-group-md btn-group-lg w-100">
                            <a href="" class="btn btn-facebook ">
                                <i class="fab fa-facebook-f"></i>
                                Facebook
                            </a>
                            <a href="" class="btn btn-google">
                                <i class="fab fa-google"></i>
                                Google
                            </a>
                            <a href="" class="btn btn-twitter">
                                <i class="fab fa-twitter"></i>
                                Twitter
                            </a>
                        </div> --}}
                    </div>
                </div>
            </div>
            @if($message = Session::get('error'))
                <div class="alert alert-danger alert-block">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <strong>{{ $message }}</strong>
                </div>
            @endif
        </div>
    </div>




@endsection
