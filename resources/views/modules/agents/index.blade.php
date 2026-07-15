@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex justify-content-between align-items-center">
<h3 class="card-title my-0 text-nation">Sales Agents</h3>
                <div>
                    @can('create_sales_agent')
                        <a class="btn btn-outline-dark btn-sm" href="{{ route('agents.create') }}">
                            <i class="align-middle" data-feather="plus"></i> Add Agent </a>
                    @endcan
                    @can('export_sales_agent')
                        <a href="{{ route('agents.import') }}" class="btn btn-sm btn-outline-dark mx-2 px-2">
                            <i class="fas fa-upload"></i>Bulk import
                        </a>
                    @endcan
                </div>
        </section>
<div class="card" id="view-table" aria-labelledby="view-table">

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-hover table-striped w-100" id="agents-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Department</th>
                                <th>Country</th>
                                <th>Corporates</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Department</th>
                                <th>Country</th>
                                <th>Corporates</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('partials._delete-modal')

@endsection
@section("header")

@endsection
@section("footer")
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.renderDataTable('#agents-table', {
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('agents.datatable') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": {_token: "{{ csrf_token() }}"}
                },
                "columns": [
                    {"data": "pos"},
                    {"data": "name"},
                    {"data": "email"},
                    {"data": "phone"},
                    {"data": "type"},
                    {"data": "department"},
                    {"data": "country"},
                    {"data": "organizations", "orderable": false},
                    {"data": "action", "orderable": false}
                ],
                "order": [[1, "asc"]]
            });
        });
    </script>
@endsection
