@extends('includes.body')
@section('content')
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title my-0 text-nation">Edit Corporate Subscription</h3>
			</div>
			<div class="card-body">
				<form action="{{ route('organization.subscription.update',[$organizationId,$subscription->id]) }}"
						method="post"
						class="form form-horizontal create-form">
					@csrf
					@method('put')
					<div class="mb-3">
						<label for="title"
								class="control-label">Title</label>
						<input type="text"
								name="title"
								id="title"
								class="form-control"
								value="{{ $subscription->title }}">
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
									<option value="{{ $organization->id }}"
											@if($subscription->organization_id == $organization->id) selected @endif>{{ $organization->name }}</option>
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
									<option value="{{ $product->id }}"
											@if($subscription->organization_id == $product->id) selected @endif>{{ $product->product_name }}</option>
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
									<option value="{{ $ratetype->id }}"
											@if($ratetype->id == $subscription->rate_type_id) selected @endif>{{ $ratetype->name }}</option>
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
									class="form-control datesingle"
									value="{{ $subscription->start_date }}">
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
									class="form-control"
									value="{{ $subscription->accounts }}">
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
									value="{{ $subscription->channel }}"
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
									class="form-control"
									value="{{ $subscription->receipt }}">
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
									class="form-control"
									value="{{ $subscription->amount }}">
							<!-- /#.form-control -->
						</div>
						<!-- /.col -->
					</div>
					<div class="mb-3">
						<label class="form-check form-check-inline">
							<input class="form-check-input"
									type="checkbox"
									name="status"
									@if($subscription->status) checked
									@endif value="1">
							<span class="form-check-label">
                                Active
                            </span>
						</label>
					
					</div>
					<div class="mb-3">
						<label for="reason"
								class="control-label">Reason</label>
						<textarea name="reason"
								id="reason"
								cols="30"
								rows="10"
								class="form-control">{{ $subscription->activator_reason }}</textarea>
					</div>
					<div class="mb-3 d-flex">
						<button class="btn btn-nation ms-auto">Update Subscription</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

