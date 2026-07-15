@extends('includes.body')
@section('content')
    <div class="col-12">
        <section class="page-hero">
<h3 class="card-title my-0 text-nation">Add User</h3>
    </section>
<div class="card">

        <div class="card-body">
            <form action="{{ route('user.export') }}" method="post" class="form form-horizontal create-form">
                @csrf
                <div class="row mb-3">
                    <div class="col"></div>
                    <div class="col"></div>

                    <label for="" class="control-label"></label>
                   <input type="date" name="" id="">
                </div>


            </form>
        </div>
    </div>
</div>
@endsection
