<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDataMigrationUpload;
use App\Models\DataMigrationUpload;
use App\Traits\Meta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class DataMigController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    public function rate_form()
    {
        $this->data['migrationType'] = 'rates';
        $this->data['uploads'] = $this->recentUploads('rates');

        return view('modules.migration.rates', $this->data);
    }

    public function rate(Request $request)
    {
        return $this->queueMigration($request, 'rates', 'Rates import', route('migrates.index'));
    }

    public function individual_form()
    {
        $this->data['migrationType'] = 'individuals';
        $this->data['uploads'] = $this->recentUploads('individuals');

        return view('modules.migration.individuals', $this->data);
    }

    public function individual(Request $request)
    {
        return $this->queueMigration($request, 'individuals', 'Individual Accounts import', route('migindividuals.index'));

    }

    public function organization_form()
    {
        $this->data['migrationType'] = 'organizations';
        $this->data['uploads'] = $this->recentUploads('organizations');

        return view('modules.migration.organizations', $this->data);
    }

    public function organization(Request $request)
    {
        return $this->queueMigration($request, 'organizations', 'Organizations import', route('migorganizations.index'));

    }

    public function corporate_users_form()
    {
        $this->data['migrationType'] = 'corporate_users';
        $this->data['uploads'] = $this->recentUploads('corporate_users');

        return view('modules.migration.corporate_users', $this->data);
    }

    public function corporate_users(Request $request)
    {
        return $this->queueMigration($request, 'corporate_users', 'Corporate Users import', route('migorganizationusers.index'));

    }

    public function sample(string $type): BinaryFileResponse
    {
        abort_unless(array_key_exists($type, $this->sampleColumns()), 404);

        return response()
            ->download($this->buildSampleWorkbook($type), $type.'-sample.xlsx')
            ->deleteFileAfterSend();
    }

    private function queueMigration(Request $request, string $type, string $title, string $redirect)
    {
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:51200'],
        ]);

        foreach ($validated['files'] as $file) {
            $path = $file->storeAs(
                'data-migrations/'.$type,
                Str::ulid().'-'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension(),
                's3'
            );

            if (! $path || ! Storage::disk('s3')->exists($path)) {
                return self::failed($title, 'File upload to S3 failed. Check S3 credentials and bucket access.', $redirect);
            }

            $upload = DataMigrationUpload::create([
                'type' => $type,
                'user_id' => Auth::id(),
                'disk' => 's3',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'status' => 'pending',
                'progress' => 0,
                'message' => 'File uploaded to S3. Import job queued.',
            ]);

            ProcessDataMigrationUpload::dispatch($upload->id)->onQueue('low');
        }

        return self::success($title, 'Files uploaded to S3 and import jobs queued. Check progress below.', $redirect);
    }

    private function recentUploads(string $type)
    {
        return DataMigrationUpload::where('type', $type)
            ->latest()
            ->limit(10)
            ->get();
    }

    private function sampleColumns(): array
    {
        return [
            'rates' => ['unique_id', 'product_name', 'product_link', 'rate_type', 'period', 'amount', 'currency'],
            'individuals' => ['email', 'name', 'password', 'product', 'product_link', 'rate_type', 'amount_paid', 'currency', 'startdate', 'enddate', 'payment_channel', 'rate'],
            'organizations' => ['email', 'name', 'password', 'product', 'subtype', 'startdate', 'enddate', 'accounts', 'payment_channel'],
            'corporate_users' => ['organization', 'name', 'email', 'password', 'product', 'start_date', 'end_date'],
        ];
    }

    private function sampleRow(string $type): array
    {
        return match ($type) {
            'rates' => ['NATION-KE', 'Nation Digital', 'https://example.com', 'Monthly', 30, 1000, 'KES'],
            'individuals' => ['customer@example.com', 'Jane Customer', 'Nation.1234', 'Nation Digital', 'https://example.com', 'Monthly', 1000, 'KES', 45858, 45888, 'import', 1000],
            'organizations' => ['admin@example.com', 'Example Corporate', 'Nation.1234', 'Nation Digital', 'Monthly', 45858, 45888, 25, 'LPO'],
            'corporate_users' => ['Example Corporate', 'John Staff', 'staff@example.com', 'Nation.1234', 'Nation Digital', 45858, 45888],
            default => [],
        };

    }

    private function buildSampleWorkbook(string $type): string
    {
        $path = tempnam(sys_get_temp_dir(), 'migration-sample-').'.xlsx';
        $zip = new ZipArchive;

        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->relationshipsXml());
        $zip->addFromString('docProps/app.xml', $this->appPropertiesXml());
        $zip->addFromString('docProps/core.xml', $this->corePropertiesXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml([
            $this->sampleColumns()[$type],
            $this->sampleRow($type),
        ]));
        $zip->close();

        return $path;
    }

    private function worksheetXml(array $rows): string
    {
        $sheetRows = collect($rows)
            ->map(function (array $row, int $rowIndex): string {
                $cells = collect($row)
                    ->map(fn ($value, int $columnIndex): string => $this->cellXml($columnIndex, $rowIndex + 1, (string) $value))
                    ->implode('');

                return '<row r="'.($rowIndex + 1).'">'.$cells.'</row>';
            })
            ->implode('');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>';
    }

    private function cellXml(int $columnIndex, int $rowIndex, string $value): string
    {
        $cell = $this->columnName($columnIndex + 1).$rowIndex;

        return '<c r="'.$cell.'" t="inlineStr"><is><t>'.e($value).'</t></is></c>';
    }

    private function columnName(int $column): string
    {
        $name = '';

        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $name = chr(65 + $remainder).$name;
            $column = intdiv($column - $remainder, 26);
        }

        return $name;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>';
    }

    private function relationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sample" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>';
    }

    private function appPropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"><Application>Subscription Management</Application></Properties>';
    }

    private function corePropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/"><dc:title>Data migration sample</dc:title><dc:creator>Subscription Management</dc:creator><dcterms:created xsi:type="dcterms:W3CDTF" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'.now()->toAtomString().'</dcterms:created></cp:coreProperties>';
    }
}
