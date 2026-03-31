<?php
	
	namespace App\Providers;
	
	use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
	
	class EventServiceProvider extends ServiceProvider
		{
		/**
		 * The event to listener mappings for the application.
		 *
		 * @var array<class-string, array<int, class-string>>
		 */
			protected $observers
				= [
				
				];
			protected $listen
				= [
					/* Registered::class => [
						 SendEmailVerificationNotification::class,
					 ],*/
					'Illuminate\Auth\Events\Registered'          => [
						'App\Listeners\LogRegisteredUser',
					],
					'Laravel\Passport\Events\AccessTokenCreated' => [
						'App\Listeners\RevokeOldTokens',
					],
					
					'Laravel\Passport\Events\RefreshTokenCreated' => [
						'App\Listeners\PruneOldTokens',
					],
				
				];
		
		/**
		 * Register any events for your application.
		 *
		 * @return void
		 */
			public function boot()
				{
					//
				}
		
		/**
		 * Determine if events and listeners should be automatically discovered.
		 *
		 * @return bool
		 */
			public function shouldDiscoverEvents()
				{
					
					return true;
				}
		}
