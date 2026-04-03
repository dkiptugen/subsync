@extends('includes.body')
@section('content')
    <div class="col-12">

           @if(($payment_method->provider =='mpesa') && ($payment_method->type =='paybill'))
            <div class="card card-border-nation">
                    <div class="card-header">
                        <h3 class="card-title text-nation my-0">Mpesa Paybill Update</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payment_method.update',$payment_method->id) }}" method="post"
                              class="form form-horizontal create-form">
                            @csrf
                            @method('put')
                            <input type="hidden" name="provider" value="mpesa">
                            <input type="hidden" name="type" value="paybill">
                            <input type="hidden" name="notify" value="0">
                            <div class="mb-3">
                                <label for="paybill_name" class="control-label">Name</label>
                                <input type="text" name="name" id="paybill_name" value="{{ $payment_method->name }}"
                                       class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="paybill_identifier" class="control-label">Shortcode</label>
                                <input type="number" name="configuration[shortcode]" id="paybill_identifier"
                                       value="{{ $payment_method->configuration['shortcode'] }}" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="paybill_consumer_key" class="control-label">Consumer Key</label>
                                <input type="text" name="configuration[consumer_key]" id="paybill_consumer_key"
                                       value="{{ $payment_method->configuration['consumer_key'] }}"
                                       class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="paybill_consumer_secret" class="control-label">Consumer Secret</label>
                                <input type="text" name="configuration[consumer_secret]" id="paybill_consumer_secret"
                                       value="{{ $payment_method->configuration['consumer_secret'] }}"
                                       class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="paybill_pass_key" class="control-label">Passkey</label>
                                <input type="text" name="configuration[pass_key]" id="paybill_pass_key"
                                       value="{{ $payment_method->configuration['pass_key'] }}" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="paybill_notfication_endpoint"
                                       class="control-label">Notification Endpoints</label>
                                <textarea name="notification_endpoint" id="paybill_notfication_endpoint"
                                          class="form-control tags"
                                          placeholder="For multiple, separate with commas"> {{ implode(',',$payment_method->notification_endpoints) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="paybill_enviroment" class="control-label">Environment</label>
                                <select name="configuration[environment]" id="paybill_enviroment"
                                        class="form-control select">
                                    <option value="1">Production</option>
                                    <option value="2">Testing</option>
                                </select>
                            </div>
                             <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status"
                                   @if($payment_method->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>

                    </div>
                            <div class="mb-3 d-flex">
                                <button type="submit" class="btn btn-nation  ms-auto">Add Paybill</button>
                            </div>
                        </form>
                    </div>
                </div>
        @elseif(($payment_method->provider =='mpesa') && ($payment_method->type =='buygoods'))
            <div class="card card-border-nation">
                    <div class="card-header">
                        <h3 class="card-title text-nation my-0">Mpesa Buy Goods Update</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payment_method.update',$payment_method->id) }}" method="post"
                              class="form form-horizontal create-form">
                            @csrf
                            @method('put')
                            <input type="hidden" name="provider" value="mpesa">
                            <input type="hidden" name="type" value="buygoods">
                            <input type="hidden" name="notify" value="0">
                            <div class="mb-3">
                                <label for="buygoods_name" class="control-label">Name</label>
                                <input type="text" name="name" id="buygoods_name" class="form-control">
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="buygoods_shortcode" class="control-label">Shortcode</label>
                                    <input type="number" name="configuration[shortcode]" id="buygoods_shortcode"
                                           class="form-control">
                                </div>
                                <div class="col">
                                    <label for="buygoods_store_number" class="control-label">Store number</label>
                                    <input type="number" name="configuration[store_number]" id="buygoods_store_number"
                                           class="form-control">
                                </div>

                            </div>
                            <div class="mb-3">
                                <label for="buygoods_consumer_key" class="control-label">Consumer Key</label>
                                <input type="text" name="configuration[consumer_key]" id="buygoods_consumer_key"
                                       class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="buygoods_consumer_secret" class="control-label">Consumer Secret</label>
                                <input type="text" name="configuration[consumer_secret]" id="buygoods_consumer_secret"
                                       class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="buygoods_pass_key" class="control-label">Passkey</label>
                                <input type="text" name="configuration[pass_key]" id="buygoods_pass_key"
                                       class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="buygoods_notfication_endpoint"
                                       class="control-label">Notification Endpoints</label>
                                <textarea name="notification_endpoint" id="buygoods_notfication_endpoint"
                                          class="form-control tags"
                                          placeholder="For multiple, separate with commas"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="paybill_enviroment" class="control-label">Environment</label>
                                <select name="configuration[environment]" id="paybill_enviroment"
                                        class="form-control select">
                                    <option value="1">Production</option>
                                    <option value="2">Testing</option>
                                </select>
                            </div>
                             <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status"
                                   @if($payment_method->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>

                    </div>
                            <div class="mb-3 d-flex">
                                <button type="submit" class="btn btn-nation ms-auto">Add Buy Goods</button>
                            </div>
                        </form>
                    </div>
                </div>
        @elseif(($payment_method->provider =='dpo'))
            <div class="card card-border-nation">
                    <div class="card-header">
                        <h3 class="card-title text-nation my-0">DPO Update</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payment_method.update',$payment_method->id) }}" method="post"
                              class="form form-horizontal create-form">
                            @csrf
                            @method('put')
                            <input type="hidden" name="provider" value="dpo">
                            <input type="hidden" name="type" value="dpo">
                            <input type="hidden" name="notify" value="1">
                            <div class="mb-3">
                                <label for="dpo_name" class="control-label">Name</label>
                                <input type="text" name="name" id="dpo_name" class="form-control"
                                       value="{{ $payment_method->name }}">
                            </div>
                            <div class="mb-3">
                                <label for="dpo_token" class="control-label">Company Token</label>
                                <input type="text" name="configuration[company_token]" id="dpo_token"
                                       value="{{ $payment_method->configuration['company_token'] }}"
                                       class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="dpo-service-code" class="control-label">Service Code</label>
                                <input type="text" name="configuration[shortcode]" id="dpo-service-code"
                                       class="form-control" value="{{ $payment_method->configuration['shortcode'] }}">
                            </div>

                            <div class="mb-3">
                                <label for="dpo_channel" class="control-label">Pay Channels</label>
                                <select name="configuration[channel][]" id="dpo_channel" multiple
                                        class="form-control select2" aria-multiselectable="true">
                                    <option value="mobile" {{--@if(is_array($payment_method->configuration["channel"]) && in_array("mobile",$payment_method->configuration["channel"]??[])) selected @endif--}}>mobile</option>
                                    <option value="card" {{--@if(is_array($payment_method->configuration["channel"]) && in_array("card",$payment_method->configuration["channel"]??[])) selected @endif--}}>Card</option>
                                    {{--@if(in_array("card",$payment_method->configuration["channel"])) selected @endif--}}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="dpo_notfication_endpoint"
                                       class="control-label">Notification Endpoints</label>
                                <textarea name="notification_endpoint" id="dpo_notfication_endpoint"
                                          class="form-control tags"
                                          placeholder="For multiple, separate with commas">{{ $payment_method->notification_endpoint }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="dpo_enviroment" class="control-label">Environment</label>
                                <select name="configuration[environment]" id="dpo_enviroment"
                                        class="form-control select">
                                    <option value="1"
                                            @if($payment_method->configuration['environment'] == 1) selected @endif>Production</option>
                                    <option value="2"
                                            @if($payment_method->configuration['environment'] == 2) selected @endif>Testing</option>
                                </select>
                            </div>
                             <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status"
                                   @if($payment_method->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>

                    </div>
                            <div class="mb-3 d-flex">
                                <button type="submit" class="btn btn-nation ms-auto">Add DPO Configuration</button>
                            </div>
                        </form>
                    </div>
                </div>
        @elseif(($payment_method->provider =='pesapal') )
            <div class="card card-border-nation">
                    <div class="card-header">
                        <h3 class="card-title text-nation my-0">Pesapal Update</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payment_method.update',$payment_method->id) }}" method="post"
                              class="form form-horizontal create-form">
                            @csrf
                            @method('put')
                            <input type="hidden" name="provider" value="pesapal">
                            <input type="hidden" name="type" value="pesapal">
                            <input type="hidden" name="notify" value="1">
                            <div class="mb-3">
                                <label for="pesapal_name" class="control-label">Name</label>
                                <input type="text" name="name" id="pesapal_name" class="form-control"
                                       value="{{ $payment_method->name }}">
                            </div>
                            <div class="mb-3">
                                <label for="pesapal_shortcode" class="control-label">Company Email</label>
                                <input type="text" name="configuration[shortcode]" id="pesapal_shortcode"
                                       class="form-control" value="{{ $payment_method->configuration['shortcode'] }}">
                            </div>
                            <div class="mb-3">
                                <label for="pesapal_consumer_key" class="control-label">Consumer Key</label>
                                <input type="text" name="configuration[consumer_key]" id="pesapal_consumer_key"
                                       class="form-control"
                                       value="{{ $payment_method->configuration['consumer_key'] }}">
                            </div>
                            <div class="mb-3">
                                <label for="pesapal_consumer_secret" class="control-label">Consumer Secret</label>
                                <input type="text" name="configuration[consumer_secret]" id="pesapal_consumer_secret"
                                       class="form-control"
                                       value="{{ $payment_method->configuration['consumer_secret'] }}">
                            </div>
                            <div class="mb-3">
                                <label for="pesapal_notification_endpoint"
                                       class="control-label">Notification Endpoints</label>
                                <textarea name="notification_endpoint" id="pesapal_notification_endpoint"
                                          class="form-control tags"
                                          placeholder="For multiple, separate with commas">{{ $payment_method->notification_endpoint }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="pesapal_enviroment" class="control-label">Environment</label>
                                <select name="configuration[environment]" id="pesapal_enviroment"
                                        class="form-control select">
                                    <option value="1"
                                            @if($payment_method->configuration['environment'] == 1) selected @endif>Production</option>
                                    <option value="2"
                                            @if($payment_method->configuration['environment'] == 2) selected @endif>Testing</option>
                                </select>
                            </div>
                             <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="status"
                                   @if($payment_method->status) checked
                                   @endif value="1">
                            <span class="form-check-label">
                                Active
                            </span>
                        </label>

                    </div>
                            <div class="mb-3 d-flex">
                                <button type="submit" class="btn btn-nation ms-auto">Add Pesapal Configuration</button>
                            </div>
                        </form>
                    </div>
                </div>
        @endif


    </div>

@endsection
