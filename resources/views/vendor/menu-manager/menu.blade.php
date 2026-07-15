@php($wrapper = $menuView['wrapper'] ?? [])

<{{ $wrapper['tag'] ?? 'ul' }} class="{{ $wrapper['class'] ?? '' }}" @include('menu-manager::partials.attributes', ['attributes' => $wrapper['attributes'] ?? []])>
    @include('menu-manager::partials.items', ['items' => $menus, 'view' => $menuView])
</{{ $wrapper['tag'] ?? 'ul' }}>
