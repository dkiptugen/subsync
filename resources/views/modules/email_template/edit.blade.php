@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title text-nation my-0">Edit Custom Template</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('email_template.update',['email_template' => $template->id]) }}" method="post" class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <div class="mb-3">
                        <label for="name" class="control-label">Name</label>
                        <input type="text" name="template_name" id="name" class="form-control" value="{{ $template->name }}">
                    </div>
                    <div class="mb-3 ">
                        
                        <label for="type" class="control-label">Email Type</label>
                        <select  name="template_type" id="template_type" class="form-control select2">
                            @foreach($types as $type)
                                <option value="{{ $type->value }}" @selected($type->value == $template->email_type)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    
                    </div>
                    <div class="mb-3 ">
                        
                        <label for="subject" class="control-label">Email Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" value="{{ $template->subject }}">
                    
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-8 col-xl-10"><label for="email_body" class="control-label">Email Body</label>
                            <textarea name="email_body" id="email_body" class="form-control editor">{{ $template->body }}</textarea>
                        </div>
                        <div class="col-12 col-md-4 col-xl-2 bg-light h-100">
                            <label for="">Variables</label>
                            <div class="text-muted">
                                <span class="text text-nation">@foreach( $variables as $variable){!!   "<p>&#123;&#123; ".$variable." &#125;&#125; </p>" !!} @endforeach</span>
                                <small class="text-warning mt-3">N/B The variable should be placed as defined above with &#123;&#123;&#125;&#125;.</small>
                            </div>
                        </div>
                        
                      
                    </div>
                   
                    <div class="mb-3">
                        <label for="products" class="control-label">Products</label>
                        <select name="products[]" id="products" class="form-control select2" multiple="multiple">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(in_array ($product->id,$template->products))>{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                   
                    <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="status"
                                   @checked((bool)$template->status)
                                   value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>
                    </div>
                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation ms-auto">Update Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
