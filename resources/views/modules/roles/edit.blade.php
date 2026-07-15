@extends('includes.body')
@section('content')
        <section class="page-hero">
<h3 class="card-title my-0 text-nation">
                Edit Role
            </h3>
    </section>
<div class="card" aria-labelledby="add-role" id="edit-role-collapse">

        <div class="card-body">
            <form action="{{ route('user.roles.update',[$userid??0,$role->id]) }}" method="post" class="form form-horizontal create-form" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-3">
                        <label for="role" class="control-label">Role Name</label>
                        <input type="text" name="role" id="edit-role" class="form-control" value="{{ $role->name }}">
                    </div>
                    <div class="col access">
                        <label class="control-label">Access</label>
                        @php($x=1)
                        <div class="row px-3">
                            @foreach($perm as $value)

                                <div class="form-check col-3">
                                    <input class="form-check-input" type="checkbox" id="edit-perm-{{ $value->id }}" @if($rp->contains('id', $value->id)) checked @endif name="perm[]" value="{{ $value->id }}" >
                                    <label class="form-check-label" for="edit-perm-{{ $value->id }}">{{ $value->name }}</label>
                                </div>
                                @php($x++)
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <button type="submit" class="ms-auto  btn btn-nation">Save</button>

                </div>
            </form>

        </div>
    </div>
@endsection
