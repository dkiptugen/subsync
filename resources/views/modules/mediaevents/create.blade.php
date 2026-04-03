@extends('includes.body')
@section('content')
    <div class="card card-border-nation" aria-labelledby="add-role" id="add-role">
        <div class="card-header">
            <h3 class="my-0 card-title text-nation">
                Add Event
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('media_events.store') }}" method="post"
                  class="form form-horizontal create-form" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="role" class="control-label">Name</label>
                    <input type="text" name="name" id="role" class="form-control" required>
                </div>
                <div class="mb-3">
                <label for="role" class="control-label">Identifier</label>
                <input type="text" name="identifier" id="identifier" class="form-control" required>
                <div class="mb-3">
                <label for="status" class="control-label">Status</label>
                <select name="status" id="status" class="form-control" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                </select>
                </div>

               
                    
                </div>
                <div class="row mb-3">
                    <button type="submit" class="ms-auto me-2 btn btn-dark btn-sm">Save</button>
                </div>
            </form>

        </div>
    </div>
@endsection
