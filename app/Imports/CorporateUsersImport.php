<?php

namespace App\Imports;

use App\Jobs\AfterImportJob;
use App\Models\B2bSubscription;
use App\Models\B2bSubscriptionUser;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use App\Notifications\NewUserNotification;
use App\Traits\Meta;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class CorporateUsersImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    use Meta;

    public $timeout = 0;

    public $user;

    public function __construct($user = 0)
    {
        $this->user = $user;
    }

    public function collection(Collection $collection)
    {
        set_time_limit(0);

        foreach ($collection as $data) {
            try {

                $organization = Organization::where('name', $data['organization'])
                    ->first();
                if (! is_null($organization)) {
                    $name = explode(' ', ucwords($data['name']));
                    $user = User::updateOrCreate([
                        'email' => $data['email'],
                    ],
                        [
                            'username' => Str::slug($data['name'].' Admin'),
                            'name' => trim($name[0]),
                            'surname' => trim($name[1] ?? ''),
                            'status' => 1,
                            'password' => bcrypt($data['password'] ?? 'Nation.1234'),
                            'type' => 'organization',
                            'organization_id' => $organization->id,
                        ]);
                    if (! is_null($user)) {

                        try {
                            $user->notify(new NewUserNotification($user, $data['password'] ?? 'Nation.1234'));
                            $this->verify_email($user->email);
                        } catch (Exception $exception) {
                            Log::error($exception->getMessage());
                        }
                        if (! Cache::has('product-'.$data['product'])) {
                            $product = Product::where('product_name', $data['product'])
                                ->first();
                            if (! is_null($product)) {
                                Cache::set('product-'.$data['product'], $product);
                            }

                        }
                        $subscription = B2bSubscription::where('organization_id', $organization->id)
                            ->whereDate('start_date', Carbon::parse(Date::excelToDateTimeObject((int) $data['start_date']))->toDateString())
                            ->whereDate('expiry_date', Carbon::parse(Date::excelToDateTimeObject((int) $data['end_date']))->toDateString())
                            ->where('product_id', Cache::get('product-'.$data['product'])->id)
                            ->first();
                        if (! is_null($subscription)) {
                            if ($subscription->accounts >= $subscription->records) {
                                $subscription = B2bSubscription::where('organization_id', $organization->id)
                                    ->whereDate('start_date', Carbon::parse(Date::excelToDateTimeObject((int) $data['start_date']))->toDateString())
                                    ->whereDate('expiry_date', Carbon::parse(Date::excelToDateTimeObject((int) $data['end_date']))->toDateString())
                                    ->where('product_id', Cache::get('product-'.$data['product'])->id)
                                    ->where('accounts', '!=', $subscription->accounts)
                                    ->where('records', '!=', $subscription->records)
                                    ->first();
                            }
                            $b2busers = B2bSubscriptionUser::updateOrCreate(
                                [
                                    'b2b_subscription_id' => $subscription->id,
                                    'user_id' => $user->id,
                                ]
                            );
                            if ($b2busers) {

                                $subscription->increment('records');
                                $subscription->save();
                                $user->notify(new NewSubscriptionNotification($user, $subscription->product));
                            }

                        }

                    }
                }

            } catch (Exception $e) {
                Log::error($e->getMessage());
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
            AfterImport::class => function (AfterImport $event) {
                // Trigger the AfterImportJob
                AfterImportJob::dispatch($this, $event);
            },
        ];
    }
}
