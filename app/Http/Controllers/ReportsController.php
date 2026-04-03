<?php

namespace App\Http\Controllers;

use App\Traits\Meta;

class ReportsController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    public function reg_index()
    {
        return view('modules.reportds.subscribers', $this->data);
    }
}
