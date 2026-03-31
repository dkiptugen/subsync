<?php

namespace App\Exports;

use App\Models\Subscription;
use Maatwebsite\Excel\Concerns\FromCollection;

class IndividualActivatedAccounts implements FromCollection
    {
        private $startdate;
        private $enddate;
        private $ratetype;
        private $product;

        public function __construct($startdate, $enddate, $ratetype, $product)
            {
                $this->startdate = $startdate;
                $this->enddate   = $enddate;
                $this->ratetype  = $ratetype;
                $this->product   = $product;
            }

    /**
     * @return \Illuminate\Support\Collection
     */
        public function collection()
            {
                return Subscription::with(['activator', 'product', 'rate'])->where('subscription_date', '>=', $this->startdate)
                                   ->where('subscription_date', '<=', $this->enddate)
                                   ->where('activator_id', '!=', 0)
                                   ->when(!is_null($this->product), function ($query)
                                       {

                                           $query->whereIn('product_id', $this->product);

                                       })
                                   ->when(!is_null($this->ratetype), function ($query)
                                       {
                                           $query->whereHas('rate', function ($q)
                                               {
                                                   $q->whereIn('rate_type_id', $this->ratetype);
                                               });
                                       })
                                   ->whereStatus(1)
                                   ->get();
            }
    }
