<?php

    namespace App\Core\Menu;

    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    class MenuManager
        {
            protected array $menus = [];

            public function register(string $location, array $menu)
            : void
                {
                    $this->menus[$location][] = $this->normalizeItem($menu, $location);
                }

            public function registerMany(string $location, array $menus)
            : void
                {
                    foreach ($menus as $menu)
                        {
                            if (is_array($menu))
                                {
                                    $this->register($location, $menu);
                                }
                        }
                }

            public function get(string $location)
            : array
                {
                    $items = $this->menus[$location] ?? [];
                    $items = $this->sortItems($items);

                    $visible = [];
                    foreach ($items as $item)
                        {
                            $resolved = $this->resolveVisibility($item);
                            if ($resolved !== null)
                                {
                                    $visible[] = $resolved;
                                }
                        }

                    return $visible;
                }

            protected function normalizeItem(array $item, string $location)
            : array
                {
                    $id   = (string)($item['id'] ?? uniqid('menu_', true));
                    $type = strtolower((string)($item['type'] ?? 'link'));
                    if (!in_array($type, ['link', 'title', 'divider'], true))
                        {
                            $type = 'link';
                        }
                    $children = [];

                    foreach (($item['children'] ?? []) as $child)
                        {
                            if (!is_array($child))
                                {
                                    continue;
                                }

                            $children[] = $this->normalizeItem($child, $location);
                        }

                    return [
                        'id'       => $id,
                        'type'     => $type,
                        'location' => (string)($item['location'] ?? $location),
                        'title'    => (string)($item['title'] ?? $id),
                        'icon'     => (string)($item['icon'] ?? ''),
                        'route'    => $item['route'] ?? null,
                        'url'      => $item['url'] ?? null,
                        'order'    => (int)($item['order'] ?? 999),
                        'before'   => (string)($item['before'] ?? ''),
                        'after'    => (string)($item['after'] ?? ''),
                        'can'      => $item['can'] ?? null,
                        'children' => $children,
                    ];
                }

            protected function sortItems(array $items)
            : array
                {
                    usort($items, static function (array $a, array $b)
                    : int
                        {
                            return [$a['order'], $a['title']] <=> [$b['order'], $b['title']];
                        });

                    $items = $this->applyAnchoredPositioning($items);

                    foreach ($items as &$item)
                        {
                            $item['children'] = $this->sortItems($item['children'] ?? []);
                        }

                    return $items;
                }

            protected function applyAnchoredPositioning(array $items)
            : array
                {
                    $indexById = [];
                    foreach ($items as $index => $item)
                        {
                            $indexById[$item['id']] = $index;
                        }

                    // Iterate multiple passes so chained anchors can settle deterministically.
                    for ($pass = 0; $pass < 3; $pass++)
                        {
                            foreach ($items as $i => $item)
                                {
                                    $before = $item['before'] ?? '';
                                    $after  = $item['after'] ?? '';

                                    if ($before !== '' && isset($indexById[$before]))
                                        {
                                            $targetIndex = $indexById[$before];
                                            if ($targetIndex !== $i)
                                                {
                                                    $moved = $items[$i];
                                                    array_splice($items, $i, 1);
                                                    if ($i < $targetIndex)
                                                        {
                                                            $targetIndex--;
                                                        }
                                                    array_splice($items, $targetIndex, 0, [$moved]);
                                                    $indexById = $this->rebuildIndexById($items);
                                                }

                                            continue;
                                        }

                                    if ($after !== '' && isset($indexById[$after]))
                                        {
                                            $targetIndex = $indexById[$after] + 1;
                                            if ($targetIndex !== $i && $targetIndex - 1 !== $i)
                                                {
                                                    $moved = $items[$i];
                                                    array_splice($items, $i, 1);
                                                    if ($i < $targetIndex)
                                                        {
                                                            $targetIndex--;
                                                        }
                                                    array_splice($items, $targetIndex, 0, [$moved]);
                                                    $indexById = $this->rebuildIndexById($items);
                                                }
                                        }
                                }
                        }

                    return $items;
                }

            protected function rebuildIndexById(array $items)
            : array
                {
                    $map = [];
                    foreach ($items as $idx => $item)
                        {
                            $map[$item['id']] = $idx;
                        }

                    return $map;
                }

            protected function resolveVisibility(array $item)
            : ?array
                {
                    if (!$this->isAllowed($item))
                        {
                            return null;
                        }

                    if (($item['type'] ?? 'link') === 'divider')
                        {
                            return $item;
                        }

                    if (($item['type'] ?? 'link') === 'title')
                        {
                            return $item;
                        }

                    $children = [];
                    foreach ($item['children'] as $child)
                        {
                            $resolved = $this->resolveVisibility($child);
                            if ($resolved !== null)
                                {
                                    $children[] = $resolved;
                                }
                        }

                    $item['children'] = $children;

                    if (is_string($item['route']) && $item['route'] !== '' && !Route::has($item['route']))
                        {
                            $item['route'] = null;
                        }

                    if ($item['route'] === null && $item['url'] === null && count($item['children']) === 0)
                        {
                            return null;
                        }

                    return $item;
                }

            protected function isAllowed(array $item)
            : bool
                {
                    if (empty($item['can']))
                        {
                            return true;
                        }

                    $user = Auth::user();
                    if ($user === null)
                        {
                            return false;
                        }

                    return $user->can($item['can']);
                }
        }
