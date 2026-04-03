@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-itens-center">
                <h3 class="card-title my-0 text-nation">Edit Subscriber</h3>

            </div>
            <div class="card-body">
                <form action="{{  route('product.subscriber.update',[0,$user->id]) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <div class="row mb-3">
                         <div class="col">
                             <label for="name" class="control-label">Name</label>
                             <input type="text" class="form-control" id="name" name="firstname"
                                    value="{{ $user->name }}">
                         </div>
                         <div class="col">
                             <label for="surname" class="control-label">Surname</label>
                             <input type="text" class="form-control" id="surname" name="surname"
                                    value="{{ $user->surname }}">
                         </div>

                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email" class="control-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email" value="{{ $user->email }}">
                        </div>
                        <div class="col">
                            <label for="phone_no" class="control-label">Phone No</label>
                            <input type="text" class="form-control" name="phone_no" id="phone_no"
                                   value="{{ $user->phone }}" placeholder="+254711000000">
                        </div>

                    </div>
                    <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status"
                                   @if((bool)$user->status) checked @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" value="1" name="notify" id="notify"  @if($user->can_notify == 1) checked @endif>
                            <label class="form-check-label" for="notify">
                                Notify
                            </label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" value="1" name="daily_notifications" id="daily_notify"  @if($user->daily_notifications == 1) checked @endif>
                            <label class="form-check-label" for="notify">
                                Receive daily notifications
                            </label>
                        </div>

                    </div>
                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation ms-auto">Update Subscriber</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
