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

					'Illuminate\Auth\Events\Registered'          => [
						'App\Listeners\LogRegisteredUser',
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
