@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation" id="view-table" aria-labelledby="view-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title my-0 text-nation">Sales Agents</h3>
                <div>
                    @can('create_sales_agent')
                        <a class="btn btn-outline-dark btn-sm" href="{{ route('agents.create') }}">
                            <i class="align-middle" data-feather="plus"></i> Add Agent </a>
                    @endcan
                    @can('export_sales_agent')
                        <a href="{{ route('agents.import') }}" class="btn btn-sm btn-outline-dark mx-2 px-2">
                            <i class="fas fa-upload"></i>Bulk import
                        </a>
                    @endcan
                </div>


            </div>
            <div class="card-body">

            </div>
        </div>
    </div>

    @include('partials._delete-modal')

@endsection
@section("header")

@endsection
@section("footer")

@endsection
