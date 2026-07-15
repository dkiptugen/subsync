<div class="card card-border-nation mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title my-0 text-nation">{{ $title }}</h3>
        <form action="{{ $exportRoute }}" method="POST" class="mb-0">
            @csrf
            <input type="hidden" name="startdate" value="{{ $filters['startdate']->toDateString() }}">
            <input type="hidden" name="enddate" value="{{ $filters['enddate']->toDateString() }}">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
            @foreach($filters['product'] ?? [] as $productId)
                <input type="hidden" name="product[]" value="{{ $productId }}">
            @endforeach
            @foreach($filters['ratetype'] ?? [] as $rateTypeId)
                <input type="hidden" name="ratetype[]" value="{{ $rateTypeId }}">
            @endforeach
            <button type="submit" class="btn btn-sm btn-outline-nation">
                <i class="fas fa-file-excel"></i> Export
            </button>
        </form>
    </div>
    <div class="card-body">
        <form action="{{ $formRoute }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="startdate" class="form-label">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="startdate" id="startdate" class="form-control @error('startdate') is-invalid @enderror" value="{{ old('startdate', $filters['startdate']->toDateString()) }}">
                @error('startdate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label for="enddate" class="form-label">End Date <span class="text-danger">*</span></label>
                <input type="date" name="enddate" id="enddate" class="form-control @error('enddate') is-invalid @enderror" value="{{ old('enddate', $filters['enddate']->toDateString()) }}">
                @error('enddate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            @isset($products)
                <div class="col-md-3">
                    <label for="product" class="form-label">Products</label>
                    <select name="product[]" id="product" class="form-control @error('product') is-invalid @enderror" multiple>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(in_array($product->id, $filters['product'], true))>{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                    @error('product')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endisset
            @isset($rateTypes)
                <div class="col-md-3">
                    <label for="ratetype" class="form-label">Rate Types</label>
                    <select name="ratetype[]" id="ratetype" class="form-control @error('ratetype') is-invalid @enderror" multiple>
                        @foreach($rateTypes as $rateType)
                            <option value="{{ $rateType->id }}" @selected(in_array($rateType->id, $filters['ratetype'], true))>{{ $rateType->name }}</option>
                        @endforeach
                    </select>
                    @error('ratetype')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endisset
            @if($showStatus ?? false)
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                        <option value="">All</option>
                        <option value="active" @selected($filters['status'] === 'active')>{{ $statusLabels['active'] ?? 'Active' }}</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>{{ $statusLabels['inactive'] ?? 'Inactive' }}</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif
            <div class="col-md-3 d-flex align-items-end justify-content-end">
                <button type="submit" class="btn btn-sm btn-outline-nation">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>
