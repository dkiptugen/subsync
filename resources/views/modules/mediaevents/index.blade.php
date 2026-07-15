@extends('includes.body')
@section('content')
    <div class="col-12">


            <div class="card" id="view-table" aria-labelledby="view-table" >
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title my-0 text-nation">MediaEvents</h3>
                    <a class="btn btn-outline-nation btn-sm"  href="{{ route('media_events.create') }}" >
                        <i class="align-middle" data-feather="plus"></i> Add Name </a>

                </div>
                <div class="card-body">
                    @livewire('media-events-livewire')
                </div>
            </div>

    </div>

    @include('partials._delete-modal')

@endsection
@section("header")

@endsection
@section("footer")
@if(session('msg'))
   <script>
        toastr.success('{{ session("msg")}}','{{ session("header")}}');
    </script>
@endif


    <script>
        $(document).ready(function (){
            $(document).on("click", ".deleteDialog ", function () {
                $('.modal-dialog #action').attr('action', $(this).data('url'));
                $(".modal-body #target").val($(this).data('target'));
                $(".modal").modal('show')
            });
        });
    </script>
@endsection
