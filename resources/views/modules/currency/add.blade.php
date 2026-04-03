@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title text-nation">Add Currency Conversion Rate</h3>
                <!-- /.card-title text-nation -->
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <form action="{{ route('currency.store') }}" method="post" class="form create-form">
                    <div class="mb-3">
                        <label for="region" class="control-label">Country</label>
                        <select name="region" id="region" class="form-control select2">
                            @foreach($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="dollar_amount" class="control-label">Dollar Amount</label>
                            <input type="number" name="dollar_amount" id="dollar_amount" class="form-control" value="1">
                        </div>
                        <div class="col">
                            <label for="currency_amount" class="control-label currency-label">Amount</label>
                            <input type="text" name="currency_amount" id="currency_amount" class="form-control">
                        </div>

                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="startdate" class="control-label">Start Date</label>
                            <input type="date" name="startdate" id="startdate" class="form-control">
                        </div>
                        <div class="col">
                            <label for="enddate" class="control-label">End date</label>
                            <input type="date" name="enddate" id="enddate" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-dark ms-auto">
                            Add Conversion Rate
                        </button>
                        <!-- /.btn btn-outline-nation -->
                    </div>
                    <!-- /.form-group -->
                </form>
                <!-- /.form create-form -->
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card card-border-nation -->
    </div>
    <!-- /.col-12 -->
@endsection
@section('header')

@endsection

@section('footer')
    <script type="text/javascript">
        $('#region').select2({
            placeholder: 'Select Country'
        }).change(function(x){

            $.ajax({
                type: 'GET',
                url: '{{ url('/manage/currency/autocomplete') }}/'+x.target.value,
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')},
                processData: false,
                contentType: false,
                success: function (Mess) {
                    $('.currency-label').html(Mess.currency+' Amount');

                },
                cache:true
            })
        });
    </script>
@endsection
