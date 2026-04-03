<?php

namespace App\Http\Controllers;

use App\Traits\Meta;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfigurationController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|Application|View
     */
    public function index()
    {
        $this->data['config'] = config('custom');

        return view('modules.configuration.index', $this->data);
    }

    public function edit(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            self::setEnv($key, $value);
        }
        shell_exec('php '.base_path('artisan').' config:clear');

        return self::success('Configuration', 'Added successfully', route('configuration.index'));
    }
}
