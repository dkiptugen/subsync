<?php

namespace App\Http\Controllers;

use App\Services\DashboardSnapshotService;
use App\Traits\Meta;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use Meta;

    public function __construct(private readonly DashboardSnapshotService $snapshots) {}

    public function index(): View
    {
        $data = self::site_def();
        $data['dashboard'] = $this->snapshots->get();

        return view('modules.dashboard.index', $data);
    }

    public function snapshot(): View
    {
        return view('components.dashboard.realtime', [
            'dashboard' => $this->snapshots->get(),
        ]);
    }
}
