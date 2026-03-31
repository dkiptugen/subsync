@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex align-item-center justify-content-between">
                <h3 class="my-0 card-title text-nation">Import Individual Accounts</h3>

            </div>
            <div class="card-body">
                <div id="dropzone" class="d-flex align-items-center justify-content-center" data-endpoint="{{ route('migindividuals.store') }}">
                    <div class="">
                        <p>Drag and drop files here, or click to select files.
                            <br>
                            <a href="{{ asset('individual.xlsx') }}">Download Sample excel Sheet</a>
                        </p>

                        <input type="file" id="fileInput" multiple data-endpoint="{{ route('migindividuals.store') }}">
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
