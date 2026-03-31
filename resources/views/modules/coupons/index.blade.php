@extends('includes.body')
@section('title',$title)
@section('description',$description)
@section('keywords',$keywords)
@section('logo',$logo)
@section('image',$image)
@section('content')
    <section class="card card-border-nation">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title my-0 text-nation">Coupon</h3>
        <a href="{{ route('coupon.create') }}" class="btn btn-sm btn-outline-nation">
            <i class="fas fa-plus "></i> <span>Add Coupon</span>
        </a>
    </div>
    <div class="card-body">
        <table class="table table-striped" id="promo-table">
            <thead class="bg-nation text-white">
                <tr>
                    <th>#</th>
                    <th>Promocode</th>
                    <th>Type</th>
                    <th>Product</th>
                    <th>Rate</th>
                    <th>Discount</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>Expiry Date</th>
                    <th>Usage</th>
                    <th>Agent</th>
                    <th>Country</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot class="bg-nation text-white">
                <tr>
                    <th>#</th>
                    <th>Promocode</th>
                    <th>Type</th>
                    <th>Product</th>
                    <th>Rate</th>
                    <th>Discount</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>Expiry Date</th>
                    <th>Usage</th>
                    <th>Agent</th>
                    <th>Country</th>
                    <th>Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

@endsection
@section('header')

@endsection
@section('footer')
    <script>
        $('#promo-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('coupon.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "code"},
                {"data": "type"},
                {"data": "product"},
                {"data": "rate_type"},
                {"data": "discount"},
                {"data": "status"},
                {"data": "startdate"},
                {"data": "expirydate"},
                {"data": "usage"},
                {"data": "agent"},
                {"data": "region"},
                {"data": "action","orderable":false}
            ],
            "order": [[1, "asc"]]


        });
    </script>
@endsection
