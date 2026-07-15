<?php

namespace App\Http\Controllers;

use App\Imports\CorporateUsersImport;
use App\Imports\IndividualImport;
use App\Imports\OrganizationImport;
use App\Imports\RateImport;
use App\Traits\Meta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class DataMigController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    public function rate_form()
    {

        return view('modules.migration.rates', $this->data);
    }

    public function rate(Request $request)
    {
        set_time_limit(0);
        if (! $request->hasFile('files') || ! is_array($request->file('files'))) {
            return self::failed('Rates import', 'no files were uploaded.', route('product.rate.index', 0));
        }

        $files = $request->file('files');
        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            if (! in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return self::failed('Rates import', 'The file must be an Excel file.', route('product.rate.index', 0));
            }
            try {
                Excel::import(new RateImport(Auth::user()->id), $file);

                return self::success('Rates import', 'import successful', route('product.rate.index', 0));
            } catch (ValidationException $e) {

                return self::failed('Rates import', $e->failures(), route('product.rate.index', 0));

            } catch (\Exception $e) {

                return self::failed('Rates import', $e->getMessage(), route('product.rate.index', 0));
            }

        }

        // return self::success('Rates import', 'import successful', route('product.rate.index',0));
    }

    public function individual_form()
    {

        return view('modules.migration.individuals', $this->data);
    }

    public function individual(Request $request)
    {
        set_time_limit(0);
        if (! $request->hasFile('files') || ! is_array($request->file('files'))) {
            return self::failed('Individual accounts import', 'no files were uploaded.', route('product.subscriber.index', 0));
        }
        $files = $request->file('files');

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            if (! in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return self::failed('Individual accounts import', 'The file must be an Excel file.', route('product.subscriber.index', 0));
            }
            try {
                Excel::import(new IndividualImport(Auth::user()->id), $file);

                return self::success('Individual Accounts import', 'import successful', route('product.subscriber.index', 0));
            } catch (ValidationException $e) {

                return self::failed('Individual Accounts import', $e->failures(), route('product.subscriber.index', 0));

            } catch (\Exception $e) {

                return self::failed('Individual Accounts import', $e->getMessage(), route('product.subscriber.index', 0));

            }

        }

    }

    public function organization_form()
    {

        return view('modules.migration.organizations', $this->data);
    }

    public function organization(Request $request)
    {
        set_time_limit(0);
        if (! $request->hasFile('files') || ! is_array($request->file('files'))) {
            return self::failed('Organizations import', 'no files were uploaded.', route('organization.index'));
        }
        $files = $request->file('files');

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            if (! in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return self::failed('Organizations import', 'The file must be an Excel file.', route('organization.index'));
            }
            try {
                Excel::import(new OrganizationImport(Auth::user()->id), $file);

                return self::success('Organizations import', 'import successful', route('organization.index'));
            } catch (ValidationException $e) {
                Log::error($e->failures());

                return self::failed('Organizations import', $e->failures(), route('organization.index'));

            } catch (\Exception $e) {
                Log::error($e->getMessage());

                return self::failed('Corporate Users import', $e->getMessage(), route('organization.index'));
            }

        }

    }

    public function corporate_users_form()
    {

        return view('modules.migration.corporate_users', $this->data);
    }

    public function corporate_users(Request $request)
    {
        set_time_limit(0);
        if (! $request->hasFile('files') || ! is_array($request->file('files'))) {
            return self::failed('Corporate Users import', 'no files were uploaded.', route('organization.index'));
        }
        $files = $request->file('files');

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            if (! in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return self::failed('Corporate Users import', 'The file must be an Excel file.', route('organization.index'));
            }
            try {
                Excel::import(new CorporateUsersImport(Auth::user()->id), $file);

                return self::success('Corporate Users import', 'import successful', route('organization.index'));
            } catch (ValidationException $e) {

                return self::failed('Corporate Users import', $e->failures(), route('organization.index'));

            } catch (\Exception $e) {
                return self::failed('Corporate Users import', $e->getMessage(), route('organization.index'));
            }

        }

    }
}
