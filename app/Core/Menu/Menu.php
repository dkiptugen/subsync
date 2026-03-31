<?php

namespace App\Core\Menu;

use Illuminate\Support\Facades\Facade;

class Menu extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MenuManager::class;
    }
}
