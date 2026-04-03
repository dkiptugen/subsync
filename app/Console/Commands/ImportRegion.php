<?php

namespace App\Console\Commands;

use App\Models\Region;
use Illuminate\Console\Command;

class ImportRegion extends Command
    {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
        protected $signature = 'region:import';

    /**
     * The console command description.
     *
     * @var string
     */
        protected $description = 'Import Regions to the map';

    /**
     * Execute the console command.
     *
     * @return int
     */
        public function handle()
            {
                $reg     = file_get_contents(__DIR__ . '/Regions.json');
                $regions = json_decode($reg);
                foreach ($regions as $region)
                    {
                        $country                  = new Region();
                        $country->name            = $region->name;
                        $country->code            = $region->code;
                        $country->capital         = $region->capital;
                        $country->currency        = $region->currency->name;
                        $country->currency_code   = $region->currency->code;
                        $country->currency_symbol = $region->currency->symbol;
                        $country->language        = $region->language->name;
                        $country->language_code   = $region->language->code;
                        $country->flag            = $region->flag;
                        $country->save();
                        echo "\n" . $region->name;
                    }

                return Command::SUCCESS;
            }
    }
