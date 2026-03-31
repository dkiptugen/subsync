@extends('includes.layout')

@section('content')
    <div class="row  align-items-center">

            <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 200px;">
                <div class="toast hidden bg-white shadow-sm"  data-animation="true" data-delay="6000" id="toast" style="position: absolute; top: 0; right: 0;">
                    <div class="toast-header">

                        <strong class="mr-auto">DPO</strong>

                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                </div>
            </div>
            <main class=" py-4">
                <div class=" container w-100 h-100">
                    <div class="row ">

                                <div class="card">
                                    <div class="card-body">

                                            <iframe src="{{ $iframe }}" class="w-100 vh-100" height="800" width="500" frameborder="0" allowtransparency="true"></iframe>

                                        <a class="btn btn-primary form-control different" href="javascript:;" data-subid="">Select a different option</a>
                                    </div>
                                </div>

                    </div>
                </div>
            </main>




    </div>
@endsection
