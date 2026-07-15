@extends('modules.front.digital.layout')

@section('title', 'Digital Payments')

@section('content')
    <section class="digital-hero digital-hero--compact">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('digital.auth') }}">Digital access</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payments</li>
                </ol>
            </nav>
            <p class="digital-eyebrow">Payments</p>
            <h1>Choose a product and payment channel.</h1>
            <p class="digital-hero__copy">Review active rates and payment methods connected to the subscription platform.</p>
        </div>
        <div class="digital-summary">
            <span>Methods</span>
            <strong>{{ $paymentMethods->count() }}</strong>
        </div>
    </section>

    <section class="digital-section">
        <div class="digital-payment-grid">
            @forelse($products as $product)
                <article class="digital-panel digital-payment-product">
                    <div class="digital-section__header">
                        <div>
                            <span class="digital-product-code">{{ $product->identifier }}</span>
                            <h2>{{ $product->product_name }}</h2>
                        </div>
                        @if($product->product_link)
                            <a class="btn btn-outline-dark btn-sm" href="{{ $product->product_link }}">Open</a>
                        @endif
                    </div>
                    <div class="digital-rates">
                        @forelse($product->rates as $rate)
                            <div class="digital-rate">
                                <div>
                                    <strong>{{ $rate->name }}</strong>
                                    <span>{{ $rate->period }} days</span>
                                </div>
                                <b>{{ $rate->currency }} {{ number_format((float) $rate->cost, 2) }}</b>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No active rates configured.</p>
                        @endforelse
                    </div>
                </article>
            @empty
                <p class="text-muted mb-0">No active digital products are available.</p>
            @endforelse
        </div>

        <div class="digital-panel mt-4">
            <h2>Payment methods</h2>
            <div class="digital-methods">
                @forelse($paymentMethods as $paymentMethod)
                    <span>{{ $paymentMethod->name }}</span>
                @empty
                    <p class="text-muted mb-0">No active payment methods are configured.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
