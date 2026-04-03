<?php
	
	namespace App\Macros;
	
	use Illuminate\Support\Collection;
	
	class CollectionMacros
		{
			public static function register()
				{
					Collection::macro('toUpper',function(){
						return $this->map(function($value){
							return strtoupper($value);
						});
					});
					Collection::macro('averageLength',function(){
						return $this->map(function($value){
							return strlen($value);
						})->avg();
					});
				}
		}