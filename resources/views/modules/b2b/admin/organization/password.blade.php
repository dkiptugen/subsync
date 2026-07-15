@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title text-nation my-0">Set Default Password</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('organization.password.store',$organization->id) }}" method="post" class="form form-horizontal create-form">
                    @csrf
                    <div class="mb-3">
                        <label for="organization_name" class="control-label">Organization Name</label>
                        <input type="text" name="name" id="organization_name" class="form-control" disabled value="{{ $organization->name }}">
                    </div>
                    <div class="mb-3">
                        <label for="default_password" class="control-label">Default Password</label>
                        <input type="text" name="password" id="default_password" class="form-control">
                    </div>


                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation ms-auto">Set Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
