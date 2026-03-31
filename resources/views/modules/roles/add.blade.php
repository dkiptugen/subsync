@extends('includes.body')
@section('content')
    <div class="card card-border-nation" aria-labelledby="add-role" id="add-role">
        <div class="card-header">
            <h3 class="my-0 card-title text-nation">
                Add Role
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('user.roles.store',$userid??0) }}" method="post"
                  class="form form-horizontal create-form" enctype="multipart/form-data">
                @csrf
                <div class="form-group">

                    <label for="role" class="control-label">Role Name</label>
                    <input type="text" name="role" id="role" class="form-control">
                </div>
                <div class="form-group">
                    <label class="control-label">Access</label>
                    @php($x=1)

                        @foreach($perm as $value)
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h3 class="card-title my-0 text-nation">{{ ucwords(str_replace('_',' ',$value->name)) }}</h3>
                                </div>
                                <div class="card-body">

                                        @foreach($value->permissions as $perm)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="perm{{ $x }}"
                                                       name="perm[]" value="{{ $perm->id }}">
                                                <label class="form-check-label"
                                                       for="perm{{ $x }}">{{ $perm->actual_name }}</label>
                                            </div>
                                            @php($x++)
                                        @endforeach



                                </div>
                            </div>

                        @endforeach


                </div>
                <div class="form-row form-group">
                    <button type="submit" class="ml-auto mr-2 btn btn-dark btn-sm">Save Roles</button>
                </div>
            </form>

        </div>
    </div>
@endsection
