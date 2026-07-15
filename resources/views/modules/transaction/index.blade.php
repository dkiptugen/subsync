@extends('includes.body')
@section('content')
    <div class="col-12">
        @if(Session::has('message'))
            <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
        @endif
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="my-0 card-title text-nation">Transactions</h3>
                <a href="" class="btn btn-sm btn-outline-nation">
                    <i class="fas fa-plus"></i> Add Transaction
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condesed table-striped table-hover" id="transaction-table">
                        <thead class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Receipt</th>
                            <th>Payment Channel</th>
                            <th>Pay Account</th>
                            <th>Total Cost</th>
                            <th>Amount Paid</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Receipt</th>
                            <th>Payment Channel</th>
                            <th>Pay Account</th>
                            <th>Total Cost</th>
                            <th>Amount Paid</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Date</th>
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
        window.renderDataTable('#transaction-table', {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('subscription.transaction.datatable',$subscription_id) }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos","name": "pos"},
                {"data": "receipt","name": "receipt"},
                {"data": "channel","name": "channel"},
                {"data": "identifier","name": "identifier"},
                {"data": "amount","name": "amount"},
                {"data": "amount_paid","name": "amount_paid"},
                {"data": "name","name": "name","orderable": false},
                {"data": "email","name": "email","orderable": false},
                {"data": "time","name": "time"},
                {"data": "status","name": "status"},
                {"data": "action","name": "action","orderable": false}
            ],
            "order": [[0, "desc"]]
        });
        });
    </script>
@endsection
