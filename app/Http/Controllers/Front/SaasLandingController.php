<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Support\SaasCatalog;
use Caydeesoft\SaasKit\Seo\Contracts\SeoMetadataGeneratorInterface;
use Caydeesoft\SaasKit\Seo\DTOs\SeoMetadataData;
use Illuminate\View\View;

class SaasLandingController extends Controller
{
    public function __invoke(SaasCatalog $catalog, SeoMetadataGeneratorInterface $seo): View
    {
        $brand = $catalog->brand();
        $description = (string) ($brand['description'] ?? 'Subscription operations for growing SaaS teams.');

        return view('modules.front.landing', [
            'brand' => $brand,
            'navigation' => $catalog->navigation(),
            'metrics' => $catalog->metrics(),
            'features' => $catalog->features(),
            'plans' => $catalog->plans(),
            'seo' => $seo->generate(new SeoMetadataData(
                title: (string) ($brand['name'] ?? config('app.name')),
                description: $description,
                url: route('landing'),
                image: asset('assets/img/logo.png'),
                siteName: (string) ($brand['name'] ?? config('app.name')),
                keywords: ['subscription management', 'saas billing', 'laravel saas', 'subscriber platform'],
            )),
        ]);
    }
}
