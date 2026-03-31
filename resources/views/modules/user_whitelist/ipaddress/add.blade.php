@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title my-0 text-nation">IP Address Whitelist</h3>
            </div>
            <div class="card-body">


                        <form action="{{ route('whitelist.type.store','ipaddress') }}" method="post" class="form form-horizontal create-form">
                            @csrf
                            <div class="form-group form-row">
                                <div class="col">
                                    <label for="organization" class="control-label">Organization</label>
                                    <select name="organization" id="organization" class="form-control select2">
                                        @foreach($organizations as $organization)
                                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="org-product" class="control-label">Product</label>
                                    <select name="product" id="org-product" class="form-control select2">
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                            <div class="form-group form-row">
                                <div class="col-8">
                                    <label for="ipaddress" class="control-label">IP Address</label>
                                    <input type="text" name="ipaddress" id="ipaddress" class="form-control">
                                </div>
                                <div class="col">
                                    <label for="users" class="control-label">Concurrent Users</label>
                                    <input type="number" name="users" id="users" class="form-control">
                                </div>

                            </div>
                            <div class="form-group form-row">

                                <div class="col">
                                    <label for="org-startdate" class="control-label">Startdate</label>
                                    <input type="date" name="startdate" id="org-startdate" class="form-control">
                                </div>
                                <div class="col">
                                    <label for="org-enddate" class="control-label">Enddate</label>
                                    <input type="date" name="enddate" id="org-enddate" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="org-reason" class="control-label"> Reason</label>
                                <textarea name="reason" id="org-reason" cols="30" rows="10" class="form-control"></textarea>
                            </div>
                            <div class="form-group d-flex">
                                <button class="btn btn-nation ml-auto">Whitelist IPADDRESS</button>
                            </div>
                        </form>


            </div>
        </div>
    </div>
@endsection

