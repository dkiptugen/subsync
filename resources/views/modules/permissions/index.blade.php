@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex align-items-center justify-content-between">
<h3 class="my-0 text-nation">Permissions</h3>
        </section>
<div class="card">

            <div class="card-body ">
                <div class="table-responsive">
                    <table class="table table-striped " id="permissions-table">
                        <thead class="text-white bg-nation">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Access</th>
                                <th>Roles</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="text-white bg-nation">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Access</th>
                                <th>Roles</th>
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
        window.renderDataTable('#permissions-table', {
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('user.permissions.datatable',$userid??0) }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                { "data": "name" },
                { "data": "access" },
                { "data": "roles" },
                {"data": "action","orderable":false}
            ],
            "order": [[ 1, "asc" ]]
        });
        });
    </script>
@endsection
