<?php

namespace App\Console\Commands\Utilities;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateDataTable extends Command
    {
    /**
     * Command signature
     */
        protected $signature = 'make:datatable {name} {model} {--search=*}';

    /**
     * Command description
     */
        protected $description = 'Create a new DataTable class';

    /**
     * Execute the command
     */
        public function handle()
            {
                $name = $this->argument('name');
                $model = $this->argument('model');
                $searchColumns = $this->option('search');

                $directory = app_path('Http/Datatables');
                $path = $directory . "/{$name}.php";

                // Ensure directory exists
                if (!File::isDirectory($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }

                // Prevent overwriting
                if (File::exists($path)) {
                    $this->error("{$name} already exists!");
                    return;
                }

                $stub = $this->getStub($name, $model, $searchColumns);

                File::put($path, $stub);

                $this->info("{$name} created successfully.");
            }

    /**
     * Generate class stub
     */
        protected function getStub($name, $model, $searchColumns)
            {
                $modelLower = strtolower($model);

                $searchConditions = collect($searchColumns)
                    ->map(function ($column, $index) {
                        if ($index === 0) {
                            return "\$q->where('{$column}', 'LIKE', \"%{\$search}%\")";
                        }

                        return "->orWhere('{$column}', 'LIKE', \"%{\$search}%\")";
                    })
                    ->implode("\n                    ");

                return <<<PHP
<?php

namespace App\Http\Datatables;

use App\Models\\{$model};


class {$name}
{


    public \$columns = [];

    /**
     * Generate DataTable data
     */
    public function data(\$request)
    {
        \$columns = \$this->columns;

        \$query = {$model}::query();

        \$totalData = \$query->count();
        \$totalFiltered = \$totalData;

        \$limit = \$request->input('length');
        \$start = \$request->input('start');

        \$order = \$columns[\$request->input('order.0.column')];
        \$dir = \$request->input('order.0.dir');

        if (!empty(\$request->input('search.value')))
        {
            \$search = \$request->input('search.value');

            \$query->where(function (\$q) use (\$search)
                            {
                    {$searchConditions};
                    });


            \$totalFiltered = (clone \$query)->count();
        }

        \$items = \$query
            ->offset(\$start)
            ->limit(\$limit)
            ->orderBy(\$order, \$dir)
            ->get();

        \$data = [];

        if (!empty(\$items))
        {
            \$pos = \$start + 1;

            foreach (\$items as \$item)
            {
                \$btn = \$this->button(\$item, \$request);

                \$nestedData['id'] = \$pos;
                \$nestedData['name'] = \$item->name ?? '';
                \$nestedData['email'] = \$item->email ?? '';
                \$nestedData['status'] = \$item->status ?? '';
                \$nestedData['action'] = \$btn;

                \$data[] = \$nestedData;

                \$pos++;
            }
        }

        return [
            'draw' => (int) \$request->input('draw'),
            'recordsTotal' => \$totalData,
            'recordsFiltered' => \$totalFiltered,
            'data' => \$data
        ];
    }

    /**
     * Action buttons
     */
    private function button(\$item, \$request)
    {
        \$button = '';

        if (\$request->user()->can('edit_{$modelLower}'))
        {
            \$button .= '<a class="text text-dark" href="' . route('{$modelLower}.edit', \$item->id) . '" title="Edit {$model}">
                <i class="fas fa-edit"></i> Edit
            </a>';
        }

        if (\$request->user()->can('destroy_{$modelLower}'))
        {
            \$button .= '<form id="delete-form-' . \$item->id . '" action="' . route('{$modelLower}.destroy', \$item->id) . '" method="POST" class="d-inline">
                <input type="hidden" name="_token" value="' . csrf_token() . '" />
                <input type="hidden" name="_method" value="DELETE" />
                <button type="submit" class="btn btn-link text-dark">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>';
        }

        return '<div class="d-flex align-items-center gap-2">' . \$button . '</div>';
    }
}
PHP;
            }
    }
