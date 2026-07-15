@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title text-nation my-0">Add Subscription Type</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('rate_type.store') }}" method="post" class="form form-horizontal create-form">
                    @csrf
                    <div class="mb-3">
                        <label for="rate-type" class="control-label">Subscription Type</label>
                        <input type="text" name="name" id="rate-type" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="swahili-name" class="control-label">Swahili Name</label>
                        <input type="text" name="swahili_name" id="swahili-name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="period" class="control-label">Period</label>
                        <input type="number" name="period" id="period" class="form-control" placeholder="Number of Days">
                    </div>
                    <div class="mb-3">
                        <label for="days-of-week" class="control-label">Days of the Week</label>
                        <select name="days_of_week[]" id="days-of-week" class="form-control select2" multiple>
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>
                    <div class="mb-3 d-flex">
                        <button class="btn btn-nation ms-auto">Add Subscription Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('header')

@endsection
@section('footer')


@endsection
