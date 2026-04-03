@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="my-0 text-nation card-title">Organizational Transactions</h3>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-hover table-striped" id="transaction-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Organization</th>
                                <th>Product</th>
                                <th>Receipt</th>
                                <th>Pay Channel</th>
                                <th>Total Amount</th>
                                <th>Amount Paid</th>
                                <th>Date Paid</th>
                                <th>Agent</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Organization</th>
                                <th>Product</th>
                                <th>Receipt</th>
                                <th>Pay Channel</th>
                                <th>Total Amount</th>
                                <th>Amount Paid</th>
                                <th>Date Paid</th>
                                <th>Agent</th>
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
        window.renderDataTable('#transaction-table', {
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('organization.transaction.datatable',$organizationId) }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                { "data": "organization" },
                { "data": "product" },
                { "data": "receipt" },
                { "data": "channel" },
                { "data": "amount" },
                { "data": "amount_paid" },
                { "data": "date_paid" },
                { "data": "agent" },
                {"data": "action","orderable":false}
            ],
            "order": [[ 0, "desc" ]]
        });
        });
    </script>
@endsection
