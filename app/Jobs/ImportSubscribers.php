<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NewUserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ImportSubscribers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels
        ;

    /**
     * Create a new job instance.
     */
    public function __construct(public Collection $data)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->data->isNotEmpty()) {
            foreach ($this->data[0]->skip(1) as $row) {
                $name = $row[0];
                $email = $row[1] ?? null;
                $password = $row[2] ?? null;
                $phone = $row[3] ?? null;
                $change_password = $row[4] ?? "FALSE";
                $real_email = $row[5] ?? "FALSE";

                if ($email /*&& filter_var($email, FILTER_VALIDATE_EMAIL)*/) {
                    $details = [
                        'name' => $name,
                        'surname' => null,
                        'phone' => $phone,
                        'status' => 1,
                        'can_notify' => 1,
                        'daily_notifications' => 1,
                        'last_login' => now(),
                    ];
                    //if($change_password == "TRUE")
                    $details['password'] = bcrypt(trim($password));

                    $user = User::updateOrCreate(['email' => trim($email)],$details );

                    if($real_email == "TRUE")
                    {
                        //$user->notify(new NewUserNotification($user, $password));
                        if($change_password == "TRUE")
                        {
                            //
                        }
                    }

                }
            }
        }
    }
}
