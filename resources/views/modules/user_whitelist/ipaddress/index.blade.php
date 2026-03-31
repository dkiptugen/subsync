@extends('includes.body')
@section('content')
    <div class="col">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title my-0 text-nation">IP Address Whitelist</h3>
                @canaccess('whitelist.type.create')
                <a href="{{ route('whitelist.type.create','ipaddress') }}" class="btn btn-outline-nation btn-sm">
                    Whitelist IP Address
                </a>
                @endcanaccess
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-hover" id="whitelist-table">
                        <thead class="text text-white bg-nation">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>IP Address</th>
                                <th>Product</th>
                                <th>Reason</th>
                                <th>Creator</th>
                                <th>Start Date</th>
                                <th>End date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="text text-white bg-nation">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>IP Address</th>
                                <th>Product</th>
                                <th>Reason</th>
                                <th>Creator</th>
                                <th>Start Date</th>
                                <th>End date</th>
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
        $('#whitelist-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('whitelist.type.datatable','ipaddress') }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "name"},
                {"data": "ipaddress"},
                {"data": "product"},
                {"data": "reason"},
                {"data": "author"},
                {"data": "startdate"},
                {"data": "enddate"},
                {"data": "action","orderable":false}
            ],
            "order": [[1, "asc"]]
        });
    </script>
@endsection
