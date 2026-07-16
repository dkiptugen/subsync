<section class="report-hero" aria-labelledby="report-hero-title">
    <div class="report-hero__header">
        <div>
            <p class="report-hero__eyebrow mb-1">Analytics report</p>
            <h1 id="report-hero-title" class="report-hero__title">{{ $title }}</h1>
            <p class="report-hero__summary mb-0">
                {{ $filters['startdate']->format('M d, Y') }} to {{ $filters['enddate']->format('M d, Y') }}
            </p>
        </div>
        <form action="{{ $exportRoute }}" method="POST" class="report-hero__export">
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
            <button type="submit" class="btn btn-outline-nation report-hero__button" aria-label="Export {{ $title }}">
                <i class="fas fa-file-excel"></i> Export
            </button>
        </form>
    </div>

    <form action="{{ $formRoute }}" method="GET" class="report-filter-grid" aria-label="{{ $title }} filters">
        <div class="report-filter-field">
            <label for="startdate" class="form-label">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="startdate" id="startdate" class="form-control @error('startdate') is-invalid @enderror" value="{{ old('startdate', $filters['startdate']->toDateString()) }}">
            @error('startdate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="report-filter-field">
            <label for="enddate" class="form-label">End Date <span class="text-danger">*</span></label>
            <input type="date" name="enddate" id="enddate" class="form-control @error('enddate') is-invalid @enderror" value="{{ old('enddate', $filters['enddate']->toDateString()) }}">
            @error('enddate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @isset($products)
            <div class="report-filter-field">
                <label for="product" class="form-label">Products</label>
                <select name="product[]" id="product" class="form-select js-choice report-filter-select @error('product') is-invalid @enderror" multiple data-placeholder="All products">
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
            <div class="report-filter-field">
                <label for="ratetype" class="form-label">Rate Types</label>
                <select name="ratetype[]" id="ratetype" class="form-select js-choice report-filter-select @error('ratetype') is-invalid @enderror" multiple data-placeholder="All rate types">
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
            <div class="report-filter-field">
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
        <div class="report-filter-actions">
            <button type="submit" class="btn btn-nation report-hero__button">
                <i class="fas fa-search"></i> Apply filters
            </button>
        </div>
    </form>
</section>
