<?php

namespace App\Imports;

use App\Jobs\AfterImportJob;
use App\Models\B2bPurchase;
use App\Models\B2bPurchaseDetail;
use App\Models\B2bSubscription;
use App\Models\B2bTransaction;
use App\Models\Organization;
use App\Models\Rate;
use App\Models\User;
use App\Traits\Meta;
use Carbon\Carbon;
use Exception;
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
use PhpOffice\PhpSpreadsheet\Shared\Date;

class OrganizationImport implements ToCollection, WithHeadingRow, WithChunkReading
    {
        use Meta;
        //use Queueable;

        public $timeout = 0;
        public $user;

        public function __construct($user = 0)
            {
                $this->user = $user;
            }

    /**
     * @param Collection $collection
     */
        public function collection(Collection $rows)
            {

                set_time_limit(0);
                foreach ($rows as $row)
                    {
                        try
                            {
	                            DB::transaction(function () use($row)
		                            {
			                            $user = User::updateOrCreate([
				                            'email' => $row['email']
			                            ],
				                            [
					                            'username' => Str::slug($row['name'] . ' Admin'),
					                            'name'     => $row['name'] . ' Admin',
					                            'status'   => 1,
					                            'password' => bcrypt($row['password']??'Nation.1234'),
					                            'type'     => 'organization'
				                            ]);
                                        try
                                            {
                                                $this->verify_email($user->email);

                                            }
                                        catch(\Exception $exception)
                                            {
                                                Log::error($exception->getMessage());
                                            }
			                            if ($user)
				                            {
					                            $rate = Rate::where('name', $row['subtype'])
					                                        ->whereHas('product', function ($query) use ($row)
						                                        {
							                                        $query->where('product_name', $row['product']);

						                                        })
					                                        ->first();
					                            $org  = Organization::updateOrCreate([
						                            'name' => $row['name']
					                            ],
						                            [
							                            'status'  => 1,
							                            'user_id' => $user->id
						                            ]);

					                            if ($row['payment_channel'] == 'LPO')
						                            {
							                            $purchase = B2bPurchase::where('organization_id', $org->id)
							                                                   ->where('created_at', Carbon::parse(Date::excelToDateTimeObject((int)$row['startdate']))->startOfDay())
							                                                   ->first();
							                            if (!is_null($purchase))
								                            {
									                            $purchase->increment('full_amount', $rate->cost);
									                            $purchase->products = array_merge($purchase->products, [$row['product']]);
									                            $purchase->save();
								                            }
							                            else
								                            {
									                            $purchase                      = new B2bPurchase();
									                            $purchase->identifier          = Str::random(13);
									                            $purchase->organization_id     = $org->id;
									                            $purchase->full_amount         = $rate->cost;
									                            $purchase->balance             = 0;
									                            $purchase->is_paid             = 1;
									                            $purchase->user_id             = $user->id;
									                            $purchase->cc_approver_id      = $this->user;
									                            $purchase->finance_approver_id = $this->user;
									                            $purchase->status              = 1;
									                            $purchase->products            = [$row['product']];
									                            $purchase->created_at          = Carbon::parse(Date::excelToDateTimeObject((int)$row['startdate']))
									                                                                   ->startOfDay()->toDateTimeString();
									                            $purchase->save();
								                            }
							                            $pd                  = new B2bPurchaseDetail();
							                            $pd->b2b_purchase_id = $purchase->id;
							                            $pd->rate_id         = $rate->id;
							                            $pd->product_id      = $rate->product_id;
							                            $pd->accounts        = $row['accounts'];
							                            $pd->cost            = (int)$rate->cost;
							                            $pd->save();
						                            }

					                            $receipt     = 'import-' . time();
					                            $sub         = B2bSubscription::firstOrCreate([
						                            'organization_id'   => $org->id,
						                            'product_id'        => $rate->product_id,
						                            'subscription_type' => $rate->name,
						                            'start_date'        => Carbon::parse(Date::excelToDateTimeObject((int)$row['startdate']))->startOfDay()->toDateTimeString(),
						                            'expiry_date'       => Carbon::parse(Date::excelToDateTimeObject((int)$row['enddate']))->endOfDay()->toDateTimeString(),
						                            'accounts'          => (int)$row['accounts'],
						                            'channel'           => $row['payment_channel'],
						                            'receipt'           => $receipt

					                            ], [
						                            'b2b_purchase_id'  => $purchase->id ?? 0,
						                            'status'           => 1,
						                            'amount'           => ($rate->cost * (int)$row['accounts']),
						                            'amount_paid'      => ($row['payment_channel'] == 'LPO') ? 0 : ($rate->cost * (int)$row['accounts']),
						                            'activator_id'     => $this->user,
						                            'activator_reason' => 'Mega Import from previous system'
					                            ]);
					                            $transaction = B2bTransaction::updateOrCreate([
						                            'b2b_subscription_id' => $sub->id,
						                            'amount_paid'         => $sub->amount_paid,
						                            'pay_channel'         => $row['payment_channel'],
						                            'date_paid'           => Carbon::parse(Date::excelToDateTimeObject((int)$row['startdate']))->startOfDay()->toDateTimeString(),

					                            ], [
						                            'identifier'      => Str::ulid(),
						                            'b2b_purchase_id' => $purchase->id ?? 0,
						                            'user_id'         => $user->id,
						                            'receipt'         => $receipt,
					                            ]);


				                            }
		                            },5);
								DB::commit();
                            }
                        catch (Exception $e)
                            {
                                Log::error($e->getMessage());
                                throw new Exception($e->getMessage());

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
