@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">Edit Corporate Rate</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('organization.rate.update',[$organizationId,$rate->id]) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <div class="form-group form-row">
                        <div class="col">
                            <label for="rate_type" class="control-label">Subscription Type</label>
                            <select name="rate_type_id" id="rate_type" class="form-control select2">
                                @foreach($rate_type as $rt)
                                    <option value="{{$rt->id}}"
                                            @if($rt->id == $rate->rate_type_id) selected @endif>{{ $rt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label for="product" class="control-label">Product</label>
                            <select name="product_id" id="product" class="form-control select2">
                                @foreach($product as $prod)
                                    <option value="{{ $prod->id }}"
                                            @if($prod->id == $rate->product_id) selected @endif>{{ $prod->product_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="form-group form-row">
                        <div class="col">
                            <label for="cost" class="control-label">Cost</label>
                            <input type="number" step="0.10" name="cost" id="cost" class="form-control"
                                   value="{{ $rate->cost }}">
                        </div>

                         <div class="col">
                            <label for="currency" class="control-label">Currency</label>
                            <input type="text" name="currency" id="currency"
                                   placeholder="should in iso format eg KES,TSH,UGX,RWF,USD" class="form-control"
                                   value="{{ $rate->currency }}">
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="organization" class="control-label">Organization</label>
                        <select name="organization_id" id="organization" class="form-control select2">

                            @foreach($organization as $org)
                                <option value="{{ $org->id }}"
                                        @if($org->id == $rate->organization_id) selected @endif>{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description" class="control-label">Description</label>
                        <textarea name="description" id="description" rows="4"
                                  class="form-control">{{ $rate->description }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status" @if($rate->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>

                    </div>
                    <div class="form-group d-flex">
                        <button class="btn btn-nation ml-auto">Update Rate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
