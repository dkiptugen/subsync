
@php(extract($actions))

@foreach($actions as $key => $action)
    @if($key == 'delete')
        @if(!is_null($action['permissions']) && !empty($action['permissions']))
            @canany(explode(',', $action['permissions']))
                @include('partials._delete-button', ['url' => $action['path']])
            @endcanany
        @else
            @include('partials._delete-button', ['url' => $action['path']])
        @endif
    @else
        @if(!is_null($action['permissions']) && !empty($action['permissions']))
            @canany(explode(',', $action['permissions']))
                <a title="edit" class="mx-2" href="{{ $action['path'] }}">
                    <i class="{{ $action['class'] }}"></i>
                </a>
            @endcanany
        @else
            <a title="edit" class="mx-2" href="{{ $action['path'] }}">
                <i class="{{ $action['class'] }}"></i>
            </a>
        @endif
    @endif
@endforeach



