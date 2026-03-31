@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title my-0 text-nation">
                    Sites
                </h3>
                @canaccess('site.create')
                    <a href="{{ route('site.create') }}" class="btn btn-sm btn-outline-nation">
                        <i class="fas fa-plus mr-2"></i> <span>Add Site</span>
                    </a>
                @endcanaccess
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped" id="products-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Products</th>
                                <th>Site URL</th>
                                <th>Region</th>
                                <th>Webhook URL</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Products</th>
                                <th>Site URL</th>
                                <th>Region</th>
                                <th>Webhook URL</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script>
        $('#products-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('site.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "site_name"},
                {"data": "products", "orderable": false},
                {"data": "site_url"},
                {"data": "region", "orderable": false},
                {"data": "callback_url","orderable":false},
                {"data": "action", "orderable": false}

            ],
            "order": [[1, "asc"]]
        });
    </script>
@endsection
