@extends('includes.body')
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title my-0 text-nation">PO Approval - {{ $po->organization->name }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('organization.purchase.update',[$organizationId,$po->id]) }}" method="post" class="form form-horizontal create-form">
                    @csrf
                    @method('put')
                    <div class="row mb-3">
                        <div class="col">
                            <label for="organization" class="control-label">Organization</label>
                            <input type="text" name="organization" id="organization" class="form-control" disabled
                                   value="{{ $po->organization->name }}">
                        </div>


                    </div>

                    <div class="row mb-3">

                        <div class="col">
                            <label for="startdate" class="control-label">Start date</label>
                            <input type="date" name="startdate" id="startdate" class="form-control" value="{{ $po->startdate }}">
                        </div>

                    </div>

                    <div class="mb-3">
                        <label for="reason" class="control-label">Reason</label>
                        <textarea name="reason" id="reason" class="form-control">{{ $po->reason }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" value="1" @if($po->status ==1 ) checked @endif>
                            <span class="form-check-label">Approve</span>
                        </label>
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" value="0" @if($po->status == 0 ) checked @endif>
                            <span class="form-check-label">Disapprove</span>
                        </label>
                    </div>
                    <div class="mb-3 d-flex">
                        <button type="submit" class="btn btn-sm btn-nation ms-auto">Change Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
