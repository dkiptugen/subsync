@extends('includes.body')
@section('content')
<div class="col-12">
    <div class="row">
        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill">
                <div class="card-header border-0">
                    <h5 class="card-title my-0 text-nation">Users</h5>
                </div>
                <div class="card-body my-1  text-left">
                    <h2 class=" font-weight-light my-0">
                        120
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill">
                <div class="card-header border-0">
                    <h5 class="card-title my-0 text-nation">Total Amount Paid</h5>
                </div>
                <div class="card-body my-1  text-left">

                    <h2 class="font-weight-light my-0">
                        Ksh 28,200/=
                    </h2>

                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill">
                <div class="card-header border-0">

                    <h5 class="card-title my-0 text-nation">Purchase orders Approved</h5>
                </div>
                <div class="card-body my-1   text-left">

                    <h2 class="font-weight-light my-0">
                        19
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill">
                <div class="card-header border-0 ">

                    <h5 class="card-title my-0 text-nation"> Total Balance Accrued</h5>
                </div>
                <div class="card-body my-1 text-left">
                    <h2 class="font-weight-light my-0">
                        Ksh 82,400/=
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-12 d-flex ">
            <div class="card flex-fill">
                <div class="card-header border-0 ">

                    <h5 class="card-title my-0 text-nation"> Subscription</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-condensed table-hover table-striped">
                            <thead class="bg-nation text-white">
                                <tr>
                                    <th>Subcription Id</th>
                                    <th>Product</th>
                                    <th>No Of Users</th>
                                    <th>Loaded Users</th>
                                    <th>Status</th>
                                    <th>Subscription Date</th>
                                    <th>Days to Expire</th>
                                    <th>Fully Paid</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot class="bg-nation text-white">
                            <tr>
                                <th>Subcription Id</th>
                                <th>Product</th>
                                <th>No Of Users</th>
                                <th>Loaded Users</th>
                                <th>Status</th>
                                <th>Subscription Date</th>
                                <th>Days to Expire</th>
                                <th>Fully Paid</th>
                                <th>Action</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
