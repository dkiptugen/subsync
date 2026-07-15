@extends('includes.body')

@section('content')
<div class="col-12">
        <section class="page-hero">
<h3 class="card-title my-0 text-nation">Failed Jobs</h3>
    </section>
<div class="card">

        <div class="card-body">
            <table id="failed_jobs_table" class="table table-striped table-condensed" >
                <thead class="text-white bg-nation">
                    <tr>
                        <th>Id</th>
                        <th>Connection</th>
                        <th>Queue</th>
                        <th>Payload</th>
                        <th>Exception</th>
                        <th>Failed At</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot class="text-white bg-nation">
                    <tr>
                        <th>Id</th>
                        <th>Connection</th>
                        <th>Queue</th>
                        <th>Payload</th>
                        <th>Exception</th>
                        <th>Failed At</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>



</div>
@endsection
@section('header')
@endsection
@section('footer')
    <script>
        $('#failed_jobs_table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('jobs.failed_datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "id" },
                { "data": "connection" },
                { "data": "queue" },
                { "data": "payload" },
                { "data": "exception" },
                { "data": "failed_at"}
            ],
            "order": [[ 1, "asc" ]]
        });
    </script>
@endsection
