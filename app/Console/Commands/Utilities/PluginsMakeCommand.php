<?php

namespace App\Console\Commands\Utilities;

use App\Core\Plugins\PluginManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonException;

class PluginsMakeCommand extends Command
    {
        protected $signature = 'plugins:make
        {name : The plugin name}
        {--title= : The human-friendly plugin title}
        {--description= : The plugin description}
        {--icon=fas fa-puzzle-piece : The sidebar icon class}
        {--install : Run the plugin install flow after scaffolding}
        {--force : Overwrite an existing plugin directory}';

        protected $description = 'Create a new plugin scaffold';

        public function handle(PluginManager $pluginManager): int
            {
                $studlyName = Str::studly((string) $this->argument('name'));

                $title = $this->option('title');
                $pluginTitle = is_string($title) && $title !== ''
                    ? $title
                    : Str::headline($studlyName);

                $pluginDescription = (string) ($this->option('description') ?: "{$pluginTitle} plugin");
                $icon = (string) $this->option('icon');

                $pluginPath = base_path("plugins/{$studlyName}");

                if (File::exists($pluginPath)) {
                    if (!$this->option('force')) {
                        $this->error("Plugin [{$studlyName}] already exists.");
                        return self::FAILURE;
                    }

                    File::deleteDirectory($pluginPath);
                }

                File::ensureDirectoryExists($pluginPath);

                $this->writePluginFiles(
                    pluginName: $studlyName,
                    pluginTitle: $pluginTitle,
                    pluginDescription: $pluginDescription,
                    icon: $icon,
                    pluginPath: $pluginPath,
                );

                $this->components->info("Plugin [{$studlyName}] scaffold created.");
                $this->line("Path: {$pluginPath}");

                if ((bool) $this->option('install')) {
                    $pluginManager->install($studlyName);
                    $this->components->info("Plugin [{$studlyName}] installed and enabled.");
                } else {
                    $this->line("Use `--install` or enable it from the plugins screen when you're ready.");
                }

                return self::SUCCESS;
            }

        protected function writePluginFiles(
            string $pluginName,
            string $pluginTitle,
            string $pluginDescription,
            string $icon,
            string $pluginPath,
        ): void {
            $routeSlug = Str::kebab($pluginName);
            $configKey = Str::snake($pluginName);
            $providerClass = "{$pluginName}ServiceProvider";
            $controllerClass = "{$pluginName}Controller";

            $migrationFile = now()->format('Y_m_d_His') . '_create_' . $configKey . '_plugin_settings_table.php';
            $tableName = "{$configKey}_plugin_settings";

            $directories = [
                "{$pluginPath}/Http/Controllers",
                "{$pluginPath}/Providers",
                "{$pluginPath}/views",
                "{$pluginPath}/config",
                "{$pluginPath}/database/migrations",
            ];

            foreach ($directories as $directory) {
                File::ensureDirectoryExists($directory);
            }

            File::put(
                "{$pluginPath}/plugins.json",
                $this->buildManifest(
                    pluginName: $pluginName,
                    pluginTitle: $pluginTitle,
                    pluginDescription: $pluginDescription,
                    icon: $icon,
                    routeSlug: $routeSlug,
                )
            );

            File::put(
                "{$pluginPath}/Providers/{$providerClass}.php",
                $this->buildProvider($pluginName, $providerClass, $controllerClass, $routeSlug)
            );

            File::put(
                "{$pluginPath}/Http/Controllers/{$controllerClass}.php",
                $this->buildController($pluginName, $controllerClass, $pluginTitle)
            );

            File::put(
                "{$pluginPath}/views/index.blade.php",
                $this->buildView($pluginTitle, $configKey)
            );

            File::put(
                "{$pluginPath}/config/{$configKey}.php",
                $this->buildConfig($pluginTitle, $pluginDescription, $icon, $routeSlug)
            );

            File::put(
                "{$pluginPath}/database/migrations/{$migrationFile}",
                $this->buildMigration($tableName)
            );
        }

        protected function buildManifest(
            string $pluginName,
            string $pluginTitle,
            string $pluginDescription,
            string $icon,
            string $routeSlug,
        ): string {
            try {
                return json_encode([
                        'name' => $pluginTitle,
                        'description' => $pluginDescription,
                        'enabled' => false,
                        'provider' => "Plugins\\{$pluginName}\\Providers\\{$pluginName}ServiceProvider",
                        'migration_paths' => [
                            'database/migrations',
                        ],
                        'menus' => [
                            [
                                'id' => Str::snake($pluginName),
                                'location' => 'sidebar',
                                'type' => 'link',
                                'title' => $pluginTitle,
                                'icon' => $icon,
                                'route' => "dashboard.{$routeSlug}.index",
                                'order' => 50,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
            } catch (JsonException $exception) {
                throw new \RuntimeException($exception->getMessage(), 0, $exception);
            }
        }

        protected function buildProvider(
            string $pluginName,
            string $providerClass,
            string $controllerClass,
            string $routeSlug,
        ): string {
            return <<<PHP
<?php

namespace Plugins\\{$pluginName}\\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\\{$pluginName}\\Http\\Controllers\\{$controllerClass};

class {$providerClass} extends ServiceProvider
{
    public function boot(): void
    {
        \$this->loadViewsFrom(base_path('plugins/{$pluginName}/views'), '{$pluginName}');
        \$this->loadMigrationsFrom(base_path('plugins/{$pluginName}/database/migrations'));

        Route::middleware(['web', 'auth:web'])
            ->prefix('dashboard/{$routeSlug}')
            ->name('dashboard.{$routeSlug}.')
            ->group(function () {
                Route::get('/', [{$controllerClass}::class, 'index'])->name('index');
            });
    }
}
PHP;
        }

        protected function buildController(string $pluginName, string $controllerClass, string $pluginTitle): string
            {
                return <<<PHP
<?php

namespace Plugins\\{$pluginName}\\Http\\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class {$controllerClass} extends Controller
{
    public function index(): View
    {
        return view('{$pluginName}::index', [
            'title' => '{$pluginTitle}',
        ]);
    }
}
PHP;
            }

        protected function buildView(string $pluginTitle, string $configKey): string
            {
                return <<<BLADE
@extends('includes.body')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-1">{{ \$title }}</h1>
        <div class="text-muted">Plugin scaffold generated for {$pluginTitle}.</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <p class="mb-2">Your plugin is active and ready for customization.</p>
        <p class="mb-0 text-muted">Config key: <code>{$configKey}</code></p>
    </div>
</div>
@endsection
BLADE;
            }

        protected function buildConfig(
            string $pluginTitle,
            string $pluginDescription,
            string $icon,
            string $routeSlug,
        ): string {
            return <<<PHP
<?php

return [
    'name' => '{$pluginTitle}',
    'description' => '{$pluginDescription}',
    'navigation' => [
        'icon' => '{$icon}',
        'route' => 'dashboard.{$routeSlug}.index',
    ],
];
PHP;
        }

        protected function buildMigration(string $tableName): string
            {
                return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table): void {
            \$table->id();
            \$table->string('key')->unique();
            \$table->text('value')->nullable();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
PHP;
            }
    }
