<?php
	
	namespace App\Enums;
	
	enum  EmailType: int
		{
			case ExpireSoon      = 1;
			case Expired         = 2;
			case PasswordReset   = 3;
			case Marketing       = 4;
			case NewSubscription = 5;
			case Receipt         = 6;
			case Verification   = 7;
		}