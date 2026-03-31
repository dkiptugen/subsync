<?php

namespace App\Imports;

use AllowDynamicProperties;
use App\Jobs\AfterImportJob;
use App\Models\B2bSubscription;
use App\Models\B2bSubscriptionUser;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use App\Notifications\NewUserNotification;
use App\Traits\Meta;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;


#[AllowDynamicProperties] class SubscriptionAssignImport implements ToCollection, WithHeadingRow, WithChunkReading
    {
    //use Queueable;
        use Meta;

        public $timeout = 0;
        public $subscriptionId;

        public $user;

        public function __construct($subscriptionId, $user = 0)
            {
                $this->user = $user;

                $this->subscriptionId = $subscriptionId;
            }

    /**
     * @param Collection $collection
     */
        public function collection(Collection $collection)
            {
                set_time_limit(0);
                $sub = B2bSubscription::find($this->subscriptionId);
                if (is_null($sub))
                    {
                        return throw new Exception('Subscription does not exist');
                    }
                else
                    {
                        if ($sub->records >= $sub->accounts)
                            {
                                return throw new Exception('Subscription exceeds account limit');
                            }
                        else
                            {
                                if (($sub->accounts - $sub->records) < ($collection->count() - 1) )
                                    {
                                        //return throw new Exception('Uploaded accounts exceeds account limit' . $collection->count());
                                    }
                                /* if (Carbon::parse($sub->expiry_date)->endOfDay()->gt(Carbon::now()))
                                     {
                                         return  throw new Exception('You cannot assign a new account on an expired subscription'.(int)(Carbon::parse($sub->expiry_date)->endOfDay()->gt(Carbon::now())));
                                     }*/
                                foreach ($collection as $row)
                                    {
                                        DB::transaction(function () use ($row, $sub)
                                            {
                                                $token = Str::ulid();
                                                $user  = User::where('email', trim($row['email']))
                                                             ->first();
                                                if (is_null($user))
                                                    {
                                                        $user = User::updateOrCreate([
                                                                                         'email' => $row['email']
                                                                                     ],
                                                                                     [
                                                                                         'name'               => $row['name'],
                                                                                         'password'           => bcrypt($row['password'] ?? 'Nation.1234'),
                                                                                         'status'             => 1,
                                                                                         'remember_token'     => $token,
                                                                                         'verification_token' => Str::ulid(),
                                                                                         'type'               => 'organization',
                                                                                         'organization_id'    => $sub->organization_id
                                                                                     ]);

//                                                        try{
//                                                            $user->notify(new NewUserNotification($user, $row['password'] ?? 'Nation.1234'));
//                                                        }catch(Exception $e){
//
//                                                        }
                                                        //$user->notify(new PasswordResetRequest($user, $endpoint, 'Nation Org', 'https://www.nation.africa', $token));
                                                    }
                                                else
                                                    {
                                                        $user->organization_id = $sub->organization_id;
                                                        $user->status          = 1;
                                                        $user->save();
                                                    }
//                                                try
//                                                    {
//                                                        $this->verify_email((string)$user->email);
//
//                                                    }
//                                                catch (Exception $exception)
//                                                    {
//                                                        Log::error($exception->getMessage());
//                                                    }

                                                $check = B2bSubscriptionUser::where('user_id', $user->id)
                                                                            ->where('b2b_subscription_id', $this->subscriptionId)
                                                                            ->first();
                                                if (is_null($check))
                                                    {
                                                        $subscription                      = new B2bSubscriptionUser();
                                                        $subscription->user_id             = $user->id;
                                                        $subscription->b2b_subscription_id = $this->subscriptionId;
                                                        $subscription->save();
                                                        $sub->increment('records');
                                                        $sub->save();
//                                                        try
//                                                            {
//                                                                $user->notify(new NewSubscriptionNotification($user, $subscription->product));
//
//                                                            }
//                                                        catch (Exception $e)
//                                                            {
//                                                                Log::error($e->getMessage());
//                                                            }

                                                    }
                                            }, 5);
                                        DB::commit();

                                    }
                            }
                    }
            }

        public function batchSize(): int
            {
                return 1000;
            }

        public function chunkSize(): int
            {

                return 1000;
            }

        public function registerEvents(): array
            {
                return [
                    AfterImport::class => function (AfterImport $event)
                        {
                            // Trigger the AfterImportJob
                            AfterImportJob::dispatch($this, $event);
                        },
                ];
            }
    }
