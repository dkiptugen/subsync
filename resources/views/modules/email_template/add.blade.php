@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title text-nation my-0">Create Email Template</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('email_template.store') }}" method="post" class="form form-horizontal create-form">
                    @csrf
                    <div class="form-group form-row">
                        
                        <div class="col">
                            <label for="name" class="control-label">Name</label>
                            <input type="text" name="template_name" id="name" class="form-control">
                        </div>
                        <div class="col">
                            <label for="type" class="control-label">Email Type</label>
                            <select  name="template_type" id="type" class="form-control select2">
                                @foreach($types as $type)
                                    <option value="{{ $type->value }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                    </div>
                    <div class="form-group ">
                        
                        <label for="subject" class="control-label">Email Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control">

                    </div>

                    <div class="form-group">
                        <label for="products" class="control-label">Products</label>
                        <select name="products[]" id="products" class="form-control select2" multiple="multiple">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group d-flex">
                        <button type="submit" class="btn btn-nation ml-auto">Create Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
