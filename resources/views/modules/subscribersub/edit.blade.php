@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex justify-content-between align-items-center flex-wrap">
<h3 class="card-title my-0 text-nation">Edit Subscription</h3>
        </section>
<div class="card w-100">

            <div class="card-body">
                <form action="{{ route('user.subscription.update' ,[$user->id,$subscription->id]) }}" method="post" class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <!-- /.form-group -->
                    <div class="row mb-3">
                        <div class="col">
                            <label for="receipt" class="control-label">Receipt No</label>
                            <!-- /.control-label -->
                            <input type="text" name="receipt" id="receipt" class="form-control" value="{{ $transaction->receipt??"" }}">
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="channel" class="control-label">Pay Channel</label>
                            <!-- /.control-label -->
                            <select name="channel" id="channel" class="form-control select2">
                                <option value="mpesa" @if(optional($transaction)->channel == 'mpesa' ) selected @endif>Mpesa</option>
                                <option value="cash" @if(optional($transaction)->channel == 'cash' ) selected @endif>Cash</option>
                                <option value="dpo" @if(optional($transaction)->channel == 'dpo' ) selected @endif>DPO</option>
                            </select>
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.form-group -->
                    <div class="row mb-3">
                        <div class="col">
                            <label for="product" class="control-label">Product</label>
                            <!-- /.control-label -->
                            <select name="product" id="product" class="form-control select2">
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @if($subscription->product_id ==$product->id ) selected @endif>{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="rate" class="control-label">Subscription Type</label>
                            <!-- /.control-label -->
                            <select name="rate" id="rate" class="form-control select2">
                                @foreach($rates as $rate)
                                    <option value="{{ $rate->id }}" @if($subscription->rate_id ==$rate->id ) selected @endif>{{ $rate->name }}</option>
                                @endforeach
                            </select>
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->

                    </div>
                    <!-- /.form-group -->
                    <div class="row mb-3">
                        <div class="col-12 col-md">
                            <label for="amount" class="control-label">Amount Paid</label>
                            <!-- /.control-label -->
                            <input type="text" name="amount" id="amount" class="form-control" value="{{ optional($transaction)->amount_paid }}">
                            <!-- /#.form-control -->
                        </div>
                        <div class="col-12 col-md">
                            <label for="currency" class="control-label">Currency</label>
                            <!-- /.control-label -->
                            <input type="text" name="currency" id="currency" class="form-control" value="{{ optional($transaction)->currency }}">
                            <!-- /#.form-control -->
                        </div>
                        <div class="col-12 col-md">
                            <label for="startdate" class="control-label">Start Date</label>
                            <!-- /.control-label -->
                            <input type="text" name="startdate" id="startdate" class="form-control datesingle" value="{{ $subscription->start_date }}">
                            <!-- /#.form-control -->
                        </div>

                    </div>
                    <!-- /.form-group -->
                    <div class="mb-3">
                        <label for="description" class="control-label">Activation Reason</label>
                        <!-- /.control-label -->
                        <textarea name="reason" id="description" class="form-control">{{ $subscription->activator_reason }}</textarea>
                        <!-- /#.form-control -->
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status"
                                   @if((bool)$subscription->status) checked @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>
                    </div>
                    <!-- /.form-group -->
                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation btn-sm ms-auto">
                            Update Subscription
                        </button>
                        <!-- /.btn btn-outline-nation btn-sm -->
                    </div>
                </form>
                <!-- /.form form-horizontal create-form -->
            </div>
        </div>
    </div>
@endsection
