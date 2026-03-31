@php
    $children = $item['children'] ?? [];
    $hasChildren = count($children) > 0;
    $collapseId = 'sidebar-menu-'.md5((string) ($item['id'] ?? $item['title'] ?? uniqid('', true)));
    $href = '#';

    if (!empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])) {
        $href = route($item['route']);
    } elseif (!empty($item['url'])) {
        $href = $item['url'];
    }
@endphp

<li class="sidebar-item">
    @if($hasChildren)
        <a
            class="sidebar-link d-flex justify-content-between align-items-center"
            href="#{{ $collapseId }}"
            data-bs-toggle="collapse"
            role="button"
            aria-expanded="false"
            aria-controls="{{ $collapseId }}"
        >
            <span>
                @if(!empty($item['icon']))
                    <i class="align-middle {{ $item['icon'] }}"></i>
                @endif
                <span class="align-middle">{{ $item['title'] }}</span>
            </span>
            <i class="align-middle" data-feather="chevron-down"></i>
        </a>
        <ul class="sidebar-dropdown list-unstyled collapse" id="{{ $collapseId }}">
            @foreach($children as $child)
                @include('includes.sidebar-menu-item', ['item' => $child])
            @endforeach
        </ul>
    @else
        <a class="sidebar-link" href="{{ $href }}">
            @if(!empty($item['icon']))
                <i class="align-middle {{ $item['icon'] }}"></i>
            @endif
            <span class="align-middle">{{ $item['title'] }}</span>
        </a>
    @endif
</li>
