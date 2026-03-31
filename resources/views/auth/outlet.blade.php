@extends('auth.layout')

@section('content')
    <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
        <div class="d-table-cell align-middle">


            <div class="card">
                <div class="card-body">
                    <div class="m-sm-4">
                        <div class="text-center">
                            <img src="{{ asset('assets/img/logo-d.png') }}" width="154" alt="">
                        </div>
                        <form method="POST" action="{{ route('outlet.select') }}" class="form form-horizontal">
                            @csrf

                            <div class="form-group">
                                <label for="product" class="control-label">{{ __('Select Outlet') }}</label>
                                <select class="form-select" name="product" id="product" autocomplete="product">
                                    @foreach($product as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-black w-100">Proceed</button>
                            </div>
                        </form>
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
