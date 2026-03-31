@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Add Rate</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('product.rate.store',$productid) }}" method="post" class="form form-horizontal create-form">
                    @csrf
                    <div class="form-group form-row">
                        <div class="col-12 col-md-8">
                            <label for="rate_type" class="control-label">Subscription Type</label>
                            <select name="rate_type_id" id="rate_type" class="form-control select2">
                                @foreach($rate_type as $rt)
                                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="product" class="control-label">Product</label>
                            <select name="product_id" id="product" class="form-control select2">
                                @foreach($product as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->product_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="form-group form-row">
                        <div class="col">
                            <label for="cost" class="control-label">Cost</label>
                            <input type="number" step="0.10" name="cost" id="cost" class="form-control">
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="currency" class="control-label">Currency</label>
                            <select  name="currency" id="currency" class="form-control select2">
                                <option value="KES">KES</option>
                                <option value="TZS">TZS</option>
                                <option value="UGX">UGX</option>
                                <option value="RWF">RWF</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="slash_price" class="control-label">Slash price</label>
                            <input type="number" step="0.10" name="slash_price" id="cost" class="form-control">
                        </div>
                        <!-- /.col -->
                        <div class="col">
                            <label for="status" class="control-label">Status</label>
                            <select  name="status" id="status" class="form-control select2">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <!-- /.col -->

                    </div>
                    <div class="form-group form-row">
                        <div class="col">
                            <label for="free_product" class="control-label">Free Product</label>
                            <select name="free_product" id="free_product" class="form-control select2">
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div class="col">
                            <label for="free_product_lifetime" class="control-label">Free Product Lifetime</label>
                            <input type="text" name="free_product_lifetime" id="free_product_lifetime" class="form-control datetimesingle">
                        </div>
                        <div class="col">
                            <label for="product" class="control-label">Category</label>
                            <select name="category" id="category" class="form-control select2">
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group form-row">
                        <div class="col-4">
                            <label for="apple_product_id" class="control-label">Apple Product ID</label>
                            <input type="text" name="apple_product_id" id="apple_product_id" class="form-control">
                        </div>
                        <div class="col">
                            <label for="editions" class="control-label">Editions</label>
                            <input type="number" name="editions" id="editions" class="form-control">
                        </div>
                        <div class="col">
                            <label for="listorder" class="control-label">ListOrder</label>
                            <input type="number" name="listorder" id="listorder" class="form-control">
                        </div>
                        <div class="col">
                            <label for="compensation_days" class="control-label">Compensation Days</label>
                            <input type="number" name="compensation_days" id="compensation_days" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description" class="control-label">Description</label>
                        <textarea name="description" id="description" rows="4" class="form-control editor"></textarea>
                    </div>
                    <div class="form-group">

                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="best_value" value="1">
                            <span class="form-check-label">
                                Best Value
                            </span>
                        </label>

                    </div>
                    <div class="form-group d-flex">
                        <button class="btn btn-nation ml-auto">Save Rate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#free_product').select2({
                ajax: {
                    url: '{{ route('get_rate_select') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        console.log(params);
                        return {
                            term: params.term // Search term entered by the user
                        };
                    },
                    processResults: function(data) {
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
