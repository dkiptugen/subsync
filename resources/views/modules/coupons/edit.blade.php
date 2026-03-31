@php use Carbon\Carbon; @endphp
@extends('includes.body')
@section('title',$title)
@section('description',$description)
@section('keywords',$keywords)
@section('logo',$logo)
@section('image',$image)
@section('content')
    <section class="card">
        <div class="card-header">
            <h3>Edit Promo code</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('coupon.update',$promo->id) }}" class="form form-horizontal create-form"
                  method="post">
                @csrf
                @method('put')
                <div class="form-group form-row">
                    <div class="col">
                        <label for="code" class="control-label">Coupon Code</label>
                        <input type="text" name="code" id="code" class="form-control" value="{{ $promo->code }}">
                    </div>
                    <div class="col">
                        <label for="type" class="control-label">Type</label>
                        <select name="type" id="type" class="custom-select">
                            <option value="1" @if($promo->type === 1) selected @endif>Fixed </option>
                            <option value="0" @if($promo->type === 0) selected @endif>Percentage</option>
                        </select>
                    </div>
                </div>
                <div class="form-group form-row w-100">
                    <div class="col">
                        <label for="products" class="control-label">Product</label>
                        <select name="products[]" class="form-control select2" id="products" multiple>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        @if(in_array($product->id,$promo->products->pluck('id')->toArray())) selected @endif>{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- /.col-2 -->
                    <div class="col">
                         <label for="rate_types" class="control-label">Subscription Type</label>
                        <select name="rate_type" class="form-control select2" id="rate_types">
                            @foreach($rate_types as $rate_type)
                                <option value="{{ $rate_type->id }}"
                                        @if($rate_type->id == $promo->rate_type) selected @endif>{{ $rate_type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- /.col-2 -->

                </div>
                <div class="form-group form-row">
                    <div class="col">
                        <label for="start_date_date" class="control-label">Start Date</label>
                        <input type="text" name="start_date" id="start_date_timestamp"
                               class="form-control"
                               value="{{ Carbon::parse($promo->start_date)->format('Y-m-d') }}">
                    </div>
                    <div class="col">
                        <label for="end_date" class="control-label">Expiry Date</label>
                        <input type="text" name="expiry_date" id="end_date_timestamp" class="form-control"
                               value="{{ Carbon::parse($promo->expiry_date)->format('Y-m-d') }}">
                    </div>

                </div>
                <div class="form-group form-row">
                    <div class="col">
                        <label for="discount" class="control-label">Discount</label>
                        <input type="text" name="discount" id="discount" class="form-control"
                               value="{{ $promo->discount }}">

                    </div>
                    <!-- /.col -->
                    <div class="col">
                        <label for="region" class="control-label">Region</label>
                        <select name="region" id="region" class="form-control">
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}"
                                        @if($promo->region_id == $region->id ) selected @endif>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- /.col -->
                    <div class="col">
                        <label for="discount" class="control-label">Sales Agent email</label>
                        <input type="email" name="agent_email" id="agent_email" class="form-control" value="{{ $promo->agent_email }}">
                    </div>

                </div>

                <div class="form-group">
                    <div class="form-group">
                        <label class="control-label fw-600">Other Subscription Types</label>
                        <select name="ratetypes[]"
                                id="ratetypes"
                                class="form-control select2"
                                multiple>
                            @foreach($rate_types as $rtype)
                                <option
                                    @if( in_array( $rtype->id,$promo->rateTypes->pluck('id')->toArray()) ) selected @endif
                                    value="{{ $rtype->id }}">{{ $rtype->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                 <div class="form-group">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status" @if($promo->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>
                     <label class="form-check form-check-inline">
                         <input class="form-check-input" type="checkbox" name="expires"
                                @if($promo->expires)
                                    checked
                                @endif value="1">
                         <span class="form-check-label">
                                Expires?
                         </span>
                     </label>
                     <label class="form-check form-check-inline">
                         <input class="form-check-input" type="checkbox" name="multi_use"
                                @if($promo->multi_use)
                                    checked
                                @endif value="1">
                         <span class="form-check-label">
                                Multiple use per user?
                         </span>
                     </label>
                 </div>
                <div class="form-group d-flex">
                    <button type="submit" class="btn btn-sm btn-nation ml-auto">Update Coupon</button>
                </div>
            </form>
        </div>
    </section>
@endsection
@section('header')

@endsection
@section('footer')

@endsection
