@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-itens-center">
                <h3 class="card-title my-0 text-nation">Change Password</h3>
               
            </div>
            <div class="card-body">
                <form action="{{ route('product.subscriber.change_password',[0,$user->id]) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    @method('patch')
                    <div class="form-group form-row">
                         <div class="col">
                             <label for="name" class="control-label">Name</label>
                             <input type="readonly" disabled class="form-control" id="name" name="name"
                                    value="{{ $user->name }}">
                         </div>
                         <div class="col">
                             <label for="email" class="control-label">Email</label>
                             <input type="readonly" disabled class="form-control" id="email" name="email"
                                    value="{{ $user->email }}">
                         </div>
                        
                    </div>
                    <div class="form-group">
                         <label for="password" class="control-label">Password</label>
                         <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="form-group">
                         <label for="password_confirmation" class="control-label">Confirm Password</label>
                         <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation">
                    </div>
                   
                    <div class="form-group d-flex">
                        <button type="submit" class="btn btn-nation ml-auto">Change password</button>
                    </div>
                </form>
              
            </div>
        </div>
    </div>
@endsection
