<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserWhitelistJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable
        //, SerializesModels
        ;

    public $users;
    public $request;
    public $support_user;
    /**
     * Create a new job instance.
     */
    public function __construct($users,$support_user,$request)
    {
        $this->users = $users;
        $this->request = $request;
        $this->support_user = $support_user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::whereIn('email', $this->users)->get();

        foreach ($users as $user) {
            $wl = $user->whitelist()->updateOrCreate([
                'product_id' => $this->request->product,
                'user_id' => $this->support_user,
                'startdate' => Carbon::parse($this->request->startdate)->startOfDay()->toDateTimeString(),
                'enddate' => Carbon::parse($this->request->enddate)->endOfDay()->toDateTimeString(),
                ],
                [
                'product_id' => $this->request->product,
                'user_id' => $this->support_user,
                'reason' => $this->request->reason,
                'status' => 1,
                'startdate' => Carbon::parse($this->request->startdate)->startOfDay()->toDateTimeString(),
                'enddate' => Carbon::parse($this->request->enddate)->endOfDay()->toDateTimeString(),
                'tag' => $this->request->tag,
            ]);

            //attach_products($wl);
        }
    }
}
