@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card card-border-nation">
            <div class="card-header">
                <h3 class="card-title text-nation">Edit Currency Conversion Rate</h3>
                <!-- /.card-title text-nation -->
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <form action="{{ route('currency.update',$currency->id) }}" method="post" class="form create-form">
                    @csrf
                    @method('put')
                    <div class="form-group">
                        <label for="region" class="control-label">Country</label>
                        <select name="region" id="region" class="form-control select2">
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}"
                                        @if($currency->region_id == $region->id) selected @endif >{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-row">
                        <div class="col">
                            <label for="dollar_amount" class="control-label">Dollar Amount</label>
                            <input type="number" name="dollar_amount" id="dollar_amount" class="form-control"
                                   value="{{ $currency->dollar_amount }}">
                        </div>
                        <div class="col">
                            <label for="currency_amount" class="control-label currency-label">Amount</label>
                            <input type="text" name="currency_amount" id="currency_amount" class="form-control"
                                   value="{{ $currency->amount }}">
                        </div>

                    </div>

                    <div class="form-group form-row">
                        <div class="col">
                            <label for="startdate" class="control-label">Start Date</label>
                            <input type="date" name="startdate" id="startdate" class="form-control"
                                   value="{{ $currency->startdate }}">
                        </div>
                        <div class="col">
                            <label for="enddate" class="control-label">End date</label>
                            <input type="date" name="enddate" id="enddate" class="form-control"
                                   value="{{ $currency->enddate }}">
                        </div>
                    </div>
                     <div class="form-group">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status" @if($currency->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>

                    </div>
                    <div class="form-group d-flex">
                        <button type="submit" class="btn btn-nation ml-auto">
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
        }).change(function (x) {

            $.ajax({
                type: 'GET',
                url: '{{ url('/manage/currency/autocomplete') }}/' + x.target.value,
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')},
                processData: false,
                contentType: false,
                success: function (Mess) {
                    $('.currency-label').html(Mess.currency + ' Amount');

                },
                cache: true
            })
        });
    </script>
@endsection
