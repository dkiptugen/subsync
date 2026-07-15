@extends('includes.body')
@section('content')
    <div class="col-12">
        <section class="page-hero">
<h3 class="card-title my-0 text-nation">Add User</h3>
    </section>
<div class="card">

        <div class="card-body">
            <form action="{{ route('user.store') }}" method="post" class="form form-horizontal create-form">
                @csrf
                <div class="row mb-3">
                    <div class="col col-md-8">
                        <label for="name" class="control-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control">
                    </div>
                    <div class="col col-md">
                        <label for="role" class="control-label">Role</label>
                        <select name="role_id" id="role" class="form-control select2">
                            @foreach($role as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <div class="mb-3">
                    <label for="email" class="control-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control">
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
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" value="1" name="status" id="active" checked>
                    <label class="form-check-label" for="active">
                        Active
                    </label>
                </div>

                <div class="row mb-3">
                    <button type="submit" class="btn  btn-dark ms-auto">Add User</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
