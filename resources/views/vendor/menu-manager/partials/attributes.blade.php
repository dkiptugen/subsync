@foreach($attributes as $name => $value)
    @if(is_bool($value))
        @if($value) {{ $name }} @endif
    @elseif(!is_null($value))
        {{ $name }}="{{ $value }}"
    @endif
@endforeach
