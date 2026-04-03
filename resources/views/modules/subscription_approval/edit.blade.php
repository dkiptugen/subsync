@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation w-100">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title my-0 text-nation">Edit Subscription</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('subscription.update' , 0) }}" method="post" class="form form-horizontal create-form">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="control-label">Email</label>
                        <!-- /.control-label -->
                        <input type="text" name="email" id="email" class="form-control">
                        <!-- /#.form-control -->
                    </div>
                    <!-- /.form-group -->
                    <div class="row mb-3">
                        <div class="col">
                            <label for="receipt" class="control-label">Receipt No</label>
                            <!-- /.control-label -->
                            <input type="text" name="receipt" id="receipt" class="form-control">
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="channel" class="control-label">Pay Channel</label>
                            <!-- /.control-label -->
                            <select name="channel" id="channel" class="form-control select2">
                                <option value="mpesa">Mpesa</option>
                                <option value="cash">Cash</option>
                                <option value="dpo">DPO</option>
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
                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
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
                                    <option value="{{ $rate->id }}">{{ $rate->name }}</option>
                                @endforeach
                            </select>
                            <!-- /#.form-control -->
                        </div>
                        <!-- /.col -->

                    </div>
                    <!-- /.form-group -->
                    <div class="row mb-3">
                        <div class="col-12 col-md">
                            <label for="currency" class="control-label">Currency</label>
                            <!-- /.control-label -->
                            <select  name="currency" id="currency" class="form-control select2">
                                <option value="KES">KES</option>
                                <option value="TSH">TSH</option>
                                <option value="UGX">UGX</option>
                                <option value="RWF">RWF</option>
                                <option value="USD">USD</option>
                            </select>
                            <!-- /#.form-control -->
                        </div>
                        <div class="col-12 col-md">
                            <label for="amount" class="control-label">Amount Paid</label>
                            <!-- /.control-label -->
                            <input type="text" name="amount" id="amount" class="form-control">
                            <!-- /#.form-control -->
                        </div>
                        <div class="col-12 col-md">
                            <label for="startdate" class="control-label">Start Date</label>
                            <!-- /.control-label -->
                            <input type="text" name="startdate" id="startdate" class="form-control datesingle">
                            <!-- /#.form-control -->
                        </div>

                    </div>
                    <!-- /.form-group -->
                    <div class="mb-3">
                        <label for="description" class="control-label">Activation Reason</label>
                        <!-- /.control-label -->
                        <textarea name="reason" id="description" class="form-control"></textarea>
                        <!-- /#.form-control -->
                    </div>
                    <!-- /.form-group -->
                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation btn-sm ms-auto">
                            Save Subscription
                        </button>
                        <!-- /.btn btn-outline-nation btn-sm -->
                    </div>
                </form>
                <!-- /.form form-horizontal create-form -->
            </div>
        </div>
    </div>
@endsection

