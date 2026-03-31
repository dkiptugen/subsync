@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex justify-content-between align-itens-center">
                <h3 class="card-title my-0 text-nation">{{ $organization->name }} Dependants</h3>
                <div class="tns">
                    @canaccess('organization.user_create')
                    <a href="{{ route('organization.user_create',$organization->id) }}" class="btn btn-sm btn-outline-nation">
                        <i class="fas fa-plus mr-1"></i>Add Dependants
                    </a>
                    @endcanaccess
                    @canaccess('organization.user_uploads')
                    <a href="{{ route('organization.upload',$organization->id) }}" class="btn btn-sm btn-outline-nation">
                        <i class="fas fa-upload mr-1"></i>Bulk Upload
                    </a>
                    @endcanaccess
                    @canaccess('organization.user_export')
                    <a href="{{ route('organization.user_export',$organization->id) }}" class="btn btn-sm btn-outline-nation">
                        <i class="fas fa-file-export mr-1"></i>Export
                    </a>
                    @endcanaccess
                </div>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-hover" id="User-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Notifying</th>
                                <th>Last Login</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Notifying</th>
                            <th>Last Login</th>
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
        $('#User-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('organization.user_datatable',$organization->id) }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                { "data": "name" },
                { "data": "email" },
                { "data": "phone"},
                { "data": "status" },
                { "data": "notifying"},
                { "data": "last_login" },
                {"data": "action","orderable":false}
            ],
            "order": [[ 1, "asc" ]]
        });
    </script>
@endsection
