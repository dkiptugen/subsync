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
				<section class="page-hero">
<h3 class="card-title text-nation my-0">Edit Product</h3>
		</section>
<div class="card">

			<div class="card-body">
				<form action="{{ route('product.update',$product->id) }}"
						method="post"
						class="form form-horizontal create-form">
					@csrf
					@method('put')
					<div class="row mb-3">
						<div class="col-12 col-md-6">
							<label for="product_name"
									class="control-label">Product Name</label>
							<input type="text"
									name="product_name"
									id="product_name"
									class="form-control"
									value="{{ $product->product_name }}">
						</div>
                        <div class="col-12 col-md-2">
                            <label for="archive_days"
                                   class="control-label">Archive specifier days</label>
                            <input type="number"
                                   name="archive_days"
                                   id="archive_days"
                                   class="form-control" min="1" value="{{$product->archive_days}}">
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="archive_skip_days"
                                   class="control-label">Archive skip days (Sat,Sun...)</label>
                            <input type="text"
                                   name="archive_skip_days"
                                   id="archive_skip_days"
                                   class="form-control" value="{{ $product->archive_skip_days }}" >
                        </div>
						<div class="col-12 col-md-2">
							<label for="product-type"
									class="control-label">Product Type</label>
							<select name="type"
									id="product-type"
									class="form-control select2">
								<option value="epaper"
										@if($product->type == 'epaper') selected @endif>Epaper
								</option>
								<option value="paywall"
										@if($product->type == 'paywall') selected @endif>Paywall
								</option>
								<option value="other"
										@if($product->type == 'other') selected @endif>Other
								</option>
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
									class="form-control"
									value="{{ $product->identifier }}">
						</div>
						<div class="col">
							<label for="payment_methods"
									class="control-label">Payment Methods</label>
							<select name="payment_methods[]"
									id="payment_methods"
									class="form-control select2"
									multiple>
								@foreach($payment_methods as $payment_method)
									<option value="{{ $payment_method->id }}"
											@if(in_array($payment_method->id,$product->payment_methods->pluck('id')->toArray())) selected @endif>{{ $payment_method->name }}</option>
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
									<option @if($product->site_id == $site->id) selected @endif value="{{ $site->id }}">{{ $site->site_name }}</option>
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
                                   class="form-control"
                                   value="{{ $product->product_link }}">
                        </div>
                        <div class="col">
                            <label for="product" class="control-label">Counterpart Product</label>
                            <select name="counterpart_id" id="counterpart" class="form-control">
                                <option value="">---None---</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}"
                                            @if($prod->id == $product->counterpart_id) selected @endif
                                    >{{ $prod->product_name.' ('.$prod->type.')' }}</option>
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
								placeholder="separate with comma for multiple notification Links">{{ $product->payment_notification_link }}</textarea>
					</div>
					<div class="mb-3">
						<label class="form-check form-check-inline">
							<input class="form-check-input"
									type="checkbox"
									name="status"
									@if($product->status) checked
									@endif value="1">
							<span class="form-check-label">
                                Active
                            </span>
						</label>
						<label class="form-check form-check-inline">
							<input class="form-check-input"
									type="checkbox"
									name="premium"
									@if($product->is_premium) checked
									@endif value="1">
							<span class="form-check-label">
                                Premium
                            </span>
						</label>
                        <label class="form-check form-check-inline">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="bundle"
                                   @if($product->is_bundled) checked @endif
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
                                    <option
                                        @if(in_array($site->id,$product->sites->pluck('id')->toArray()))
                                            selected
                                        @endif
                                        value="{{ $site->id }}">{{ $site->site_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="control-label fw-800">Bundle Products</label>
                            <select name="children[]"
                                    id="children"
                                    class="form-control select2"
                                    multiple>
                                @foreach($products as $sproduct)
                                    <option
                                        @if(in_array($sproduct->id,$product->children->pluck('id')->toArray()))
                                            selected
                                        @endif
                                        value="{{ $sproduct->id }}">{{ $sproduct->product_name.' ('.$sproduct->type.')' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
					<!-- /.form-group -->
                    <div class="mb-3">
                        <label for="description" class="control-label">Description</label>
                        <textarea name="description" id="description" rows="4" class="form-control editor">{{$product->description}}</textarea>
                    </div>
					<div class="mb-3 d-flex">
						<button type="submit"
								class="btn btn-nation ms-auto">Update Product
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
