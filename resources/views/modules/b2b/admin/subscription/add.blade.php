@extends('includes.body')
@section('content')
	<div class="col-12">
				<section class="page-hero">
<h3 class="card-title my-0 text-nation">Add Corporate Subscription</h3>
		</section>
<div class="card">

			<div class="card-body">
				<form action="{{ route('organization.subscription.store',$organizationId) }}"
						method="post"
						class="form form-horizontal create-form">
					@csrf
					<div class="mb-3">
						<label for="title"
								class="control-label">Title</label>
						<input type="text"
								name="title"
								id="title"
								class="form-control">
					</div>
					<div class="row mb-3">
						<div class="col">
							<label for="organization"
									class="control-label">Organization</label>
							<!-- /.control-label -->
							<select name="organization"
									id="organization"
									class="select2 form-control">
								@foreach($organizations as $organization)
									<option value="{{ $organization->id }}">{{ $organization->name }}</option>
								@endforeach
							</select>
							<!-- /#.select2 form-control -->
						</div>
						<!-- /.col -->
						<div class="col">
							<label for="product"
									class="control-label">Product</label>
							<!-- /.control-label -->
							<select name="product"
									id="product"
									class="select2 form-control">
								@foreach($products as $product)
									<option value="{{ $product->id }}">{{ $product->product_name }}</option>
								@endforeach
							</select>
							<!-- /#.select2 form-control -->
						</div>
						<!-- /.col -->
					</div>
					<!-- /.form-group row -->
					<div class="row mb-3">
						<div class="col">
							<label for="ratetype"
									class="control-label">Subscription Type</label>
							<!-- /.control-label -->
							<select name="ratetype"
									id="ratetype"
									class="select2 form-control">
								@foreach($ratetypes as $ratetype)
									<option value="{{ $ratetype->id }}">{{ $ratetype->name }}</option>
								@endforeach
							</select>
							<!-- /#.select2 form-control -->
						</div>
						<div class="col">
							<label for="startdate"
									class="control-label">Start Date</label>
							<!-- /.control-label -->
							<input type="text"
									name="startdate"
									id="startdate"
									class="form-control datesingle">
							<!-- /#.form-control -->
						</div>
						<!-- /.col -->
					</div>
					<!-- /.form-group row -->
					<div class="row mb-3">
						<div class="col">
							<label for="users"
									class="control-label">No of User</label>
							<!-- /.control-label -->
							<input type="text"
									name="users"
									id="users"
									class="form-control">
							<!-- /#.form-control -->
						</div>
						<!-- /.col -->
						<div class="col">
							<label for="paychannel"
									class="control-label">Pay Channel</label>
							<!-- /.control-label -->
							<input type="text"
									name="paychannel"
									id="paychannel"
									class="form-control">
							<!-- /#.form-control -->
						</div>
						<!-- /.col -->


					</div>
					<div class="row mb-3">
						<div class="col">
							<label for="receipt"
									class="control-label">Receipt No</label>
							<!-- /.control-label -->
							<input type="text"
									name="receipt"
									id="receipt"
									class="form-control">
							<!-- /#.form-control -->
						</div>
						<!-- /.col -->
						<div class="col">
							<label for="amount"
									class="control-label">Amount Paid</label>
							<!-- /.control-label -->
							<input type="number"
									name="amount"
									id="amount"
									class="form-control">
							<!-- /#.form-control -->
						</div>
						<!-- /.col -->
					</div>
					<div class="mb-3">
						<label for="reason"
								class="control-label">Reason</label>
						<textarea name="reason"
								id="reason"
								cols="30"
								rows="10"
								class="form-control"></textarea>
					</div>
					<div class="mb-3 d-flex">
						<button class="btn btn-nation ms-auto">Save Subscription</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
