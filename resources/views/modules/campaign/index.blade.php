@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title text-nation my-0">Campaigns</h3>
                <a href="{{ route('campaign.index') }}" class="btn btn-sm btn-outline-nation"><span
                        class="fas fa-plus"></span>Add Campaign</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-condensed" id="campaign-table">
                        <thead class="bg-nation text-white">
                            <th>#</th>
                            <th>Name</th>
                            <th>Product</th>
                            <th>Link</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <th>#</th>
                            <th>Name</th>
                            <th>Product</th>
                            <th>Link</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Action</th>
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
        document.addEventListener('DOMContentLoaded', function () {
        window.renderDataTable('#campaign-table', {
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('campaign.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                { "data": "name" },
                { "data": "product" },
                { "data": "link" },
                { "data": "startdate" },
                { "data": "enddate"},
                { "data": "status"},
                {"data": "action","orderable":false}
            ],
            "order": [[ 1, "asc" ]]
        });
        });
    </script>
@endsection
