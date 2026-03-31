@extends('includes.body')
@section('content')
	<div class="col-12">
		<div class="card card-border-nation">
			<div class="card-header d-flex align-items-center justify-content-between">
				<h3 class="my-0 text-nation card-title">Organizational Subscriptions</h3>
				@canaccess('organization.subscription.create')
				<a href="{{ route('organization.subscription.create',0) }}"
						class="btn btn-sm btn-outline-nation">
					<i class="fas fa-plus"></i>
					Add Subscription
				</a>
				@endcanaccess
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-condensed table-hover table-striped"
							id="subscription-table">
						<thead class="bg-nation text-white">
						<tr>
							<th>#</th>
							<th>Title</th>
							<th>Organization</th>
							<th>Product</th>
							<th>Subscription Type</th>
							<th>Rate</th>
							<th>Is Paid</th>
							<th>Dependants</th>
							<th>Assigned</th>
							<th>Subscription Date</th>
							<th>Expiry Date</th>
							<th>Created At</th>
							<th>Status</th>
                            <th>Last Modified</th>
							<th>Action</th>
						</tr>
						</thead>
						<tfoot class="bg-nation text-white">
						<tr>
							<th>#</th>
							<th>Title</th>
							<th>Organization</th>
							<th>Product</th>
							<th>Subscription Type</th>
							<th>Rate</th>
							<th>Is Paid</th>
							<th>Dependants</th>
							<th>Assigned</th>
							<th>Subscription Date</th>
							<th>Expiry Date</th>
							<th>Created At</th>
							<th>Status</th>
                            <th>Last Modified</th>
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
        $('#subscription-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('organization.subscription.datatable',$organizationId) }}",
                "dataType": "json",
                "type": "POST",
                "data": {_token: "{{csrf_token()}}"}
            },
            "columns": [
                {"data": "pos"},
                {"data": "title"},
                {"data": "organization"},
                {"data": "product"},
                {"data": "subscription_type","orderable":false},
                {"data": "cost"},
                {"data": "paid","orderable":false},
                {"data": "users"},
                {"data": "assigned"},
                {"data": "subdate"},
                {"data": "expirydate"},
                {"data": "created_at"},
                {"data": "status"},
                {"data": "updated_at","orderable":false},
                {"data": "action","orderable":false}
            ],
            "order": [[0, "desc"]]
        });
	</script>
@endsection
