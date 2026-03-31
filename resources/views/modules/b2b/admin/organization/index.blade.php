@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title text-nation my-0">Corporates</h3>
                @canaccess('organization.create')
                <a href="{{ route('organization.create') }}" class="btn btn-sm btn-outline-nation">
                    <i class="fas fa-plus"></i> Add Corporate
                </a>
                @endcanaccess
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-hover table-striped" id="organization-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone No</th>
                                <th>Reg No</th>
                                <th>KRA Pin</th>
                                <th>Admin Name</th>
                                <th>Admin Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone No</th>
                                <th>Reg No</th>
                                <th>KRA Pin</th>
                                <th>Admin Name</th>
                                <th>Admin Email</th>
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
        $('#organization-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('organization.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "name"},
                {"data": "address"},
                {"data": "phone_number"},
                {"data": "kra_pin"},
                {"data": "registration_no"},
                {"data": "admin_name"},
                {"data": "admin_email"},
                {"data": "status"},
                {"data": "action","orderable":false}
            ],
            "order": [[1, "asc"]]


        });
    </script>
@endsection
