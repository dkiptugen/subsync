@extends('includes.body')
@section('content')
    <div class="col-12">
        <section class="page-hero">
<h3 class="card-title my-0 text-nation">Profile</h3>
    </section>
<div class="card">

        <div class="card-body">
            <form action="{{ route('profile.update',$user->id) }}" method="post"
                  class="form form-horizontal create-form">
                @csrf
                @method('put')
                <div class="row mb-3">
                    <div class="col">
                        <label for="name" class="control-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
                    </div>
                    <div class="col">
                        <label for="surname" class="control-label">Surname</label>
                        <input type="text" name="surname" id="surname" class="form-control"
                               value="{{ $user->surname }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label for="email" class="control-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}">
                    </div>
                    <div class="col">
                        <label for="phone_number" class="control-label">Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" class="form-control"
                               value="{{ $user->phone }}">
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label for="password" class="control-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control">
                    </div>
                    <div class="col">
                        <label for="con_password" class="control-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="con_password" class="form-control">
                    </div>

                </div>

                <div class="row mb-3">
                    <button type="submit" class="btn  btn-dark ms-auto">Update Account</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
