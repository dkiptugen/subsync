<?php

namespace App\Enums;

enum PaymentTypeIdentifiers: int
    {
        case msisdn     = 1;
        case tillnumber = 2;
        case shortcode  = 4;

    }
