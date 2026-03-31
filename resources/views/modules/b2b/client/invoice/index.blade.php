@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Invoices</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-hover" id="invoice-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Period</th>
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
