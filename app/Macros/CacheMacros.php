<?php
	
	namespace App\Macros;
	
	use Illuminate\Support\Facades\Cache;
	
	class CacheMacros
		{
			public static function register()
				{
					Cache::macro('rememberOnce',function($key,$ttl,$callback){
						if(Cache::has($key))
							{
								return Cache::get($key);
							}
						$value = $callback;
						Cache::put($key,$value,$ttl);
						return  $value;
					});
			}
		}