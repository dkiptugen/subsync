@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Receipts</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-hover" id="receipt-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Receipt No</th>
                                <th>Product</th>
                                <th>Total Cost</th>
                                <th>Balance</th>
                                <th>Amount Paid</th>
                                <th>Pay Channel</th>
                                <th>Date Paid</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">

                        <tr>
                            <th>#</th>
                            <th>Receipt No</th>
                            <th>Product</th>
                            <th>Total Cost</th>
                            <th>Balance</th>
                            <th>Amount Paid</th>
                            <th>Pay Channel</th>
                            <th>Date Paid</th>
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
