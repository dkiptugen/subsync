@extends('includes.body')
@section('content')
    <div class="col-12">
        @livewire('dashboard')
    </div>

@endsection
@section('header')
    @livewireStyles
@endsection
@section('footer')
    @livewireScripts
    @parent

@endsection
