@extends('includes.body')
@section('content')
    <div class="col-12">
        <section class="page-hero d-flex justify-content-between align-items-center">
            <h3 class="card-title my-0 text-nation">Media Events</h3>
            <a class="btn btn-outline-nation btn-sm" href="{{ route('media_events.create') }}">
                <i class="align-middle" data-feather="plus"></i> Add Event
            </a>
        </section>
        <div class="card" id="view-table" aria-labelledby="view-table">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="media-events-table" class="table table-condensed table-striped table-hover w-100">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Identifier</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('partials._delete-modal')

@endsection
@section("header")

@endsection
@section("footer")
@if(session('msg'))
   <script>
        toastr.success('{{ session("msg")}}','{{ session("header")}}');
    </script>
@endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.renderDataTable('#media-events-table', {
                ajax: "{{ route('media_events.datatable') }}",
                columns: [
                    {data: 'pos', orderable: false, searchable: false},
                    {data: 'name'},
                    {data: 'identifier'},
                    {data: 'status'},
                    {data: 'created_at'},
                    {data: 'actions', orderable: false, searchable: false}
                ],
                order: [[4, 'desc']],
                fixedHeader: true,
                responsive: true
            });

            document.addEventListener('click', function (event) {
                const button = event.target.closest('.deleteDialog');

                if (!button) {
                    return;
                }

                document.querySelector('#confirmDeleteModal #action').setAttribute('action', button.dataset.url);
                document.querySelector('#confirmDeleteModal #target').value = button.dataset.target || '';
                window.bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmDeleteModal')).show();
            });
        });
    </script>
@endsection
