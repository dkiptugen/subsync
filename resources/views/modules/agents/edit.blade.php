@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex justify-content-between align-itens-center">
<h3 class="card-title my-0 text-nation">Add Sales Agent</h3>
        </section>
<div class="card">

            <div class="card-body">
                <form action="{{  route('agents.update',$agent) }}" method="post"
                      class="form form-horizontal create-form">
                    @method('PATCH')
                    @csrf
                    <div class="row mb-3">
                        <div class="col">
                            <label for="name" class="control-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $agent->name }}">
                        </div>
                        <div class="col">
                            <label for="email" class="control-label">Email</label>
                            <input type="text" class="form-control" name="email" id="email" value="{{ $agent->email }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="phone" class="control-label">Phone No</label>
                            <input type="text" class="form-control" name="phone" placeholder="+254711000000" id="phone" value="{{ $agent->phone }}">
                        </div>

                        <div class="col">
                            <label for="surname" class="control-label">Type</label>
                            <select class="form-control" name="type">
                                @foreach($types as $type)
                                    <option
                                        @if($type == $agent->type)
                                            selected
                                        @endif
                                        value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="dept" class="control-label">Department</label>
                            <input type="text" class="form-control" id="dept" name="department" value="{{ $agent->department }}">
                        </div>
                        <div class="col">
                            <label for="pin" class="control-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="{{ $agent->country }}">
                        </div>
                    </div>

                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation ms-auto">Edit Agent</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection


