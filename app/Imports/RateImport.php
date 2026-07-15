<?php

namespace App\Imports;

use App\Jobs\AfterImportJob;
use App\Models\Product;
use App\Models\Rate;
use App\Models\RateType;
use App\Models\Region;
use App\Models\Site;
use App\Traits\Meta;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;

class RateImport implements ToCollection, WithChunkReading, WithHeadingRow
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
     * @throws Exception
     */
    public function collection(Collection $rows)
    {
        set_time_limit(0);
        foreach ($rows as $row) {
            try {
                DB::transaction(function () use ($row) {
                    $iso2 = explode('-', $row['unique_id']);
                    $region = Region::where('code', $iso2[1])
                        ->first();

                    $site = Site::updateOrCreate([
                        'site_name' => $row['unique_id'],
                        'region_id' => $region->id ?? 0,
                    ],
                        [
                            'site_url' => $row['product_link'],
                        ]);

                    $product = Product::firstOrCreate([
                        'identifier' => $row['unique_id'],
                    ],
                        [
                            'product_name' => $row['product_name'],
                            'payment_methods' => [1],
                            'product_link' => $row['product_link'],
                            'user_id' => $this->user,
                            'status' => 1,
                            'site_id' => $site->id,
                        ]);

                    if (! is_null($product)) {
                        $rateType = RateType::updateOrCreate([
                            'name' => $row['rate_type'],
                        ],
                            [
                                'period' => $row['period'],
                                'dow' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                            ]);
                        if (! is_null($rateType)) {
                            Rate::updateOrCreate([
                                'product_id' => $product->id,
                                'rate_type_id' => $rateType->id,
                            ],
                                [
                                    'name' => $rateType->name,
                                    'period' => $rateType->period,
                                    'cost' => $row['amount'],
                                    'currency' => $row['currency'],
                                    'region_id' => $region->id,
                                    'status' => 1,
                                    'user_id' => $this->user,
                                    'start_date' => '2000-01-01',
                                ]);

                        }
                    }
                }, 5);
                DB::commit();
            } catch (Exception $e) {
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
            AfterImport::class => function (AfterImport $event) {
                // Trigger the AfterImportJob
                AfterImportJob::dispatch($this, $event);
            },
        ];
    }
}
