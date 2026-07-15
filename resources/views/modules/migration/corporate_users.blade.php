@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex align-item-center justify-content-between">
                <h3 class="my-0 card-title text-nation">Import Corporate User Subscriptions</h3>

            </div>
            <div class="card-body">
                <form action="{{ route('migorganizationusers.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="dropzone" class="d-flex align-items-center justify-content-center">
                        <div class="">
                        <p>Drag and drop files here, or click to select files.
                            <br>
                            <a href="{{ asset('corporate_users.xlsx') }}">Download Sample excel Sheet</a>
                        </p>

                            <input type="file" id="fileInput" name="files[]" class="form-control" multiple required>
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-sm btn-outline-nation">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
