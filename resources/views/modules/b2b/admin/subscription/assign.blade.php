@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="my-0 text-nation card-title">{{ $organization->name }}
                    Assign  : {{ $subscription->product->product_name.'('.$subscription->start_date.'  -  '.$subscription->expiry_date.')' }}</h3>
                <div class="action">
                    <a href="{{ route('organization.subscription.assign_form',[$organization->id,$subscription->id]) }}"
                       class="btn btn-outline-nation">
                        <i class="fas fa-upload"></i>Assign User :
                        {{ $subscription->records.'/'.$subscription->accounts }}
                    </a>
                    <a href="{{ route('organization.subscription.assign_upload_form',[$organization->id,$subscription->id]) }}"
                       class="btn btn-outline-nation">
                        <i class="fas fa-upload"></i>Upload Users :
                        {{ $subscription->records.'/'.$subscription->accounts }}
                    </a>
                </div>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-hover table-striped" id="user-table">
                        <thead class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
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
        $('#user-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('organization.subscription.assign_datatable',[$organization->id,$subscription->id]) }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "name"},
                {"data": "email"},
                {"data": "registered"},
                {"data": "status"},
                {"data": "action","orderable":false}
            ],
            "order": [[3, "desc"]]
        });
    </script>
@endsection

