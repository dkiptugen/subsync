@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex justify-content-between align-items-center">
<h3 class="card-title my-0 text-nation">
                    Email Templates
                </h3>
                @can('create_email_template')
                    <a href="{{ route('email_template.create') }}" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-plus me-2"></i> <span>Create Email Template</span>
                    </a>
                @endcan
        </section>
<div class="card">

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped" id="email-template-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Products</th>
                                <th>Creator</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Products</th>
                                <th>Creator</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
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
        window.renderDataTable('#email-template-table', {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('email_template.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "name"},
                {"data": "products", "orderable": false},
                {"data": "creator"},
                {"data": "type"},
                {"data": "status"},
                {"data": "action", "orderable": false}

            ],
            "order": [[1, "asc"]]
        });
        });
    </script>
@endsection
