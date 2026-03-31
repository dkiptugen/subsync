@extends('includes.body')
@section('content')

        <div class="col-12">
            <div class="table-responsive">
                <div class="card card-border-nation">
                    <div class="card-header">
                        <h3 class="my-0 card-title text-nation">Logs</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-condensed" id="logger">
                            <thead class="text-white bg-nation">
                            <tr>
                                <th>#</th>
                                <th>Action</th>
                                <th>Excecutor</th>
                                <th>Model</th>
                                <th>Affected Id</th>
                                <th>Change</th>
                                <th>Time</th>
                            </tr>
                            </thead>
                            <tbody>


                            </tbody>
                            <tfoot class="text-white bg-nation">
                            <tr>
                                <th>#</th>
                                <th>Action</th>
                                <th>Excecutor</th>
                                <th>Model</th>
                                <th>Affected Id</th>
                                <th>Change</th>
                                <th>Time</th>
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
        $('#logger').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('logs.user.datatable',$user) }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                {"data": "action","orderable":false},
                { "data": "executer" },
                { "data": "model" },
                { "data": "affectedid" },
                { "data": "change" },
                { "data": "time"}

            ],
            "order": [[ 6, "desc" ]]
        });
    </script>

@endsection
