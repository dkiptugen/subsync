@extends('includes.body')
@section('content')
    <div class="col-12">
                <section class="page-hero d-flex justify-content-between align-items-center">
<h3 class="card-title my-0 text-nation">Currency Conversion Rate</h3>
                <a href="{{ route('currency.create') }}" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-plus"></i>
                    Add conversion rate
                </a>
        </section>
<div class="card">

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-condensed" id="converstion-rate-table">
                        <thead class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Region</th>
                                <th>Currency</th>
                                <th>Currency Amount</th>
                                <th>Dollar Amount</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Region</th>
                                <th>Currency</th>
                                <th>Currency Amount</th>
                                <th>Dollar Amount</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Author</th>
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
@section('header')

@endsection

@section('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initDataTable('#converstion-rate-table',
                "{{ route('currency.datatable') }}",
                [
                { "data": "pos" },
                { "data": "region" },
                { "data": "currency" },
                { "data": "currency_amount" },
                { "data": "dollar_amount" },
                { "data": "startdate" },
                { "data": "enddate" },
                {"data": "author"},
                { "data": "status" },
                {"data": "action","orderable":false}
            ],
                [[7, 'desc']]);
        });
    </script>
@endsection
