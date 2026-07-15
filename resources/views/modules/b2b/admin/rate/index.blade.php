@extends('includes.body')
@section('content')
    <div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title my-0 text-nation">
               Corporate Rates
            </h3>
            @canaccess('organization.rate.create')
            <a href="{{ route('organization.rate.create',$organizationId) }}" class="btn btn-sm btn-outline-nation">
                <i class="fas fa-plus"></i><span class="">Add Corporate Rate</span>
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
                            <th>Period</th>
                            <th>Cost</th>
                            <th>Organization</th>
                            <th>Product</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Period</th>
                            <th>Cost</th>
                            <th>Organization</th>
                            <th>Product</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Author</th>
                            <th>Status</th>
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
        document.addEventListener('DOMContentLoaded', function () {
        window.renderDataTable('#rates-table', {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('organization.rate.datatable',$organizationId) }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "name"},
                {"data": "period"},
                {"data": "cost"},
                {"data": "organization"},
                {"data": "product"},
                {"data": "startdate"},
                {"data": "enddate"},
                {"data": "author"},
                {"data": "status"},
                {"data": "action","orderable":false}
            ],
            "order": [[1, "asc"]]
        });
        });
    </script>
@endsection
