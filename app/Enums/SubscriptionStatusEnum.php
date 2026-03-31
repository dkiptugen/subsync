<?php

    namespace App\Enums;

    enum SubscriptionStatusEnum: int
        {
            case Active   = 1;
            case Inactive = 0;
            case Panding  = 2;
        }
