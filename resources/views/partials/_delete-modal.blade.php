<div id="confirmDeleteModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body p-4">
                <div class="text-center">
                    <i class="dripicons-warning h1 text-warning"></i>
{{--                    <h4 class="mt-2">Hey</h4>--}}
                    <p class="mt-3"> Do you want to confirm this action?</p>
                    <form id="action" method="post" action="">
                        @csrf
                        @method('delete')
                        <div class="text-center">
                                <input id="target" name="modal" type="hidden" value="{{old('modal')}}">
                            <div class="modal-footer text-center">
                                <button type="button" class="btn btn-secondary btn-sm btn-rounded" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn bg-nation btn-sm btn-rounded text-white">Delete</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
