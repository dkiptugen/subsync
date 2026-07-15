@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-nation my-0">Edit Site</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('site.update',$site->id) }}" method="post"
                      class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <div class="mb-3">
                        <label for="site_name" class="control-label">Site Name</label>
                        <input type="text" name="site_name" id="site_name" class="form-control"
                               value="{{ $site->site_name }}">
                    </div>
                    <div class="mb-3 ">

                        <label for="site_link" class="control-label">Site Link</label>
                        <input type="text" name="site_url" id="site_link" class="form-control"
                               value="{{ $site->site_url }}">

                    </div>

                    <div class="mb-3">
                        <label for="region" class="control-label">Region</label>
                        <select name="region_id" id="region" class="form-control select2">
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}"
                                        @if($region->id == $site->region_id) selected @endif>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 ">
                        <label for="webhook_url" class="control-label">Webhook URL</label>
                        <input type="text" name="webhook_url" id="webhook_url" class="form-control" value="{{ $site->callback_url }}">
                    </div>

                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-nation ms-auto">Edit Site</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
