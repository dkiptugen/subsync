<?php

    namespace App\Core\Plugins;


    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Facades\Schema;
    use InvalidArgumentException;
    use PharData;
    use RuntimeException;
    use Spatie\Permission\Models\Permission;
    use Spatie\Permission\Models\Role;
    use Throwable;
    use ZipArchive;

    class PluginManager
        {
            protected string $pluginPath = 'plugins';

            public function all()
            : array
                {
                    $plugins = [];

                    foreach (File::directories(base_path($this->pluginPath)) as $dirPath)
                        {
                            $jsonPath = $dirPath . '/plugins.json';
                            if (!File::exists($jsonPath))
                                {
                                    continue;
                                }

                            $data = $this->decodePluginJson($jsonPath);
                            if ($data === null)
                                {
                                    continue;
                                }

                            $plugins[] = $this->normalizePluginData($data, basename($dirPath));
                        }

                    return $plugins;
                }

            public function allEnabled()
            : array
                {
                    return array_values(array_filter($this->all(), static fn(array $plugin)
                    : bool => ($plugin['enabled'] ?? false) === true));
                }

            public function enabledProviders()
            : array
                {
                    $providers = [];

                    foreach ($this->allEnabled() as $plugin)
                        {
                            $provider = trim((string)($plugin['provider'] ?? ''));

                            if ($provider === '')
                                {
                                    continue;
                                }

                            $providers[] = $provider;
                        }

                    return array_values(array_unique($providers));
                }

            public function installFromUploadedArchive(UploadedFile $archiveFile, bool $enable = true)
            : array
                {
                    $tempPath = storage_path('app/plugins-upload/' . uniqid('plugin_', true));
                    File::ensureDirectoryExists($tempPath);

                    $targetPath = null;
                    $copied     = false;

                    try
                        {
                            $archiveSource = $archiveFile->getRealPath();
                            if (!is_string($archiveSource) || $archiveSource === '')
                                {
                                    throw new RuntimeException('Could not read the uploaded archive file.');
                                }

                            $this->extractUploadedArchive($archiveFile, $archiveSource, $tempPath);

                            [$sourcePath, $pluginData] = $this->locatePluginSourceFromExtractedArchive($tempPath);

                            $pluginDirectory = $this->normalizePluginName((string)($pluginData['directory'] ?? basename($sourcePath)));
                            $targetPath      = base_path("{$this->pluginPath}/{$pluginDirectory}");

                            if (File::exists($targetPath))
                                {
                                    throw new RuntimeException("Plugin [{$pluginDirectory}] already exists.");
                                }

                            if (!File::copyDirectory($sourcePath, $targetPath))
                                {
                                    throw new RuntimeException("Could not copy plugins files for [{$pluginDirectory}].");
                                }

                            $copied = true;

                            $manifestPath = $targetPath . '/plugins.json';
                            $manifest     = $this->decodePluginJson($manifestPath);
                            if ($manifest === null)
                                {
                                    throw new RuntimeException("Plugin [{$pluginDirectory}] has an invalid plugins.json.");
                                }

                            $manifest['enabled'] = false;
                            $this->savePluginJson($pluginDirectory, $manifest);

                            if ($enable)
                                {
                                    $this->install($pluginDirectory);
                                }

                            return $this->getPlugin($pluginDirectory);
                        }
                    catch (Throwable $e)
                        {
                            if ($copied && $targetPath !== null && File::isDirectory($targetPath))
                                {
                                    File::deleteDirectory($targetPath);
                                }

                            if ($e instanceof RuntimeException || $e instanceof InvalidArgumentException)
                                {
                                    throw $e;
                                }

                            throw new RuntimeException($e->getMessage(), 0, $e);
                        }
                    finally
                        {
                            if (File::isDirectory($tempPath))
                                {
                                    File::deleteDirectory($tempPath);
                                }
                        }
                }

            public function install(string $pluginName)
            : void
                {
                    $plugin = $this->getPlugin($pluginName);

                    if (($plugin['enabled'] ?? false) === true)
                        {
                            return;
                        }

                    $installerClass = $plugin['install'] ?? null;

                    // Run installer first; only mark enabled if installation succeeds.
                    if (is_string($installerClass) && $installerClass !== '')
                        {
                            if (!class_exists($installerClass))
                                {
                                    throw new RuntimeException("Installer class [{$installerClass}] not found for plugins [{$pluginName}].");
                                }

                            $installer = app($installerClass);
                            if (!method_exists($installer, 'run'))
                                {
                                    throw new RuntimeException("Installer class [{$installerClass}] must implement run().");
                                }

                            $installer->run();
                        }

                    $this->runPluginMigrations($plugin);

                    $this->enable($pluginName);
                }

            public function enable(string $pluginName)
            : void
                {
                    $plugin            = $this->getPlugin($pluginName);
                    $plugin['enabled'] = true;

                    $this->savePluginJson($pluginName, $plugin);
                    $this->syncPluginPermissions($plugin);
                }

            public function disable(string $pluginName)
            : void
                {
                    $plugin            = $this->getPlugin($pluginName);
                    $plugin['enabled'] = false;

                    $this->savePluginJson($pluginName, $plugin);
                }

            public function isLicensed(array $plugin)
            : bool
                {
                    $requiresLicense = (bool)($plugin['requires_license'] ?? $plugin['license_required'] ?? false);
                    if (!$requiresLicense)
                        {
                            return true;
                        }

                    if (!Schema::hasTable('licenses'))
                        {
                            return false;
                        }

                    $pluginName = (string)($plugin['name'] ?? $plugin['directory'] ?? '');
                    if ($pluginName === '')
                        {
                            return false;
                        }

                    try
                        {
                            $license = License::query()
                                              ->where('plugin_name', $pluginName)
                                              ->where('status', 'active')
                                              ->first();
                        }
                    catch (Throwable)
                        {
                            return false;
                        }

                    return $license !== null && $license->expires_at !== null && $license->expires_at->isFuture();
                }

            protected function getPlugin(string $pluginName)
            : array
                {
                    $pluginName = $this->normalizePluginName($pluginName);
                    $jsonPath   = base_path("{$this->pluginPath}/{$pluginName}/plugins.json");

                    if (!File::exists($jsonPath))
                        {
                            throw new InvalidArgumentException("Plugin [{$pluginName}] does not exist.");
                        }

                    $plugin = $this->decodePluginJson($jsonPath);
                    if ($plugin === null)
                        {
                            throw new RuntimeException("Plugin [{$pluginName}] has an invalid plugins.json.");
                        }

                    return $this->normalizePluginData($plugin, $pluginName);
                }

            protected function savePluginJson(string $pluginName, array $data)
            : void
                {
                    $pluginName = $this->normalizePluginName($pluginName);
                    $path       = base_path("{$this->pluginPath}/{$pluginName}/plugins.json");

                    unset($data['directory']);
                    File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
                }

            protected function normalizePluginName(string $pluginName)
            : string
                {
                    $pluginName = trim($pluginName);
                    if (!preg_match('/^[A-Za-z0-9._-]+$/', $pluginName))
                        {
                            throw new InvalidArgumentException("Invalid plugins name [{$pluginName}].");
                        }

                    return $pluginName;
                }

            protected function decodePluginJson(string $path)
            : ?array
                {
                    try
                        {
                            $data = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

                            return is_array($data) ? $data : null;
                        }
                    catch (Throwable)
                        {
                            return null;
                        }
                }

            protected function normalizePluginData(array $data, string $directory)
            : array
                {
                    $data['directory'] = $directory;
                    $data['name']      = (string)($data['name'] ?? $directory);
                    $data['enabled']   = (bool)($data['enabled'] ?? false);
                    $data['provider']  = (string)($data['provider'] ?? '');

                    return $data;
                }

            protected function locatePluginSourceFromExtractedArchive(string $extractPath)
            : array
                {
                    $candidates   = [];
                    $rootManifest = $extractPath . '/plugins.json';

                    if (File::exists($rootManifest))
                        {
                            $candidates[] = $rootManifest;
                        }

                    foreach (File::directories($extractPath) as $dir)
                        {
                            $manifest = $dir . '/plugins.json';
                            if (File::exists($manifest))
                                {
                                    $candidates[] = $manifest;
                                }
                        }

                    if ($candidates === [])
                        {
                            $recursive = [];
                            foreach (File::allFiles($extractPath) as $file)
                                {
                                    if ($file->getFilename() === 'plugins.json')
                                        {
                                            $recursive[] = $file->getPathname();
                                        }
                                }
                            $candidates = $recursive;
                        }

                    if ($candidates === [])
                        {
                            throw new RuntimeException('Uploaded archive does not contain plugins.json.');
                        }

                    if (count($candidates) > 1)
                        {
                            throw new RuntimeException('Uploaded archive contains multiple plugins.json files. Keep one plugin package per archive.');
                        }

                    $manifestPath = $candidates[0];
                    $pluginData   = $this->decodePluginJson($manifestPath);

                    if ($pluginData === null)
                        {
                            throw new RuntimeException('Uploaded plugins.json is invalid.');
                        }

                    return [dirname($manifestPath), $pluginData];
                }

            protected function extractUploadedArchive(UploadedFile $archiveFile, string $sourcePath, string $destination)
            : void
                {
                    $extension = strtolower((string)$archiveFile->getClientOriginalExtension());

                    if ($extension === 'zip')
                        {
                            $this->extractZipArchive($sourcePath, $destination);

                            return;
                        }

                    if ($extension === 'tar')
                        {
                            $this->extractTarArchive($sourcePath, $destination);

                            return;
                        }

                    throw new RuntimeException('Unsupported archive format. Please upload a ZIP or TAR file.');
                }

            protected function extractZipArchive(string $sourcePath, string $destination)
            : void
                {
                    $zip    = new ZipArchive;
                    $status = $zip->open($sourcePath);

                    if ($status !== true)
                        {
                            throw new RuntimeException('Uploaded file is not a valid ZIP archive.');
                        }

                    try
                        {
                            $this->safeExtractZip($zip, $destination);
                        }
                    finally
                        {
                            $zip->close();
                        }
                }

            protected function extractTarArchive(string $sourcePath, string $destination)
            : void
                {
                    try
                        {
                            $archive = new PharData($sourcePath);
                            $archive->extractTo($destination, null, true);
                        }
                    catch (Throwable $e)
                        {
                            throw new RuntimeException('Uploaded file is not a valid TAR archive.', 0, $e);
                        }

                    $this->assertArchiveExtractionIsSafe($destination);
                }

            protected function safeExtractZip(ZipArchive $zip, string $destination)
            : void
                {
                    for ($i = 0; $i < $zip->numFiles; $i++)
                        {
                            $name = $zip->getNameIndex($i);
                            if (!is_string($name) || $name === '')
                                {
                                    continue;
                                }

                            $entry = str_replace('\\', '/', $name);
                            $entry = ltrim($entry, '/');
                            if ($entry === '' || str_contains($entry, "\0"))
                                {
                                    continue;
                                }

                            if (
                                str_contains($entry, '../') ||
                                str_starts_with($entry, '..') ||
                                str_contains($entry, ':')
                            )
                                {
                                    throw new RuntimeException('ZIP contains an unsafe path.');
                                }

                            $target = $destination . '/' . $entry;

                            if (str_ends_with($entry, '/'))
                                {
                                    File::ensureDirectoryExists($target);

                                    continue;
                                }

                            File::ensureDirectoryExists(dirname($target));

                            $stream = $zip->getStream($name);
                            if ($stream === false)
                                {
                                    throw new RuntimeException('Could not read ZIP entry.');
                                }

                            $contents = stream_get_contents($stream);
                            fclose($stream);

                            if ($contents === false)
                                {
                                    throw new RuntimeException('Could not extract ZIP entry.');
                                }

                            File::put($target, $contents);
                        }
                }

            protected function assertArchiveExtractionIsSafe(string $destination)
            : void
                {
                    $normalizedDestination = rtrim(str_replace('\\', '/', realpath($destination) ?: $destination), '/');

                    foreach (File::allFiles($destination) as $file)
                        {
                            $realPath = str_replace('\\', '/', $file->getRealPath() ?: $file->getPathname());

                            if (!str_starts_with($realPath, $normalizedDestination . '/'))
                                {
                                    throw new RuntimeException('Archive contains an unsafe path.');
                                }
                        }
                }

            protected function syncPluginPermissions(array $plugin)
            : void
                {
                    if (!class_exists(Permission::class) || !class_exists(Role::class))
                        {
                            return;
                        }

                    if (!Schema::hasTable('permissions') || !Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions'))
                        {
                            return;
                        }

                    $permissions = $this->collectDeclaredPermissions($plugin);
                    if ($permissions === [])
                        {
                            return;
                        }

                    $guard    = (string)($plugin['guard_name'] ?? 'web');
                    $roleName = (string)($plugin['super_admin_role'] ?? 'super-admin');

                    try
                        {
                            $role = Role::findOrCreate($roleName, $guard);
                        }
                    catch (Throwable)
                        {
                            return;
                        }

                    foreach ($permissions as $permissionName)
                        {
                            try
                                {
                                    $permission = Permission::findOrCreate($permissionName, $guard);
                                    $role->givePermissionTo($permission);
                                }
                            catch (Throwable)
                                {
                                    // Keep install/enable resilient; ignore single permission failures.
                                    continue;
                                }
                        }
                }

            protected function collectDeclaredPermissions(array $plugin)
            : array
                {
                    $permissions = [];

                    foreach (($plugin['permissions'] ?? []) as $permission)
                        {
                            if (is_string($permission) && $permission !== '')
                                {
                                    $permissions[] = $permission;
                                }
                        }

                    foreach (($plugin['menu']['children'] ?? []) as $child)
                        {
                            $this->extractMenuPermissions($permissions, $child);
                        }

                    if (isset($plugin['menu']))
                        {
                            $this->extractMenuPermissions($permissions, $plugin['menu']);
                        }

                    foreach (($plugin['menus'] ?? []) as $menu)
                        {
                            $this->extractMenuPermissions($permissions, $menu);
                        }

                    return array_values(array_unique($permissions));
                }

            protected function extractMenuPermissions(array &$permissions, mixed $item)
            : void
                {
                    if (!is_array($item))
                        {
                            return;
                        }

                    $can = $item['can'] ?? null;
                    if (is_string($can) && $can !== '')
                        {
                            $permissions[] = $can;
                        }

                    foreach (($item['children'] ?? []) as $child)
                        {
                            $this->extractMenuPermissions($permissions, $child);
                        }
                }

            protected function runPluginMigrations(array $plugin)
            : void
                {
                    foreach ($this->migrationPathsFor($plugin) as $path)
                        {
                            Artisan::call('migrate', [
                                '--path'     => $this->relativePath($path),
                                '--realpath' => true,
                                '--force'    => true,
                            ]);
                        }
                }

            protected function migrationPathsFor(array $plugin)
            : array
                {
                    $pluginPath = $this->pluginBasePath($plugin);
                    $paths      = [];

                    foreach ((array)($plugin['migration_paths'] ?? []) as $relativePath)
                        {
                            if (!is_string($relativePath) || trim($relativePath) === '')
                                {
                                    continue;
                                }

                            $paths[] = $pluginPath . '/' . trim($relativePath, '/');
                        }

                    $paths[] = $pluginPath . '/database/migrations';
                    $paths[] = $pluginPath . '/migrations';

                    return array_values(array_unique(array_filter($paths, static fn(string $path)
                    : bool => File::isDirectory($path))));
                }

            protected function pluginBasePath(array $plugin)
            : string
                {
                    return base_path($this->pluginPath . '/' . trim((string)$plugin['directory'], '/'));
                }

            protected function relativePath(string $path)
            : string
                {
                    $basePath = rtrim(base_path(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

                    if (str_starts_with($path, $basePath))
                        {
                            return substr($path, strlen($basePath));
                        }

                    return $path;
                }
        }
