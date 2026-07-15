@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex justify-content-between align-itens-center">
<h3 class="card-title my-0 text-nation">Add Subscriber</h3>
        </section>
<div class="card">

            <div class="card-body">
                <form action="{{  route('product.subscriber.store',0) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    <div class="row mb-3">
                         <div class="col">
                             <label for="name" class="control-label">Name</label>
                             <input type="text" class="form-control" id="name" name="firstname">
                         </div>
                         <div class="col">
                             <label for="surname" class="control-label">Surname</label>
                             <input type="text" class="form-control" id="surname" name="surname">
                         </div>

                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email" class="control-label">Email</label>
                            <input type="text" class="form-control" name="email" id="email">
                        </div>
                        <div class="col">
                            <label for="phone_no" class="control-label">Phone No</label>
                            <input type="text" class="form-control" name="phone_no" placeholder="+254711000000" id="phone_no">
                        </div>

                    </div>

                    <div class="row mb-3">
                         <div class="col">
                             <label for="password" class="control-label">Password</label>
                             <input type="password" class="form-control" id="password" name="password">
                         </div>
                         <div class="col">
                             <label for="password_confirmation" class="control-label">Confirm Password</label>
                             <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation">
                         </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status" value="1" checked>
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" value="1" name="notify" id="notify" checked>
                            <label class="form-check-label" for="notify">
                                Notify
                            </label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" value="1" name="daily_notifications" id="daily_notify" checked>
                            <label class="form-check-label" for="daily_notify">
                                Receive daily notifications
                            </label>
                        </div>
                    </div>

                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation ms-auto">Add Subscriber</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
