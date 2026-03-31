<?php

    namespace App\Http\Controllers;

    use App\Models\Product;
    use App\Models\SubscriptionGroup;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class ChurnController extends Controller
        {
            public function index()
                {
                    $this->data['product'] = Product::select(DB::Raw('distinct(name)') ,'id')
                                                    ->where('organization_id' ,session('default_org'))
                                                    ->get();
                    $startdate = request('startdate') ?? Carbon::now()
                                                               ->subDays(7)
                                                               ->format('Y-m-d');
                    $enddate = request('enddate') ?? Carbon::now()
                                                           ->format('Y-m-d');
                    $this->data['subgroup'] = SubscriptionGroup::whereDate('subdate' ,'>=' ,$startdate)
                                                               ->whereDate('subdate' ,'<=' ,$enddate)
                                                               ->orderBY('subdate' ,'ASC')
                                                               ->get();
                    $this->data['datecount'] = Carbon::parse($enddate)
                                                     ->diffInDays(Carbon::parse($startdate));
                    $this->data['lastdate'] = $enddate;

                    return view('modules.churn.index' ,$this->data);
                }
        }
