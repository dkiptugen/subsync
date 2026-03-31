@extends('includes.body')
@section('content')
<div class="col-12">
    <div class="card card-border-nation">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title my-0 text-nation">
                Rates
            </h3>
            @canaccess('product.rate.create')
            <a href="{{ route('product.rate.create',$productid) }}" class="btn btn-sm btn-outline-nation">
                <i class="fas fa-plus"></i><span class="">Add Rate</span>
            </a>
            @endcanaccess
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-condensed" id="rates-table">
                    <thead class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Swahili_name</th>
                            <th>Period</th>
                            <th>Editions</th>
                            <th>Cost</th>
                            <th>Product</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Is Best Value</th>
                            <th>List Order</th>
                            <th>Compensation Days</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                             <th>Swahili_name</th>
                            <th>Period</th>
                            <th>Editions</th>
                            <th>Cost</th>
                            <th>Product</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Is Best Value</th>
                            <th>List Order</th>
                            <th>Compensation Days</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
@section("footer")
    <script>
        $('#rates-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('product.rate.datatable',$productid) }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                { "data": "name" },
                { "data": "swahili_name" },
                { "data": "period" },
                { "data": "editions","orderable":false },
                { "data": "cost" },
                { "data": "product" },
                { "data": "startdate" },
                { "data": "enddate" },
                { "data": "author" },
                { "data": "status" },
                { "data": "best_value" },
                { "data": "listorder" },
                { "data": "compensation_days" },
                { "data": "action","orderable":false }
            ],
            "order": [[ 1, "asc" ]]
        });
    </script>
@endsection
