<?php

namespace App\Enums;

enum PaymentStageEnum :int
    {
        case production  = 1;
        case development = 2;
    }
