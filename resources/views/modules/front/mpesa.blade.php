<div class="row  align-items-center">

    <main class=" py-4">
        <div class="payment container w-100 h-100">
            <div class="row h-100">
                <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">
                        <div class="card">
                            <div class="card-body">
                                <div class="">
                                   
                                    <img src="{{ asset('assets/img/mpesa.png') }}" height="50" alt=" mpesa logo">
                                    <h2 class="font-18">Payment Option One</h2>
                                    <hr>
                                    <p>Scan this qr code on your mpesa App</p>
                                    <div class="d-flex justify-content-center  my-4">
                                        <img src="data:image/png;base64, {!! $qr !!}  " alt="" height="200" width="200" class="img-fluid">
                                    </div>
                                    <h2 class="font-18">Payment Option Two</h2>
                                    <p>
                                        Enter your M-PESA registered phone number below and click Pay Now then check
                                        your mobile phone handset for an instant payment request from Safaricom M-PESA.
                                    </p>
                                    <form action="{{ route('api.stkpush') }}" class="form form-horizontal pay-form"
                                          method="post">
                                        @csrf
                                        <input type="hidden" name="amount" value="{{ $amount }}">
                                        <input type="hidden" name="account" value="{{ $account }}">
                                        <input type="hidden" name="identifier" value="{{ $payment_method->identifier }}">
                                        <input type="hidden" name="description" value="payment">
                                        <div class="form-group">
                                            <label for="checkout_phone">M-PESA Registered Phone Number</label>
                                            <div class="input-group">
                                                <input type="text" name="msisdn" id="checkout_phone"
                                                       class="form-control" placeholder="Phone Number">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-nation" id="pay-now-btn">Pay Now</button>
                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                    <span class="message"></span>
                                </div>
                                <div class="mt-4 mb-2">
                                    <h2 class="font-18">Payment Option Three</h2>
                                    <hr>
                                    <p>Paybill No: <strong>{{ $payment_method->configuration['shortcode'] }}</strong></p>
                                    <p>Account No: <strong>{{ $account }}</strong></p>
                                    <p>Amount: <strong>{{ $amount }}</strong></p>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


</div>
@endsection
@section('footer')
    <script>
        // pusherScript.js
        // Pusher.logToConsole = true;
        
        /* Echo.private('payment.2')
			 .listen('PaymentMade', (e) => {
				 alert(e);
			 });*/
        $(document).ready(function (){
            $('.pay-form').on('submit', function (e) {
                e.preventDefault();
                $('#checkout_phone').attr('disabled', true);
                $('#pay-now-btn').attr('disabled', true);
                
                var frm = $(this);
                
                $.ajax({
                    type: 'POST',
                    url: frm.attr('action'),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    
                    success: function (Mess) {
                        if (Mess.status === true) {
                            toastr.success("Transaction sent successfully, Enter your pin to complete transaction", 'Mpesa Checkout', {
                                timeOut: 10000,
                                closeButton: true,
                                progressBar: true,
                                newestOnTop: true
                            });
                        } else {
                            toastr.error('Something went wrong!',  'Mpesa Checkout', {
                                timeOut: 3000,
                                closeButton: true,
                                progressBar: true,
                                newestOnTop: true
                            });
                            // Re-enable the form elements if there's an error
                            $('#checkout_phone').attr('disabled', false);
                            $('#pay-now-btn').attr('disabled', false);
                        }
                    },
                    error: function (xhr, status, errorThrown) {
                        toastr.error('Something went wrong!',  'Mpesa Checkout', {
                            timeOut: 3000,
                            closeButton: true,
                            progressBar: true,
                            newestOnTop: true
                        });
                        
                        // Re-enable the form elements on error
                        $('#checkout_phone').attr('disabled', false);
                        $('#pay-now-btn').attr('disabled', false);
                    }
                });
            });
        });
        var pusher = new Pusher('4ab2686a7a70eb43db60', {
			cluster: 'eu',
			encrypted: true,
			authEndpoint: '/pusher/auth',
		});
	 
		var channel = pusher.subscribe('payment.{{ $account }}');
		channel.bind('new_payment', function (data) {
		    if(data.transaction.status)
                {
                    window.location.href = '{{ route('success_payment',$account) }}';
                }
            else
                {
                    window.location.reload();
                }
            
	 
		});
		channel.bind('pusher:subscription_count', function (members) {
			console.log('successfully subscribed!');
		});
		channel.bind('pusher:subscription_succeeded', function (members) {
			console.log('successfully subscribed!' + members);
        });
    
    </script>
@endsection
