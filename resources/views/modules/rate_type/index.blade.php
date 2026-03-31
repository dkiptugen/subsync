@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title my-0 text-nation">Subscription Types</h3>
                @canaccess('rate_type.create')
                <a href="{{ route('rate_type.create') }}" class="btn btn-outline-nation btn-sm">
                    <i class="fas fa-plus"></i>
                    <span>Add Subscription Type</span>
                </a>
                @endcanaccess
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-hover table-striped" id="rate-type-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Swahili Name</th>
                                <th>Period</th>
                                <th>Days Of Week</th>
                                <th>Status</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                 <th>Swahili Name</th>
                                <th>Period</th>
                                <th>Days Of Week</th>
                                <th>Status</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('header')

@endsection
@section('footer')
    <script>
        $('#rate-type-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('rate_type.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                { "data": "name" },
                { "data": "swahili_name" },
                { "data": "period" },
                { "data": "dow" },
                { "data": "status" },
                { "data": "date_created" },
                { "data": "action","orderable":false }
            ],
            "order": [[ 1, "asc" ]]
        });
    </script>

@endsection
