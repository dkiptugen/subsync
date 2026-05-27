<?php

return [
    'menus' => [
        'saas' => [
            'items' => [
                [
                    'type' => 'header',
                    'title' => 'SaaS',
                    'position' => 1,
                ],
                [
                    'type' => 'item',
                    'title' => 'Product Catalog',
                    'route' => 'saas-product.index',
                    'icon' => 'layers',
                    'position' => 2,
                ],
            ],
        ],
    ],
];
