<?php

namespace App\Http\Controllers;

class ReportsController extends Controller
    {
        public function reg_index()
            {
                return view('modules.reportds.subscribers', $this->data);
            }
    }
