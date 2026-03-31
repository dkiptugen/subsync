@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title text-nation my-0">Add Site</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('site.store') }}" method="post" class="form form-horizontal create-form">
                    @csrf
                    <div class="form-group">
                        <label for="site_name" class="control-label">Site Name</label>
                        <input type="text" name="site_name" id="site_name" class="form-control">
                    </div>
                    <div class="form-group ">
                        <label for="site_link" class="control-label">Site Link</label>
                        <input type="text" name="site_url" id="site_link" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="region" class="control-label">Region</label>
                        <select name="region_id" id="region" class="form-control select2">
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group ">
                        <label for="webhook_url" class="control-label">Webhook URL</label>
                        <input type="text" name="webhook_url" id="webhook_url" class="form-control">
                    </div>

                    <div class="form-group d-flex">
                        <button type="submit" class="btn btn-nation ml-auto">Save Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
