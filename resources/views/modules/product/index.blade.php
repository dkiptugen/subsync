@extends('includes.body')
@section('content')
	<div class="col-12">
				<section class="page-hero d-flex justify-content-between align-items-center">
<h3 class="card-title my-0 text-nation">
					Products
				</h3>
				@can('create_product')
				<a href="{{ route('product.create') }}"
						class="btn btn-sm btn-outline-dark">
					<i class="fas fa-plus me-2"></i> <span>Add Product</span>
				</a>
				@endcan
		</section>
<div class="card">

			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-condensed table-striped"
							id="products-table">
						<thead class="bg-nation text-white">
						<tr>
							<th>#</th>
							<th>Name</th>
							<th>Site</th>
							<th>Product Prefix</th>
							<th>Product Type</th>
							<th>Premium</th>
                            <th>Bundle</th>
							<th>Payment Methods</th>
							<th>Link</th>
							<th>Author</th>
							<th>Status</th>
							<th>Date Created</th>
                            <th>Rates</th>
                            <th>Archive specifier days</th>
							<th>Action</th>
						</tr>
						</thead>
						<tfoot class="bg-nation text-white">
						<tr>
							<th>#</th>
							<th>Name</th>
							<th>Site</th>
							<th>Product Prefix</th>
							<th>Product Type</th>
							<th>Premium</th>
                            <th>Bundle</th>
							<th>Payment Methods</th>
							<th>Link</th>
							<th>Author</th>
							<th>Status</th>
							<th>Date Created</th>
                            <th>Rates</th>
                            <th>Archive specifier days</th>
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
        window.renderDataTable('#products-table', {
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('product.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "name"},
                {"data": "site"},
                {"data": "prefix"},
                {"data": "type"},
                {"data": "premium"},
                {"data": "bundle"},
                {"data": "payment_method"},
                {"data": "productlink"},
                {"data": "author"},
                {"data": "status"},
                {"data": "date_created"},
                {"data": "rates"},
                {"data": "archive_days"},
                {"data": "action","orderable":false}
            ],
            "order": [[1, "asc"]]
        });
        });
    </script>
@endsection
