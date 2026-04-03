@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation w-100">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title my-0 text-nation">Subscription Approval</h3>

            </div>
            <div class="card-body">
                <div class="table-responsive table-responsive-lg table-responsive-md table-responsive-sm table-responsive-xl table-responsive-xxl ">
                    <table class="table table-condensed table-striped table-hover " id="subscription-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Identifier</th>
                                <th>Product</th>
                                <th>Subscription Type</th>
                                <th>Transactions</th>
                                <th>Unit Cost</th>
                                <th>Amount Paid</th>
                                <th>Recurrent</th>
                                <th>Sub Date</th>
                                <th>Expiry Date</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Identifier</th>
                                <th>Product</th>
                                <th>Subscription Type</th>
                                <th>Transactions</th>
                                <th>Unit Cost</th>
                                <th>Amount Paid</th>
                                <th>Recurrent</th>
                                <th>Sub Date</th>
                                <th>Expiry Date</th>
                                <th>Email</th>
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
        window.renderDataTable('#subscription-table', {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('subscription-approval.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "identifier"},
                {"data": "product"},
                {"data": "st"},
                {"data": "transactions"},
                {"data": "cost"},
                {"data": "amount_paid"},
                {"data": "recurrent"},
                {"data": "subdate"},
                {"data": "expirydate"},
                {"data": "email"},
                {"data": "status"},
                {"data": "action","orderable":false}
            ],
            "order": [[7, "desc"]],

        });
        });
    </script>
@endsection
