@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex justify-content-between align-items-center">
<h3 class="card-title my-0 text-nation">Subscriptions</h3>
                <a href="" class="btn btn-sm btn-outline-nation"><i class="fas fa-check me-2"></i>Subscribe</a>
        </section>
<div class="card">

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-hover" id="subscriptions-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>No Of Users</th>
                                <th>Added Users</th>
                                <th>Is Paid</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">

                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>No Of Users</th>
                            <th>Added Users</th>
                            <th>Is Paid</th>
                            <th>Status</th>
                            <th>Created At</th>
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
        window.renderDataTable('#subscriptions-table', {
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('client_subscription.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },

                { "data": "product" },
                { "data": "subdate" },
                { "data": "expirydate" },
                { "data": "users" },
                { "data": "assigned" },

                { "data": "paid" },



                { "data": "status" },
                { "data": "created_at" },
                {"data": "action","orderable":false}
            ],
            "order": [[ 8, "desc" ]]
        });
        });
    </script>
@endsection
