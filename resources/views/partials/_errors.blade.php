@if($errors->any())
    <div class="container">
        <div class="col-sm-6 m-2 p-2">
            <p><strong>Oops Something went wrong</strong></p>
            <ul style="list-style:none;">
                @foreach ($errors->all() as $error)
                    <li class="text-bold text-danger">
                        <i class="dripicons-wrong me-2 text-warning text-sm"></i> {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

@endif
