<?php

namespace App\Http\Controllers;

use App\Exports\SubscriberExport;
use App\Models\Product;
use App\Traits\Meta;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportTSController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    public function subscribers_form()
    {
        $this->data['products'] = Product::get();

        return view('modules.reports.subscribers', $this->data);
    }

    public function subscribers_export(Request $request)
    {

        $d = Excel::download(new SubscriberExport($request), 'subscribers-'.$request->startdate.'-'.$request->enddate.'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        if ($d) {
            return $d;
        }

    }

    public function subscriptions_form()
    {
        return view('modules.reportds.subscriptions', $this->data);
    }

    public function subscriptions(Request $request) {}
}
