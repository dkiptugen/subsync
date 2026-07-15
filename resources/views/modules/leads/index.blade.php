@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title text-nation my-0">
                    Leads
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-condensed" id="leads-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Title</th>
                                <th>Link</th>
                                <th>Date</th>
                                <th>Clicks</th>
                                <th>Amount Paid</th>
                                <th>Package</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Title</th>
                                <th>Link</th>
                                <th>Date</th>
                                <th>Clicks</th>
                                <th>Amount Paid</th>
                                <th>Package</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('footer')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.renderDataTable('#leads-table', {
                "processing": true,
                "serverSide": true,
                "ajax":{
                    "url": "{{ route('lead.datatable') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{ _token: "{{csrf_token()}}"}
                },
                "columns": [
                    { "data": "pos" },
                    { "data": "product" },
                    { "data": "title" },
                    { "data": "link" },
                    { "data": "date" },
                    { "data": "clicks" },
                    { "data": "amount" },
                    { "data": "package" }

                ],
                "order": [[ 4, "desc" ]]
            });
        });
    </script>

@endsection
