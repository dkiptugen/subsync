@extends('includes.body')
@section('title',$title)
@section('description',$description)
@section('keywords',$keywords)
@section('logo',$logo)
@section('image',$image)
@section('content')

    <section class="card card-border-nation">
    <div class="card-header">
        <h3 class="my-0 card-title text-nation">Add Coupon</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('coupon.store') }}" class="form form-horizontal create-form" method="post">
            @csrf
            <div class="row mb-3">
                 <div class="col">
                     <label for="code" class="control-label">Coupon Code</label>
                     <input type="text" name="code" id="code" class="form-control">
                 </div>
                 <div class="col">
                     <label for="type" class="control-label">Type</label>
                     <select name="type" id="type" class="form-control select2">
                         <option value="1">Fixed</option>
                         <option value="0">Percentage</option>
                     </select>
                 </div>
             </div>
            <div class="row mb-3">
                <div class="col">
                    <label for="products" class="control-label">Product</label>
                <select name="products[]" class="form-control select2" id="products" multiple>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                    @endforeach
                </select>
                </div>
                <!-- /.col -->
                <div class="col">
                    <label for="rate_types" class="control-label">Subscription Type</label>
                    <select name="rate_type" class="form-control select2" id="rate_types">
                        @foreach($rate_types as $rate_type)
                            <option value="{{ $rate_type->id }}">{{ $rate_type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- /.col -->


            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <label for="start_date_date" class="control-label">Start Date</label>
                    <input type="text" name="start_date" id="start_date_timestamp" class="form-control">
                </div>
                <div class="col-6">
                    <label for="end_date" class="control-label">Expiry Date</label>
                    <input type="text" name="expiry_date" id="end_date_timestamp" min='{{ date('Y-m-d 00:00:00') }}'
                           class="form-control">
                </div>

            </div>
            <div class="row mb-3">
                <div class="col">
                    <label for="discount" class="control-label">Discount</label>
                    <input type="text" name="discount" id="discount" class="form-control">
                </div>
                <!-- /.col -->


                <div class="col">
                    <label for="region" class="control-label">Region</label>
                    <select name="region" id="region" class="form-control">
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="discount" class="control-label">Sales Agent email</label>
                    <input type="email" name="agent_email" id="agent_email" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <div class="mb-3">
                    <label class="control-label fw-600">Other Subscription Types</label>
                    <select name="ratetypes[]"
                            id="ratetypes"
                            class="form-control select2"
                            multiple>
                        @foreach($rate_types as $rtype)
                            <option value="{{ $rtype->id }}">{{ $rtype->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="expires" value="1">
                    <span class="form-check-label">
                        Expires?
                    </span>
                </label>
                <label class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="multi_use" value="1">
                    <span class="form-check-label">
                        Multiple use per user?
                    </span>
                </label>
            </div>
            <div class="mb-3 d-flex">
                <button type="submit" class="btn btn-nation ms-auto">Save Coupon</button>
            </div>
        </form>
    </div>
</section>

@endsection
@section('header')

@endsection
@section('footer')

@endsection
