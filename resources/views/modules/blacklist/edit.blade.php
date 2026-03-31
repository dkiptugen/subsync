@extends('includes.body')
@section('content')
    <div class="card card-border-nation" aria-labelledby="add-role" id="add-role">
        <div class="card-header">
            <h3 class="my-0 card-title text-nation">
                Add Blacklist
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('mpesa_blacklist.update',$record) }}" method="post"
                  class="form form-horizontal create-form" enctype="multipart/form-data">
                  @method('PATCH')
                @csrf
                <div class="form-group">
                    <label for="role" class="control-label">Phone Number</label>
                    <input type="text" name="phone_number" id="role" class="form-control" value="{{$record->phone}}" required>
                </div>
                <div class="form-group">
                <label for="role" class="control-label">Reason</label>
                <input type="text" name="reason" id="reason" class="form-control" value="{{ $record->description }}" required>
                    
                </div>
                <div class="form-row form-group">
                    <button type="submit" class="ml-auto mr-2 btn btn-dark btn-sm">Update</button>
                </div>
            </form>

        </div>
    </div>
@endsection
