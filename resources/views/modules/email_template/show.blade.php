@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="mt-3">
            <a href="{{ route('email_template.index') }}" class="btn btn-nation">Go Back</a>
        </div>
               @include('mail.custom_email')
          
    </div>
@endsection
