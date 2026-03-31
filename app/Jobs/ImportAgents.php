<?php

namespace App\Jobs;

use App\Models\Agent;
use Illuminate\Support\Collection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportAgents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels
        ;

    /**
     * Create a new job instance.
     */
    public function __construct(public Collection $data)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if($this->data->isNotEmpty())
        {
            foreach ($this->data[0]->skip(1) as $row) {
                try{
                    $name=@$row[0];
                    $email=@$row[1];
                    $type=@$row[2];
                    $department=@$row[3];
                    $country=@$row[4];

                    if(is_null($email)) continue;

                    $record = [
                        'name' => $name,
                        'email' => $email,
                        'type' => $type,
                        'department' => $department,
                        'country' => $country
                    ];

                    $agent = Agent::updateOrCreate(['email' => $email], $record);
                }catch (\Exception $e){
                    report($e);
                }
            }
        }
    }
}
