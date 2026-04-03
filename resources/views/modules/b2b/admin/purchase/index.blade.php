@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="my-0 text-nation card-title">Organizational Purchase Order</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-hover table-striped" id="purchase_table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Order No</th>
                                <th>Organization</th>
                                <th>Products</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Date Initiated</th>
                                <th>Initiator</th>
                                <th>CustomerCare Approver</th>
                                <th>Finance Approver</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Order No</th>
                                <th>Organization</th>
                                <th>Products</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Date Initiated</th>
                                <th>Initiator</th>
                                <th>CustomerCare Approver</th>
                                <th>Finance Approver</th>
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
        window.renderDataTable('#purchase_table', {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('organization.purchase.datatable',$organizationId) }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "p_order"},
                {"data": "organization"},
                {"data": "products"},
                {"data": "amount"},
                {"data": "balance"},
                {"data": "created_at"},
                {"data": "intiator"},
                {"data": "cc_approver"},
                {"data": "finance_approver"},
                {"data": "status"},
                {"data": "action","orderable":false}
            ],
            "order": [[1, "asc"]]
        });
        });
    </script>
@endsection
