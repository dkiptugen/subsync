<div class="row  align-items-center">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body text-center">
                <div class="mb-4">
                    <img src="https://img.icons8.com/color/96/000000/checked.png" alt="Success">
                </div>
                <h2 class="text-success">Payment Successful!</h2>
                <p class="lead">Thank you for your purchase. Your payment has been processed successfully.</p>

                                <section class="page-hero">
Receipt
                </section>
<div class="card mt-4">

                    <div class="card-body">
                        <p><strong>Transaction ID:</strong>{{ $account }}</p>
                        <p><strong>Amount Paid:</strong> {{ $amount }}</p>
                        <p><strong>Date:</strong>{{ $payment_date }}</p>
                    </div>
                </div>

                <a href="https://www.nation.africa" class="btn btn-primary mt-4">Return to Homepage</a>
            </div>
        </div>
    </div>
</div>
@endsection

