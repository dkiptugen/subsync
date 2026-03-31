@extends('includes.body')
@section('content')
    <div id="accordion">
        @php($x=1)
        @foreach($config as $key => $value)
        <div class="card card-border-nation">
            <div class="card-header" id="headingOne">
              <h3 class="mb-0 card-title text-nation my-0" data-toggle="collapse" data-target="#{{$key}}" aria-expanded="true"
                  aria-controls="collapseOne">
                    {{$key}}
              </h3>
            </div>

            <div id="{{$key}}" class="collapse @if($x == 1)show @endif" aria-labelledby="headingOne" data-parent="#accordion">
              <div class="card-body">
                  <form action="{{ route('configuration.edit') }}" method="post" class="form form form-horizontal create-form">
                      @csrf
                      @foreach($config[$key] as $ob => $val)
                      <div class="form-group">
                          <label for="{{ $ob }}" class="control-label">{{ $ob }}</label>
                          <input type="text" name="{{ $ob }}" id="{{ $ob }}" class="form-control" value="{{ $val }}">
                      </div>
                      @endforeach
                          <div class="form-group d-flex">
                          <button type="submit" class="btn btn-sm btn-nation ml-auto">
                              Save configuration
                          </button>
                          </div>
                  </form>
              </div>
            </div>
          </div>
            @php($x++)
        @endforeach
        </div>
@endsection
