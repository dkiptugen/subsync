@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex justify-content-between align-itens-center">
<h3 class="card-title my-0 text-nation">Dependants</h3>
                <div class="tns">
                    @canaccess('client_users.create')
                    <a href="{{ route('client_users.create') }}" class="btn btn-sm btn-outline-nation">
                        <i class="fas fa-plus me-1"></i>Add Dependants
                    </a>
                    @endcanaccess
                    @canaccess('client_users.uploadform')
                    <a href="{{ route('client_users.uploadform',\Illuminate\Support\Facades\Auth::user()->organization_id) }}"
                       class="btn btn-sm btn-outline-nation">
                        <i class="fas fa-upload me-1"></i>Bulk Upload
                    </a>
                    @endcanaccess
                </div>
        </section>
<div class="card">

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-hover" id="User-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
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
        document.addEventListener('DOMContentLoaded', function () {
        window.renderDataTable('#User-table', {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('client_users.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "name"},
                {"data": "email"},
                {"data": "status"},
                {"data": "action","orderable":false}
            ],
            "order": [[1, "asc"]]
        });
        });
    </script>
@endsection
