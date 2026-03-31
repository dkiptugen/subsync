@extends('includes.body')
@section('content')
    <div class="col-12">

        @livewire('registrations-report')
    </div>
@endsection
@section('header')
    @livewireStyles
@endsection
@section('footer')
    @livewireScripts
    @parent

@endsection
