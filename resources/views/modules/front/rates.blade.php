@extends('includes.layout')

@section('content')
    <div class="row">
        <form action="" class="form form-horizontal row">
            @foreach($products as $product)
                <div class="col-md-6 col-12 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-nation my-0 card-title">{{ $product->product_name }}</h3>
                        </div>
                        <div class="card-body">
                            @foreach(App\Models\Rate::whereStatus(1)->where('product_id',$product->id)->get() as $rate)
                                <div class="d-flex justify-content-between border-bottom py-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rate[{{$product->id}}]"
                                               id="exampleRadios1" value="{{ $rate->id }}" checked>
                                        <label class="form-check-label" for="exampleRadios1">
                                            {{ $rate->rate_type->name }}
                                        </label>
                                    </div>
                                    <div class="cost">
                                        {{ $rate->currency." ".$rate->cost."/=" }}
                                    </div>
                                </div>

                            @endforeach
                        </div>

                    </div>
                </div>
            @endforeach


            <div class="row mb-3">

            </div>

        </form>
    </div>
@endsection
