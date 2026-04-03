@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title text-nation my-0">Edit Subscription Type</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('rate_type.update',$ratetype->id) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <div class="mb-3">
                        <label for="rate-type" class="control-label">Subscription Type</label>
                        <input type="text" name="name" id="rate-type" class="form-control"
                               value="{{ $ratetype->name }}">
                    </div>
                     <div class="mb-3">
                        <label for="swahili-name" class="control-label">Swahili Name</label>
                        <input type="text" name="swahili_name" id="swahili-name" class="form-control" value="{{ $ratetype->swahili_name }}">
                    </div>
                    <div class="mb-3">
                        <label for="period" class="control-label">Period</label>
                        <input type="number" name="period" id="period" class="form-control"
                               value="{{ $ratetype->period }}" placeholder="Number of Days">
                    </div>
                    <div class="mb-3">
                        <label for="days-of-week" class="control-label">Days of the Week</label>
                        <select name="days_of_week[]" id="days-of-week" class="form-control select2" multiple>
                            <option value="Sunday" @if(in_array('Sunday',$ratetype->dow??[])) selected @endif>Sunday</option>
                            <option value="Monday" @if(in_array('Monday',$ratetype->dow??[])) selected @endif>Monday</option>
                            <option value="Tuesday" @if(in_array('Tuesday',$ratetype->dow??[])) selected @endif>Tuesday</option>
                            <option value="Wednesday" @if(in_array('Wednesday',$ratetype->dow??[])) selected @endif>Wednesday</option>
                            <option value="Thursday" @if(in_array('Thursday',$ratetype->dow??[])) selected @endif>Thursday</option>
                            <option value="Friday" @if(in_array('Friday',$ratetype->dow??[])) selected @endif>Friday</option>
                            <option value="Saturday" @if(in_array('Saturday',$ratetype->dow??[])) selected @endif>Saturday</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status" @if($ratetype->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>

                    </div>
                    <div class="mb-3 d-flex">
                        <button class="btn btn-nation ms-auto">Edit Subscription Type</button>
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
