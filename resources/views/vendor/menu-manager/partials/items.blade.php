@foreach($items as $item)
    @php($type = $item['type'] ?? 'item')

    @if($type === 'header')
        @php($header = $view['header'] ?? [])
        <{{ $header['tag'] ?? 'li' }} class="{{ $item['class'] ?? $header['class'] ?? '' }}" @include('menu-manager::partials.attributes', ['attributes' => $item['attributes'] ?? $header['attributes'] ?? []])>
            {{ $item['title'] }}
        </{{ $header['tag'] ?? 'li' }}>
    @endif

    @if($type === 'item')
        @php($itemView = $view['item'] ?? [])
        @php($link = $view['link'] ?? [])
        @php($icon = $view['icon'] ?? [])
        @php($label = $view['label'] ?? [])
        @php($activeClass = request()->routeIs($item['route']) ? ' '.($itemView['active_class'] ?? 'active') : '')

        <{{ $itemView['tag'] ?? 'li' }} class="{{ trim(($item['item_class'] ?? $itemView['class'] ?? '').$activeClass) }}" @include('menu-manager::partials.attributes', ['attributes' => $item['item_attributes'] ?? $itemView['attributes'] ?? []])>
            <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="{{ $item['link_class'] ?? $link['class'] ?? '' }}" @include('menu-manager::partials.attributes', ['attributes' => $item['link_attributes'] ?? $link['attributes'] ?? []])>
                @isset($item['icon'])
                    <{{ $icon['tag'] ?? 'i' }} class="{{ trim($item['icon'].' '.($item['icon_class'] ?? $icon['class'] ?? '')) }}" @include('menu-manager::partials.attributes', ['attributes' => $item['icon_attributes'] ?? $icon['attributes'] ?? []])></{{ $icon['tag'] ?? 'i' }}>
                @endisset
                <{{ $label['tag'] ?? 'span' }} class="{{ $item['label_class'] ?? $label['class'] ?? '' }}" @include('menu-manager::partials.attributes', ['attributes' => $item['label_attributes'] ?? $label['attributes'] ?? []])>{{ $item['title'] }}</{{ $label['tag'] ?? 'span' }}>
            </a>
        </{{ $itemView['tag'] ?? 'li' }}>
    @endif

    @if($type === 'dropdown')
        @php($dropdown = $view['dropdown'] ?? [])
        @php($toggle = $dropdown['toggle'] ?? [])
        @php($icon = $view['icon'] ?? [])
        @php($label = $view['label'] ?? [])
        @php($children = $dropdown['children'] ?? [])
        @php($dropdownId = $item['id'] ?? 'menu-'.\Illuminate\Support\Str::slug($item['title']))

        <{{ $dropdown['tag'] ?? 'li' }} class="{{ $item['item_class'] ?? $dropdown['class'] ?? '' }}" @include('menu-manager::partials.attributes', ['attributes' => $item['item_attributes'] ?? $dropdown['attributes'] ?? []])>
            <a href="#{{ $dropdownId }}" class="{{ $item['link_class'] ?? $toggle['class'] ?? '' }}" @include('menu-manager::partials.attributes', ['attributes' => array_merge($toggle['attributes'] ?? [], $item['link_attributes'] ?? [])])>
                @isset($item['icon'])
                    <{{ $icon['tag'] ?? 'i' }} class="{{ trim($item['icon'].' '.($item['icon_class'] ?? $icon['class'] ?? '')) }}" @include('menu-manager::partials.attributes', ['attributes' => $item['icon_attributes'] ?? $icon['attributes'] ?? []])></{{ $icon['tag'] ?? 'i' }}>
                @endisset
                <{{ $label['tag'] ?? 'span' }} class="{{ $item['label_class'] ?? $label['class'] ?? '' }}" @include('menu-manager::partials.attributes', ['attributes' => $item['label_attributes'] ?? $label['attributes'] ?? []])>{{ $item['title'] }}</{{ $label['tag'] ?? 'span' }}>
            </a>

            <{{ $children['tag'] ?? 'ul' }} id="{{ $dropdownId }}" class="{{ $children['class'] ?? '' }}" @include('menu-manager::partials.attributes', ['attributes' => $children['attributes'] ?? []])>
                @include('menu-manager::partials.items', ['items' => $item['children'] ?? [], 'view' => $view])
            </{{ $children['tag'] ?? 'ul' }}>
        </{{ $dropdown['tag'] ?? 'li' }}>
    @endif

    @if($type === 'html')
        {!! $item['html'] ?? '' !!}
    @endif
@endforeach
