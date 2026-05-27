<?php

namespace App\Http\Controllers;

use App\Support\SaasCatalog;
use Illuminate\View\View;

class SaasProductController extends Controller
{
    public function index(SaasCatalog $catalog): View
    {
        $plans = $catalog->plans();
        $features = $catalog->features();

        return view('modules.saas.index', [
            'title' => 'SaaS Product',
            'brand' => $catalog->brand(),
            'plans' => $plans,
            'features' => $features,
            'metrics' => [
                'plans' => $plans->count(),
                'features' => $features->count(),
                'entry_price' => $plans->first()['price'] ?? 'Configured',
            ],
        ]);
    }
}
