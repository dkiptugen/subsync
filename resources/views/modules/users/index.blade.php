@extends('includes.body')
@section('content')
<div class="col-12">
    <div class="card card-border-nation">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title my-0 text-nation">Users</h3>
            <div class="actionbtn">
                <a href="{{ route('user.create') }}" class="btn btn-outline-nation btn-sm">
                    <i class="fas fa-plus"></i> Add User
                </a>
                <a href="{{ route('user.export') }}" class="btn btn-outline-nation btn-sm">
                    <i class="fas fa-file-export"></i> Export
                </a>
            </div>
        </div>
        <div class="card-body table-responsive">
            <div class="table-responsive">
                <table id="userstable" class="table table-striped table-hover table-condensed">
                    <thead class="text-white bg-nation">
                    <tr>
                        <th>*</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Notify</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tfoot class="text-white bg-nation">
                    <tr>
                        <th>*</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Notify</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
@section('header')

@endsection
@section('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        window.renderDataTable('#userstable', {
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('user.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "id","orderable": false },
                { "data": "name" },
                { "data": "email" },
                { "data": "status" },
                { "data": "notify" },

                { "data": "role","orderable": false },
                { "data": "action","name": "action","orderable": false  }
            ],
            "order": [[ 1, "asc" ]]


        });
        });
    </script>
@endsection
