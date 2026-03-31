@extends('includes.body')
@section('content')
    <div class="col-12 card card-border-nation">
        <div class="card-header">
            <h3 class="card-title my-0 text-nation">Edit Purchase Order</h3>
        </div>
        <div class="card-body">
            <form action="" method="post" class="form form-horizontal create-form">
                <div class="form-group form-row">
                    <div class="col">
                        <label for="product" class="control-label">Product</label>
                        <select name="product" id="product" multiple="multiple" class="form-control select2">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <label for="subtype" class="control-label">Subscription Type</label>
                        <select name="subtype" id="subtype" class="form-control select2">
                            @foreach($rates as $rate)
                                <option value="{{ $rate->id }}">{{ $rate->rate_type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <div class="form-group">
                    <label for="accounts" class="control-label">No Of Users</label>
                    <input type="number" name="accounts" id="accounts" class="form-control">
                </div>
                <div class="form-group">
                    <label for="startdate" class="control-label">Estimated Start Date</label>
                    <input type="date" name="startdate" id="startdate"class="form-control">
                </div>
                <div class="form-group">
                    <label for="description" class="control-label">Description</label>
                    <textarea name="description" id="" class="form-control"></textarea>
                </div>
                <div class="form-group d-flex">
                    <button type="submit" class="btn btn-sm btn-nation ml-auto">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection
