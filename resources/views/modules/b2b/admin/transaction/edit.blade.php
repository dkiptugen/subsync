@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Edit Transaction : {{ $transaction->subscription->company_name .' '.$transaction->subscription->product->product_name }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('organization.transaction.update',[$organizationId,$transaction->id]) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <div class="row mb-3">
                        <div class="col">
                            <label for="startdate" class="control-label">Start Date</label>
                            <!-- /.control-label -->
                            <input type="text" name="startdate" id="startdate" class="form-control datesingle" value="{{ $transaction->subscription->start_date }}">
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="paychannel" class="control-label">Pay Channel</label>
                            <!-- /.control-label -->
                            <input type="text" name="channel" id="paychannel" class="form-control" value="{{ $transaction->pay_channel }}">
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="receipt" class="control-label">Receipt No</label>
                            <!-- /.control-label -->
                            <input type="text" name="receipt" id="receipt" class="form-control" value="{{ $transaction->receipt }}">
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="amount" class="control-label">Amount Paid</label>
                            <!-- /.control-label -->
                            <input type="number" name="amount" id="amount" class="form-control" value="{{ $transaction->amount_paid }}">
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="control-label">Reason</label>
                        <textarea name="reason" id="reason" cols="30" rows="10" class="form-control">{{$transaction->activator_reason}}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status" @if($transaction->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>

                    </div>
                    <div class="mb-3 d-flex">
                        <button class="btn btn-nation ms-auto">Update Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
