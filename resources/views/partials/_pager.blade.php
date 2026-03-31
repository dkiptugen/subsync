@if(isset($items))
    <div class="row g3 mt-2 p-1">
        <div class="col-sm-6">
            {{$items->render()}}

        </div>
        <div class="col-sm-6">
 <span class="d-flex flex-row-reverse">
         <i class="font-italic">{{$items->count().' results of '.$items->total().' entries'}}</i>
         &nbsp;-&nbsp;
         <i class="font-italic">{{'page '.$items->currentPage().' of '.$items->lastPage()}}</i>

 </span>
        </div>
    </div>
@endif
