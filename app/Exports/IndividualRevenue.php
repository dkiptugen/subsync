<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;

class IndividualRevenue implements FromCollection
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
                return Transaction::whereStatus(1)
                                  ->whereBetween('transaction_date', [$this->startdate, $this->enddate])
                                  ->when(!is_null($this->product), function ($query)
                                      {
                                          $query->whereHas('subscription', function ($q)
                                              {
                                                  $q->whereIn('product_id', $this->product);
                                              });
                                      })
                                  ->when(!is_null($this->ratetype), function ($query)
                                      {
                                          $query->whereHas('subscription.rate', function ($q)
                                              {
                                                  $q->whereIn('rate_type_id', $this->ratetype);
                                              });
                                      })
                                  ->orderBy('transaction_date', 'ASC')
                                  ->get();
            }
    }
