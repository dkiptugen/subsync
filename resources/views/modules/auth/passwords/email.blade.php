@extends('includes.auth_layout')

@section('content')
    <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
        <div class="d-table-cell align-middle">



            <div class="card">
                <div class="card-body pb-5">
                    <div class="m-sm-4">
                        <div class="text-center">
                            <img src="{{ $logo }}" height="100" alt="">
                        </div>
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" class="mt-3" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-3">

                            <div class="input-group">
                                <div class="input-group-text text-black">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <input id="email" placeholder="Enter Email Address" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            </div>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                        </div>

                        <div class="mb-3 d-flex mb-4">

                                <button type="submit" class="btn btn-nation ms-auto">
                                    {{ __('Send Reset Link') }}
                                </button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
