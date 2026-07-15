@extends('includes.body')
@section('content')

        <div class="col-12">
            <div class="table-responsive">
                                <section class="page-hero">
<h3 class="my-0 card-title text-nation">Logs</h3>
                </section>
<div class="card">

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
        document.addEventListener('DOMContentLoaded', function () {
        window.renderDataTable('#logger', {
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('user.logs.datatable',$user) }}",
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
        });
    </script>

@endsection
