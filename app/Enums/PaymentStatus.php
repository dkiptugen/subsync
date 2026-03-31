<?php
	
	namespace App\Enums;
	
	enum PaymentStatus: int
		{
			case Active   = 1;
			case Inactive = 0;
		}
