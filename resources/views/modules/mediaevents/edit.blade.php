@extends('includes.body')
@section('content')
    <div class="card card-border-nation" aria-labelledby="add-role" id="add-role">
        <div class="card-header">
            <h3 class="my-0 card-title text-nation">
                Add MediaEvent
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('media_events.update',$record) }}" method="post"
                  class="form form-horizontal create-form" enctype="multipart/form-data">
                  @method('PATCH')
                @csrf
                <div class="form-group">
                    <label for="role" class="control-label">Name</label>
                    <input type="text" name="name" id="role" class="form-control" value="{{$record->name}}" required>
                </div>
                <div class="form-group">
                <label for="role" class="control-label">Identifier</label>
                <input type="text" name="identifier" id="identifier" class="form-control" value="{{$record->identifier}}" required>
                <div class="form-group">
                <label for="status" class="control-label">Status</label>
                <select name="status" id="status" class="form-control" value="{{$record->status}}" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                </select>
                </div>
                    
                </div>
                <div class="form-row form-group">
                    <button type="submit" class="ml-auto mr-2 btn btn-dark btn-sm">Update</button>
                </div>
            </form>

        </div>
    </div>
@endsection
