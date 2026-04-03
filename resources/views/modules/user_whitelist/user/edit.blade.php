@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title my-0 text-nation">User Whitelist</h3>
            </div>
            <div class="card-body">

                <form action="{{ route('whitelist.type.update',['user',$whitelist->id]) }}" method="POST" class="form form-horizontal create-form">
                    @method('PATCH')
                    @csrf
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email" class="control-label">Email</label>
                            <input type="text" name="email" id="email" class="form-control" value="{{ @$whitelist->customer->email }}">
                        </div>
                        <div class="col">
                            <label for="product" class="control-label">Product</label>
                            <select name="product" id="" class="form-control select2">
                                @foreach($products as $product)
                                    <option
                                        @if($product->id == $whitelist->product->id)selected @endif
                                        value="{{ $product->id }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="row mb-3">

                        <div class="col">
                            <label for="startdate" class="control-label">Startdate {{$whitelist->startdate}}</label>
                            <input type="datetime-local" name="startdate" id="startdate" class="form-control" value="{{$whitelist->startdate}}">
                        </div>
                        <div class="col">
                            <label for="enddate" class="control-label">Enddate {{$whitelist->startdate}}</label>
                            <input type="datetime-local" name="enddate" id="enddate" class="form-control" value="{{$whitelist->enddate}}">
                        </div>
                        <div class="col">
                            <label for="tag" class="control-label">Tag (not mandatory)</label>
                            <input type="text" name="tag" value="{{ $whitelist->tag }}" class="form-control">
                        </div>

                        <div class="col">
                            <label for="product" class="control-label">Status</label>
                            <select name="status" id="" class="form-control select2">
                                @foreach(['inactive','active'] as $key => $status)
                                    <option
                                        @if($key == $whitelist->status)selected @endif
                                    value="{{ $key }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="control-label"> Reason</label>
                        <textarea name="reason" id="reason" cols="30" rows="10" class="form-control">{{$whitelist->reason}}</textarea>
                    </div>
                    <div class="mb-3 d-flex">
                        <button class="btn btn-nation ms-auto">Whitelist User</button>
                    </div>
                </form>


            </div>
        </div>
    </div>
@endsection

