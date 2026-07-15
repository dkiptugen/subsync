<?php

namespace App\Imports;

use App\Jobs\AfterImportJob;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Rate;
use App\Models\RateType;
use App\Models\Region;
use App\Models\Site;
use App\Models\Subscription;
use App\Models\SubscriptionGroup;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\NewUserNotification;
use App\Traits\Meta;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class IndividualImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    use Meta;

    // use Queueable;

    public $timeout = 0;

    public $user;

    public function __construct($user = 0)
    {
        $this->user = $user;
    }

    /**
     * @param  Collection  $collection
     */
    public function collection(Collection $rows)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        // DB::transaction();
        foreach ($rows as $row) {

            try {
                DB::transaction(function () use ($row) {
                    $user = User::updateOrCreate([
                        'email' => $row['email'],
                    ],
                        [
                            'username' => Str::slug($row['name']),
                            'name' => $row['name'],
                            'status' => 1,
                            'password' => bcrypt($row['password'] ?? 'Nation.1234'),
                            'type' => 'customer',
                        ]);

                    try {
                        $user->notify(new NewUserNotification($user, $row['password'] ?? 'Nation.1234'));
                        $this->verify_email($user->email);

                    } catch (Exception $exception) {
                        Log::error($exception->getMessage());
                    }

                    $product = Product::where('product_name', $row['product'])
                        ->first();
                    $region = Region::where('currency_code', $row['currency'])
                        ->first();
                    if (is_null($product)) {

                        $unID = explode(' ', $row['product']);
                        $id = strtoupper(substr($unID[0], 0, 2).'-'.substr($unID[1] ?? Str::random(), 0, 2));
                        $site = Site::updateOrCreate([
                            'site_name' => $row['product'],
                            'region_id' => $region->id ?? 0,
                        ],
                            [
                                'site_url' => $row['product_link'] ?? 'https://www.test.import',
                            ]);

                        $product = Product::firstOrCreate([
                            'identifier' => $id,
                        ],
                            [
                                'product_name' => $row['product'],
                                'payment_methods' => [1],
                                'product_link' => $row['product_link'] ?? 'https://www.test.import',
                                'user_id' => $this->user,
                                'status' => 1,
                                'site_id' => $site->id,
                            ]);

                    }
                    $rate = Rate::where('name', $row['rate_type'])
                        ->whereHas('product', function ($query) use ($row) {
                            $query->where('product_name', $row['product']);
                        })
                        ->first();
                    if (is_null($rate)) {
                        $rateType = RateType::where('name', $row['rate_type'])
                            ->first();
                        Rate::updateOrCreate([
                            'product_id' => $product->id,
                            'rate_type_id' => $rateType->id,
                        ],
                            [
                                'name' => $rateType->name,
                                'period' => $rateType->period,
                                'cost' => $row['amount_paid'],
                                'currency' => $row['currency'],
                                'region_id' => $region->id,
                                'status' => 1,
                                'user_id' => $this->user,
                                'start_date' => '2000-01-01',
                            ]);
                        // return throw new \Exception('rate not found' . $row['product'] . '-' . $row['rate_type']);
                    }

                    $subg = SubscriptionGroup::firstOrCreate([
                        'subdate' => Date::excelToDateTimeObject($row['startdate']),
                    ], [
                        'identifier' => $this->identifer('SubscriptionGroup', 'identifier'),
                    ]);

                    if ($subg) {
                        $pym = PaymentMethod::first();
                        $subs = Subscription::updateOrCreate([
                            'subscription_date' => Carbon::parse(Date::excelToDateTimeObject($row['startdate']))->startOfDay(),
                            'expiry_date' => Carbon::parse(Date::excelToDateTimeObject($row['enddate']))->endOfDay(),
                            'user_id' => $user->id,
                            'product_id' => $rate->product_id,
                        ],
                            [
                                'identifier' => $this->identifer('Subscription', 'identifier'),
                                'subscription_group_id' => $subg->id,
                                'reccurent_cycle' => 1,
                                'rate_id' => $rate->id,
                                'reccuring' => 1,
                                'status' => 1,
                                'activator_id' => $this->user,
                                'activator_reason' => 'Mega Import from previous system',
                            ]);
                        if ($subs) {

                            $trans = new Transaction;
                            $trans->identifier = $this->identifer('Transaction', 'identifier');
                            $trans->subscription_id = $subs->id;
                            $trans->payment_method_id = $pym->id ?? 1;
                            $trans->{'channel'} = $row['payment_channel'] ?? 'import';
                            $trans->receipt = strtoupper(Str::random(15));
                            $trans->initiator = $user->email;
                            $trans->total_amount = $row['rate'];
                            $trans->amount = $row['amount_paid'];
                            $trans->status = 1;
                            $trans->user_id = $user->id;
                            $trans->currency = $row['currency'];
                            $trans->reserved_currency = 'USD';
                            $trans->reserved_currency_amount = $this->currency_convert($row['amount_paid'], $row['currency'], 'USD');
                            $trans->transaction_date = Date::excelToDateTimeObject($row['startdate']);
                            $trans->amount_paid = $row['amount_paid'];
                            $trans->type = 'initial';
                            $trans->save();
                        }
                    }
                }, 5);
                DB::commit();

            } catch (Exception $e) {
                Log::error($e->getMessage());
            }

        }
        // DB::commit();

    }

    public function chunkSize(): int
    {

        return 100;
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
