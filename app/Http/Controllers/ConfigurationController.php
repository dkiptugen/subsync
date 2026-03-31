<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;

    class ConfigurationController extends Controller
        {
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\View\View
         */
            public function index()
                {
                    $this->data['config'] = config('custom');
                    return view('modules.configuration.index' ,$this->data);
                }

            public function edit(Request $request)
                {
                    foreach ($request->all() as $key => $value)
                        {
                            self::setEnv($key ,$value);
                        }
                    shell_exec('php ' . base_path('artisan') . ' config:clear');
                    return self::success('Configuration' ,'Added successfully' ,route('configuration.index'));
                }


        }
