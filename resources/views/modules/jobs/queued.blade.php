@extends('includes.body')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title my-0 text-nation">Queued Jobs</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="queued_jobs_table" class="table table-striped table-condensed" >
                    <thead class="text-white bg-nation">
                    <tr>
                        <th>Id</th>
                        <th>Attempts</th>
                        <th>Queue</th>
                        <th>Payload</th>
                        <th>Reserved at</th>
                        <th>Available at</th>
                        <th>Created at</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    <tfoot class="text-white bg-nation">
                    <tr>
                        <th>Id</th>
                        <th>Attempts</th>
                        <th>Queue</th>
                        <th>Payload</th>
                        <th>Reserved at</th>
                        <th>Available at</th>
                        <th>Created at</th>
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
        $('#queued_jobs_table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('jobs.queued_datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "id" },
                { "data": "attempts" },
                { "data": "queue" },
                { "data": "payload" },
                { "data": "reserved_at" },
                { "data": "available_at"},
                { "data": "created_at"}
            ],
            "order": [[ 1, "asc" ]]
        });
    </script>
@endsection

