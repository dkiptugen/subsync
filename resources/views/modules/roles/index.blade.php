@extends('includes.body')
@section('content')
    <div class="col-12">


                        <section class="page-hero d-flex justify-content-between align-items-center">
<h3 class="card-title my-0 text-nation">Roles</h3>
                    <a class="btn btn-outline-nation btn-sm"  href="{{ route('user.roles.create',$userid??0) }}" >
                        <i class="align-middle" data-feather="plus"></i> Add Role
                    </a>
            </section>
<div class="card" id="view-table" aria-labelledby="view-table" >

                <div class="card-body">
                    <div class="table-responsive w-100">
                        <table class="table table-striped " id="roles-table">
                            <thead class="text-white bg-nation">
                            <tr>
                                <th>#</th>
                                <th>Role Name</th>
                                <th>Access</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot class="text-white bg-nation">
                            <tr>
                                <th>#</th>
                                <th>Role Name</th>
                                <th>Access</th>
                                <th>Action</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>



        </div>



@endsection
@section("header")

@endsection
@section("footer")
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        $(document).ready(function() {
            window.renderDataTable('#roles-table', {
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('user.roles.datatable',$userid??0) }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": {_token: "{{csrf_token()}}"}
                },
                "columns": [
                    {"data": "pos"},
                    {"data": "name"},
                    {"data": "access"},
                    {"data": "action","orderable":false}
                ],
                "order": [[1, "asc"]],
                responsive: true

            });
        });
        });
    </script>
@endsection
