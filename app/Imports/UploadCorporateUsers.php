<?php

    namespace App\Imports;

    use App\Jobs\AfterImportJob;
    use App\Models\PasswordReset;
    use App\Models\User;
    use App\Notifications\NewUserNotification;
    use App\Notifications\PasswordResetRequest;
    use App\Notifications\UserVerificationNotification;
    use App\Traits\Meta;
    use Carbon\Carbon;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Maatwebsite\Excel\Concerns\ToCollection;
    use Maatwebsite\Excel\Concerns\WithChunkReading;
    use Maatwebsite\Excel\Concerns\WithHeadingRow;
    use Maatwebsite\Excel\Events\AfterImport;

    class UploadCorporateUsers implements ToCollection, WithHeadingRow, WithChunkReading
        {
            use Meta;
            //use Queueable;
            public $timeout = 0;
            public $org_id;
            public $user;
            public function __construct($org_id,$user=0)
                {
                    $this->user =$user;
                    $this->org_id = $org_id;
                }

        /**
         * @param Collection $collection
         */
            public function collection(Collection $collection)
                {
                    set_time_limit(0);
                    $platform = 'epaper';

                    foreach ($collection as $row)
                        {
                            try
                                {
                                    DB::transaction(function () use($row)
                                        {
                                            $token = Str::ulid();
                                            $user  = User::updateOrCreate([
                                                'email' => $row['email']
                                            ],
                                                [
                                                    'name'               => $row['name'],
                                                    'password'           => bcrypt($row['password'] ?? 'Nation.1234'),
                                                    'phone'               => $row['phone'],
                                                    'status'             => 1,
                                                    'remember_token'     => $token,
                                                    'verification_token' => Str::ulid(),
                                                    'type'               => 'organization',
                                                    'organization_id'    => $this->org_id,
                                                    'daily_notifications' => 1
                                                ]);
                                            try
                                                {
                                                    //$this->verify_email($user->email);

                                                }
                                            catch(\Exception $exception)
                                                {
                                                    Log::error($exception->getMessage());
                                                }
                                            if ($user)
                                                {

                                                    if ((bool)$row['real_email'])
                                                        {
                                                            //check if user creation is less than 5 minutes from now first
                                                            if($user->created_at >= Carbon::now()->subMinutes(2))
                                                                $user->notify(new NewUserNotification($user, $row['password']));
//                                                            $user->notify(new UserVerificationNotification($user,'null'));
                                                            if ((bool)$row['change_password'])
                                                                {
                                                                    PasswordReset::updateOrCreate([
                                                                        'email' => $user->email
                                                                    ],
                                                                        [
                                                                            'token'      => $token,
                                                                            'expires_in' => config('custom.CUSTOMER.TOKEN_EXPIRY') * 24 * 60 * 60,
                                                                            'created_at' => Carbon::now()->toDateTimeString()
                                                                        ]);
                                                                    $endpoint = email_link(@$row['platform'] ?? 'epaper');
                                                                    $redirect_url = extract_base_url($endpoint);
                                                                    $user->notify(new PasswordResetRequest($user, $endpoint, 'Nation Org', $redirect_url, $token,$user->created_at));
                                                                }
                                                        }
                                                }
                                        },5);
                                    DB::commit();
                                }
                            catch (\Exception $e)
                                {
                                    report($e);
                                }

                        }

                }
            public function batchSize(): int
                {
                    return 1000;
                }

            public function chunkSize()
            : int
                {

                    return 1000;
                }
            public function registerEvents(): array
                {
                    return [
                        AfterImport::class => function (AfterImport $event) {
                            // Trigger the AfterImportJob
                            AfterImportJob::dispatch($this, $event);
                        },
                    ];
                }
        }
