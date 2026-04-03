@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title my-0 text-nation">User Whitelist</h3>
            </div>
            <div class="card-body">

                        <form action="{{ route('whitelist.type.store','user') }}" method="post" enctype="multipart/form-data" class="form-i form-horizontal create-form">
                           @csrf

                            <div class="row mb-3">
                                <div class="col">
                                    <label for="email" class="control-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control">
                                </div>
                                <div class="col">
                                    <label for="excel_file" class="control-label">Excel file &nbsp; &nbsp; &nbsp; &nbsp;<a href="{{ asset('assets/emails.xlsx') }}">Download sample</a></label>
                                    <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" class="form-control">
                                </div>
                                <div class="col">
                                    <label for="product" class="control-label">Product</label>
                                    <select name="product" id="" class="form-control select2">
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                            <div class="row mb-3">

                                <div class="col">
                                    <label for="startdate" class="control-label">Startdate</label>
                                    <input type="date" name="startdate" id="startdate" class="form-control">
                                </div>
                                <div class="col">
                                    <label for="enddate" class="control-label">Enddate</label>
                                    <input type="date" name="enddate" id="enddate" class="form-control">
                                </div>
                                <div class="col">
                                    <label for="tag" class="control-label">Tag (not mandatory)</label>
                                    <input type="text" name="tag" value="" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="reason" class="control-label"> Reason</label>
                                <textarea name="reason" id="reason" cols="30" rows="10" class="form-control"></textarea>
                            </div>
                            <div class="mb-3 d-flex">
                                <button class="btn btn-nation ms-auto">Whitelist User</button>
                            </div>
                        </form>


            </div>
        </div>
    </div>
@endsection

