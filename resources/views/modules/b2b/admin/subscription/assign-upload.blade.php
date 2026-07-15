@extends('includes.body')
@section('content')
    <div class="col-12">
        <section class="page-hero d-flex align-item-center justify-content-between">
<h3 class="my-0 card-title text-nation">{{ $organization->name }} : Users upload</h3>
            <div class="">{{ $subscription->product->product_name.'('.$subscription->start_date.'  -  '.$subscription->expiry_date.')' }}</div>
    </section>
<div class="card">

        <div class="card-body">
            <div id="dropzone" class="d-flex align-items-center justify-content-center"
                 data-endpoint="{{ route('organization.subscription.assign_upload',[$organization->id,$subscription->id]) }}">
                <div class="">
                    <p>Drag and drop files here, or click to select files.
                        <br>
                        <a href="{{ asset('users.xlsx') }}">Download Sample excel Sheet.</a>
                        <br>
                        The password is <strong>optional</strong> and can be used on emails that cannot be verified.
                    </p>

                    <input type="file" id="fileInput" multiple
                           data-endpoint="{{ route('organization.subscription.assign_upload',[$organization->id,$subscription->id]) }}">
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
