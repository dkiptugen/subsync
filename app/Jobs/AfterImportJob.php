<?php

    namespace App\Jobs;

    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldBeUnique;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Log;
    use Maatwebsite\Excel\Events\AfterImport;
    use Maatwebsite\Excel\Importer;

    class AfterImportJob implements ShouldQueue
        {
            use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

            protected $importer;
            protected $event;

        /**
         * Create a new job instance.
         */
            public function __construct(Importer $importer, AfterImport $event)
                {
                    $this->importer = $importer;
                    $this->event    = $event;
                }

        /**
         * Execute the job.
         */
            public function handle()
            : void
                {
                    $rowsImported = $this->event->getConcernable()->getRowCount();
                    Log::info("Import completed. Rows imported: $rowsImported");
                }
        }
