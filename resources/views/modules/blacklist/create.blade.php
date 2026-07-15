@extends('includes.body')
@section('content')
        <section class="page-hero">
<h3 class="my-0 card-title text-nation">
                Add Blacklist
            </h3>
    </section>
<div class="card" aria-labelledby="add-role" id="add-role">

        <div class="card-body">
            <form action="{{ route('mpesa_blacklist.store') }}" method="post"
                  class="form form-horizontal create-form" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="role" class="control-label">Phone Number</label>
                    <input type="text" name="phone_number" id="role" class="form-control" required>
                </div>
                <div class="mb-3">
                <label for="role" class="control-label">Reason</label>
                <input type="text" name="reason" id="reason" class="form-control" required>

                </div>
                <div class="row mb-3">
                    <button type="submit" class="ms-auto me-2 btn btn-dark btn-sm">Save</button>
                </div>
            </form>

        </div>
    </div>
@endsection
