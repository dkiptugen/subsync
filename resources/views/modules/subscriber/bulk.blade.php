@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-itens-center">
                <h3 class="card-title my-0 text-nation">Add Subscriber</h3>

            </div>
            <div class="card-body">
                <form action="{{  route('subscribers.upload') }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf

                    <div class="mb-3">
                        <div class="col">
                            <label for="excel_file" class="control-label">Excel file &nbsp; &nbsp; &nbsp; &nbsp;<a href="{{ asset('assets/subscribers.xlsx') }}">Download sample</a></label>
                            <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation ms-auto">Upload Subscribers</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
