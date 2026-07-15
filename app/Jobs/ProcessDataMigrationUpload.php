<?php

namespace App\Jobs;

use App\Imports\CorporateUsersImport;
use App\Imports\IndividualImport;
use App\Imports\OrganizationImport;
use App\Imports\RateImport;
use App\Models\DataMigrationUpload;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessDataMigrationUpload implements ShouldQueue
{
    use Queueable;

    public int $timeout = 0;

    public int $tries = 1;

    public function __construct(public string $uploadId) {}

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $upload = DataMigrationUpload::findOrFail($this->uploadId);

        $upload->update([
            'status' => 'processing',
            'progress' => 10,
            'message' => 'Import job started.',
            'started_at' => Carbon::now(),
        ]);

        Excel::import($this->importer($upload), $upload->path, $upload->disk);

        $upload->update([
            'status' => 'completed',
            'progress' => 100,
            'processed_files' => 1,
            'message' => 'Import completed successfully.',
            'completed_at' => Carbon::now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $upload = DataMigrationUpload::find($this->uploadId);

        if (! $upload) {
            return;
        }

        $upload->update([
            'status' => 'failed',
            'progress' => 100,
            'error' => $exception->getMessage(),
            'message' => 'Import failed.',
            'completed_at' => Carbon::now(),
        ]);

        Log::error('Data migration import failed.', [
            'upload_id' => $this->uploadId,
            'error' => $exception->getMessage(),
        ]);
    }

    private function importer(DataMigrationUpload $upload): object
    {
        return match ($upload->type) {
            'rates' => new RateImport($upload->user_id),
            'individuals' => new IndividualImport($upload->user_id),
            'organizations' => new OrganizationImport($upload->user_id),
            'corporate_users' => new CorporateUsersImport($upload->user_id),
            default => throw new Exception("Unsupported migration type [{$upload->type}]."),
        };
    }
}
