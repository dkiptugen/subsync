<?php
	
	namespace App\Macros;
	
	use Illuminate\Database\Eloquent\Builder;
	
	class BuilderMacros
		{
			public static function register()
				{
					Builder::macro('active',function(){
						return $this->where('status',1);
					});
				}
		}