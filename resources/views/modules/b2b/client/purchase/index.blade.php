@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="my-0 text-nation card-title">Purchase Order</h3>
                <a href="{{ route('client_purchase_order.create') }}" class="btn btn-outline-nation btn-sm">
                    <i class="fas fa-plus mr-2"></i>
                    Create Purchase Order
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-hover table-striped">
                        <thead class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Order No</th>
                            <th>Product</th>
                            <th>No Of Users</th>
                            <th>Amount</th>
                            <th>Date Initiated</th>
                            <th>Initiator</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Order No</th>
                            <th>Product</th>
                            <th>No of Users</th>
                            <th>Amount</th>
                            <th>Date Initiated</th>
                            <th>Initiator</th>
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
