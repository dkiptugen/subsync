@extends('includes.body')
@section('content')
    <style>
        .bundle-section {
            border: 2px solid #330; /* Dark border */
            padding: 16px;
            margin: 20px 0;
            position: relative;
        }

        .bundle-section legend {
            font-weight: bold;
            padding: 2px 10px;
        }

        legend{
            font-size: 1.1rem !important;
        }

        /* Optional: if you prefer a 'label-like' header outside the box */
        .bundle-label {
            font-weight: bold;
            background: white;
            position: absolute;
            top: -12px;
            left: 20px;
            padding: 0 8px;
        }
    </style>
	<div class="col-12">
		<div class="card card-border-nation">
			<div class="card-header">
				<h3 class="card-title text-nation my-0">Add Product</h3>
			</div>
			<div class="card-body">
				<form action="{{ route('product.store') }}"
						method="post"
						class="form form-horizontal create-form">
					@csrf
					<div class="row mb-3">
						<div class="col-12 col-md-6">
							<label for="product_name"
									class="control-label">Product Name</label>
							<input type="text"
									name="product_name"
									id="product_name"
									class="form-control">
						</div>
                        <div class="col-12 col-md-2">
                            <label for="archive_days"
                                   class="control-label">Archive specifier days</label>
                            <input type="number"
                                   name="archive_days"
                                   id="archive_days"
                                   class="form-control" min="1" value="1">
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="archive_skip_days"
                                   class="control-label">Archive skip days (Sat,Sun...)</label>
                            <input type="text"
                                   name="archive_skip_days"
                                   id="archive_skip_days"
                                   class="form-control" >
                        </div>
						<div class="col-12 col-md-2">
							<label for="product-type"
									class="control-label">Product Type</label>
							<select name="type"
									id="product-type"
									class="form-control select2">
								<option value="epaper">Epaper</option>
								<option value="paywall">Paywall</option>
								<option value="other">Other</option>
							</select>
						</div>

					</div>
					<div class="row mb-3">
						<div class="col">
							<label for="payment_prefix"
									class="control-label">Product Identifier</label>
							<input type="text"
									name="payment_prefix"
									id="payment_prefix"
									class="form-control">
						</div>
						<div class="col">
							<label for="payment_methods"
									class="control-label">Payment Methods</label>
							<select name="payment_methods[]"
									id="payment_methods"
									class="form-control select2"
									multiple>
								@foreach($payment_methods as $payment_method)
									<option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="col">
							<label for="site"
									class="control-label">Site</label>
							<select name="site"
									id="site"
									class="form-control select2">
								@foreach($sites as $site)
									<option value="{{ $site->id }}">{{ $site->site_name }}</option>
								@endforeach
							</select>
						</div>

					</div>

					<div class="row mb-3">
						<div class="col">
                            <label for="product_link"
                                   class="control-label">Product Link</label>
                            <input type="text"
                                   name="product_link"
                                   id="product_link"
                                   class="form-control">
                        </div>

                        <div class="col">
                            <label for="product" class="control-label">Counterpart Product</label>
                            <select name="counterpart_id" id="counterpart" class="form-control">
                                <option value="">---None---</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name.' ('.$product->type.')' }}</option>
                                @endforeach
                            </select>
                        </div>
					</div>

					<div class="mb-3">
						<label for="payment_notification_link"
								class="control-label">Other Payment Notification Links</label>
						<textarea name="notification_link"
								id="payment_notification_link"
								class="form-control"
								placeholder="separate with comma for multiple notification Links"></textarea>
					</div>
					<div class="mb-3">
						<label class="form-check form-check-inline">
							<input class="form-check-input"
									type="checkbox"
									name="premium"
									checked
									value="1">
							<span class="form-check-label">
                                Premium
                            </span>
						</label>

                        <label class="form-check form-check-inline">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="bundle"
                                   value="1">
                            <span class="form-check-label">
                                Bundle
                            </span>
                        </label>
					</div>
                    <fieldset class="bundle-section">
                        <legend>For Bundles Only</legend>
                        <div class="mb-3">
                            <label class="control-label fw-800">Other sites</label>
                            <select name="sites[]"
                                    id="other_sites"
                                    class="form-control select2"
                                    multiple>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="control-label fw-800">Bundle Products</label>
                            <select name="children[]"
                                    id="children"
                                    class="form-control select2"
                                    multiple>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name.' ('.$product->type.')' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    <div class="mb-3">
                        <label for="description" class="control-label">Description</label>
                        <textarea name="description" id="description" rows="4" class="form-control editor"></textarea>
                    </div>
					<div class="mb-3 d-flex">
						<button type="submit"
								class="btn btn-nation ms-auto">Save Product
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
