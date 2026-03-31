@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="my-0 text-nation">Payment Methods</h3>
                @canaccess('payment_method.create')
                <a class="btn btn-outline-nation btn-sm " href="{{ route('payment_method.create') }}">
                    <i class="align-middle" data-feather="plus"></i> Add Payment Method
                </a>
                @endcanaccess
            </div>
            <div class="card-body ">
                <div class="table-responsive">
                    <table class="table table-striped table-condensed" id="payment-method-table">
                        <thead class="bg-nation text-white" >
                            <tr>
                                <th>#</th>
                                <th>Identifier</th>
                                <th>Provider</th>
                                <th>Status</th>
                                <th>Notifying</th>
                                <th>Creator</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot class="bg-nation text-white">
                            <tr>
                                <th>#</th>
                                <th>Identifier</th>
                                <th>Provider</th>
                                <th>Status</th>
                                <th>Notifying</th>
                                <th>Creator</th>
                                <th>Date Created</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section("footer")
    <script>
        $('#payment-method-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":{
                "url": "{{ route('payment_method.datatable') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: "{{csrf_token()}}"}
            },
            "columns": [
                { "data": "pos" },
                { "data": "identifier" },
                { "data": "provider" },
                { "data": "status" },
                { "data": "notify" },
                { "data": "creator" },
                { "data": "date_created" },
                {"data": "action","orderable":false}
            ],
            "order": [[ 1, "asc" ]]
        });
        $(document).on('change','.shortcode-notify',function(){
            // console.log($(this).data('shortcode'));
            var chk  = $(this);
            if(chk.is(':checked'))
            {
                $.ajax({
                    type:'POST',
                    url:'{{ route('mpesa.notify') }}',
                    data: {_token: "{{csrf_token()}}",identifier: $(this).data('shortcode')},
                    success:function(Mess){

                        if(Mess)
                        {

                            toastr.success('Notification started successfully.', 'Notification', {timeOut: 1000, closeButton:true, progressBar:true, newestOnTop:true});
                        }
                        else
                        {
                            toastr.error('Notification failed to start.'+Mess, 'Notification', {timeOut: 1000, closeButton:true, progressBar:true, newestOnTop:true,onHidden: function () {
                                    chk.prop("checked", false);
                                }});

                        }

                    },
                    error:function (e) {
                        toastr.error(e.responseJSON.error, e.responseJSON.message, {timeOut: 1000, closeButton:true, progressBar:true, newestOnTop:true,onHidden: function () {
                                chk.prop("checked", false);
                            }});
                        console.log(e);

                    }
                });
            }
        });
    </script>
@endsection
