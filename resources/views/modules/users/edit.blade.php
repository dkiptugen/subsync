@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero">
<h3 class="card-title my-0 text-nation">Edit User</h3>
        </section>
<div class="card">

            <div class="card-body">
                <form action="{{ route('user.update',$user->id) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col col-md-8">
                            <label for="name" class="control-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
                        </div>
                        <div class="col col-md">
                            <label for="role" class="control-label">Role</label>
                            <select name="role" id="role" class="form-control select2">
                                @foreach($role as $value)
                                    <option value="{{ $value->id }}"
                                            @if($user->roles->contains('id', $value->id)) selected @endif>{{ $value->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="mb-3">
                        <label for="email" class="control-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}">
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="password" class="control-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                        <div class="col">
                            <label for="con_password" class="control-label">Confirm Password</label>
                            <input type="password" name="con_password" id="con_password" class="form-control">
                        </div>

                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" name="status" id="active"
                               @if($user->status == 1) checked @endif>
                        <label class="form-check-label" for="active">
                            Active
                        </label>
                    </div>

                    <div class="row mb-3">
                        <button type="submit" class="btn  btn-dark ms-auto">Edit User</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
