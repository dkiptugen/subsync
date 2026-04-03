@php use Carbon\Carbon; @endphp
@extends('includes.body')
@section('content')

	<div class="col-12">
		<div class="card card-border-nation">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h3 class="card-title my-0 text-nation">User Whitelist</h3>
			</div>
			<div class="card-body">


				<form action="{{ route('whitelist.type.update',['organization',$whitelist->id]) }}"
						method="post"
						class="form form-horizontal create-form">
					@csrf
					@method('put')
					<div class="row mb-3">
						<div class="col">
							<label for="organization"
									class="control-label">Organization</label>
							<select name="organization"
									id="organization"
									class="form-control select2">
								@foreach($organizations as $organization)
									<option value="{{ $organization->id }}"
											@if($whitelist->whitelistable_id == $organization->id) selected @endif>{{ $organization->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="col">
							<label for="org-product"
									class="control-label">Product</label>
							<select name="product"
									id="org-product"
									class="form-control select2">
								@foreach($products as $product)
									<option value="{{ $product->id }}"
											@if($whitelist->product_id == $product->id) selected @endif>{{ $product->product_name }}</option>
								@endforeach
							</select>
						</div>

					</div>
					<div class="row mb-3">

						<div class="col">
							<label for="org-startdate"
									class="control-label">Startdate</label>
							<input type="date"
									name="startdate"
									id="org-startdate"
									class="form-control"
									value="{{ Carbon::parse($whitelist->startdate)->format('Y-m-d') }}">
						</div>
						<div class="col">
							<label for="org-enddate"
									class="control-label">Enddate</label>
							<input type="date"
									name="enddate"
									id="org-enddate"
									class="form-control"
									value="{{ Carbon::parse($whitelist->enddate)->format('Y-m-d') }}">
						</div>
					</div>
					<div class="mb-3">
						<label for="org-reason"
								class="control-label"> Reason</label>
						<textarea name="reason"
								id="org-reason"
								cols="30"
								rows="10"
								class="form-control">{{$whitelist->reason }}</textarea>
					</div>
					<div class="mb-3">
						<label class="form-check form-check-inline">
							<input class="form-check-input"
									type="checkbox"
									name="status"
									@if($whitelist->status) checked
									@endif value="1">
							<span class="form-check-label">
                                Active
                            </span>
						</label>

					</div>
					<div class="mb-3 d-flex">
						<button class="btn btn-nation ms-auto">Whitelist Organization</button>
					</div>
				</form>


			</div>
		</div>
	</div>

@endsection
