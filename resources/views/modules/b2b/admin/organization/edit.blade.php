@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex justify-content-between align-items-center">
<h3 class="card-title text-nation my-0">Edit Corporate</h3>
        </section>
<div class="card">

            <div class="card-body">
                <form action="{{ route('organization.update',$organization->id) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <div class="mb-3">
                        <label for="organization_name" class="control-label">Organization Name</label>
                        <input type="text" name="name" id="organization_name" class="form-control"
                               value="{{ $organization->name }}">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="control-label">Address</label>
                        <input type="text" name="address" id="address" class="form-control"
                               value="{{ $organization->address }}">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="control-label">Phone No</label>
                        <input type="text" name="phone_number" id="phone" class="form-control"
                               value="{{ $organization->phone_number }}">
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="admin_name" class="control-label">Admin Name</label>
                            <input type="text" name="admin_name" id="admin_name" class="form-control"
                                   value="{{ $organization->user->name }}">
                        </div>
                        <div class="col">
                             <label for="admin_email" class="control-label">Admin Email</label>
                            <input type="text" name="admin_email" id="admin_email" class="form-control"
                                   value="{{ $organization->user->email }}">
                        </div>

                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="kra_pin" class="control-label">KRA Pin</label>
                            <input type="text" name="kra_pin" id="kra_pin" class="form-control"
                                   value="{{ $organization->kra_pin }}">
                        </div>
                        <div class="col">
                             <label for="registration_number" class="control-label">Registration No</label>
                            <input type="text" name="registration_no" id="registration_number" class="form-control"
                                   value="{{ $organization->registration_no }}">
                        </div>
                    </div>

                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation ms-auto">Update Organization</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
