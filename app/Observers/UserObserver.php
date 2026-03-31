<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\UserVerificationNotification;
use App\Traits\Meta;
use Exception;
use Illuminate\Support\Facades\Log;


class UserObserver
    {
        use Meta;

    /**
     * Handle the User "created" event.
     */
        public function created(User $user): void
            {

                try
                    {
                        //$user->withoutVerifying();
                        if ($user->type == 'customer')
                            {

                            }

                        //$user->notify(new UserVerificationNotification($user));
                    }
                catch (Exception $e)
                    {
                        Log::error('User First verification: ' . $e->getMessage());
                    }


                //::
            }

    /**
     * Handle the User "updated" event.
     */
        public function updated(User $user): void
            {
                //
            }

    /**
     * Handle the User "deleted" event.
     */
        public function deleted(User $user): void
            {
                //
            }

    /**
     * Handle the User "restored" event.
     */
        public function restored(User $user): void
            {
                //
            }

    /**
     * Handle the User "force deleted" event.
     */
        public function forceDeleted(User $user): void
            {
                //
            }
    }
