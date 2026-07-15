@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Edit Rate</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('product.rate.update',[$productid,$rate->id]) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <div class="row mb-3">
                        <div class="col-12 col-md-8">
                            <label for="rate_name" class="control-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control"
                                   value="{{ $rate->rate_type->name }}">
                        </div>
                        <input type="hidden" name="rate_type_id" value="{{ $rate->rate_type_id }}">
                        <div class="col-12 col-md-4">
                            <label for="product" class="control-label">Product</label>
                            <select name="product_id" id="product" class="form-control select2">
                                @foreach($product as $prod)
                                    <option value="{{ $prod->id }}"
                                            @if($rate->product_id == $prod->id) selected @endif>{{ $prod->product_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="period" class="control-label">Period in days</label>
                            <input type="text" name="period" id="period" class="form-control"
                                   value="{{ $rate->rate_type->period }}">
                        </div>
                        <div class="col">
                            <label for="cost" class="control-label">Cost</label>
                            <input type="text" name="cost" id="cost" class="form-control" value="{{ $rate->cost }}">
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="currency" class="control-label">Currency</label>
                            <select name="currency" id="currency" class="form-control select2">
                                <option value="KES" @if($rate->currency=='KES') selected @endif>KES</option>
                                <option value="TZS" @if($rate->currency=='TZS') selected @endif>TZS</option>
                                <option value="UGX" @if($rate->currency=='UGX') selected @endif>UGX</option>
                                <option value="RWF" @if($rate->currency=='RWF') selected @endif>RWF</option>
                                <option value="USD" @if($rate->currency=='USD') selected @endif>USD</option>
                            </select>
                        </div>
                        <div class="col">
                            <label for="slash_price" class="control-label">Slash price</label>
                            <input type="number" step="0.10" name="slash_price" id="cost" class="form-control" value="{{$rate->strike_price}}">
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="status" class="control-label">Status</label>
                            <select  name="status" id="status" class="form-control select2">
                                <option value="1" @if($rate->status == 1) selected @endif>Active</option>
                                <option value="0" @if($rate->status == 0) selected @endif>Inactive</option>
                            </select>
                        </div>
                        <!-- /.col -->
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="free_product" class="control-label">Free Product</label>
                            <select name="free_product" id="free_product" class="form-control select2">
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div class="col">
                            <label for="free_product_lifetime" class="control-label">Free Product Lifetime</label>
                            <input type="text" name="free_product_lifetime" id="free_product_lifetime"
                                   class="form-control datetimesingle">
                        </div>
                        <div class="col">
                            <label for="product" class="control-label">Category</label>
                            <select name="category" id="category" class="form-control select2">
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @if($rate->category == $category) selected @endif>{{ ucfirst($category) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-8">
                            <label for="apple_product_id" class="control-label">AppLe Product ID</label>
                            <input type="text" name="apple_product_id" id="apple_product_id" class="form-control"
                                   value="{{ $rate->apple_product_id }}">
                        </div>
                        <div class="col">
                            <label for="editions" class="control-label">Editions</label>
                            <input type="number" name="editions" id="editions" class="form-control" value="{{ $rate->editions }}">
                        </div>
                        <div class="col">
                            <label for="listorder" class="control-label">ListOrder</label>
                            <input type="number" name="listorder" id="listorder" class="form-control" value="{{ $rate->listorder }}">
                        </div>
                        <div class="col">
                            <label for="compensation_days" class="control-label">Compensation Days</label>
                            <input type="number" name="compensation_days" id="compensation_days" class="form-control" value="{{ $rate->compensation_days }}" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="control-label">Description</label>
                        <textarea name="description" id="description" rows="4"
                                  class="form-control">{{ $rate->description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status" @if($rate->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="best_value"
                                   @if($rate->best_value) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Best Value
                            </span>
                        </label>

                    </div>
                    <!-- /.form-group -->
                    <div class="mb-3 d-flex">
                        <button class="btn btn-nation ms-auto">Update Rate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#free_product').select2({
                ajax: {
                    url: '{{ route('get_rate_select') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        console.log(params);
                        return {
                            term: params.term // Search term entered by the user
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                placeholder: 'Select a Product',
                minimumInputLength: 2 // Minimum number of characters before the search is performed
            });

        });
    </script>

@endsection
