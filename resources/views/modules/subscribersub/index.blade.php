@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title my-0 text-nation">{{ $user->name.' '.$user->surname }} - Subscriptions</h3>
                @canaccess('user.subscription.create')
                <a href="{{ route('user.subscription.create',$user->id) }}" class="btn btn-outline-nation btn-sm">
                    <i class="fas fa-plus"></i>
                    Add Subscription
                </a>
                @endcanaccess
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
                                <th>Receipt</th>
                                <th>Recurrent</th>
                                <th>Sub Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Last Modified</th>
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
                                <th>Receipt</th>
                                <th>Recurrent</th>
                                <th>Sub Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Last Modified</th>
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
        document.addEventListener('DOMContentLoaded', function () {
        window.renderDataTable('#subscription-table', {
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('user.subscription.datatable',$user->id) }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                { "data": "identifier" },
                { "data": "product" },
                {"data": "st"},
                { "data": "transactions" },
                { "data": "cost" },
                { "data": "amount_paid" },
                { "data": "receipt"},
                { "data": "recurrent" },
                { "data": "subdate" },
                { "data": "expirydate" },
                { "data": "status" },
                { "data": "updatedate" },
                {"data": "action","orderable":false}
            ],
            "order": [[ 8, "desc" ]]
        });
        });
    </script>
@endsection
