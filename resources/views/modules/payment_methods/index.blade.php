@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex align-items-center justify-content-between">
<h3 class="my-0 text-nation">Payment Methods</h3>
                @can('create_payment_method')
                <a class="btn btn-outline-nation btn-sm " href="{{ route('payment_method.create') }}">
                    <i class="align-middle" data-feather="plus"></i> Add Payment Method
                </a>
                @endcan
        </section>
<div class="card">

            <div class="card-body ">
                <div class="table-responsive">
                    <table class="table table-striped table-condensed" id="payment-method-table">
                        <thead class="bg-nation text-white" >
                            <tr>
                                <th>#</th>
                                <th>Identifier</th>
                                <th>Provider</th>
                                <th>Status</th>
                                <th>Notifying</th>
                                <th>Creator</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Identifier</th>
                                <th>Provider</th>
                                <th>Status</th>
                                <th>Notifying</th>
                                <th>Creator</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section("footer")
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        window.renderDataTable('#payment-method-table', {
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('payment_method.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                { "data": "identifier" },
                { "data": "provider" },
                { "data": "status" },
                { "data": "notify" },
                { "data": "creator" },
                { "data": "date_created" },
                {"data": "action","orderable":false}
            ],
            "order": [[ 1, "asc" ]]
        });

        });
    </script>
@endsection
