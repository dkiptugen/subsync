<?php

    namespace App\Core\Plugins;

    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Str;
    use InvalidArgumentException;
    use RuntimeException;
    use Throwable;
    use ZipArchive;

    class GitHubPluginUpdater
        {
            public function __construct(
                protected PluginManager       $pluginManager,
                protected GitHubPluginWatcher $watcher
            )
                {
                }

            public function update(string $pluginName, bool $force = false, bool $dryRun = false)
            : array
                {
                    $plugin = $this->findPlugin($pluginName);

                    return $this->updatePlugin($plugin, $force, $dryRun);
                }

            public function updateAll(bool $force = false, bool $dryRun = false, bool $enabledOnly = true)
            : array
                {
                    $plugins = $enabledOnly ? $this->pluginManager->allEnabled() : $this->pluginManager->all();
                    $results = [];

                    foreach ($plugins as $plugin)
                        {
                            if (!$this->watcher->supports($plugin))
                                {
                                    continue;
                                }

                            try
                                {
                                    $results[] = $this->updatePlugin($plugin, $force, $dryRun);
                                }
                            catch (Throwable $e)
                                {
                                    $results[] = [
                                        'plugins' => (string)($plugin['directory'] ?? $plugin['name'] ?? 'unknown'),
                                        'status'  => 'error',
                                        'message' => $e->getMessage(),
                                    ];
                                }
                        }

                    return $results;
                }

            protected function updatePlugin(array $plugin, bool $force, bool $dryRun)
            : array
                {
                    $check = $this->watcher->check($plugin);
                    if ($check === null)
                        {
                            throw new InvalidArgumentException('This plugins is not configured for GitHub updates.');
                        }

                    $pluginDirectory = (string)($plugin['directory'] ?? $plugin['name'] ?? 'unknown');

                    if (isset($check['error']))
                        {
                            return [
                                'plugins'         => $pluginDirectory,
                                'status'          => 'error',
                                'message'         => (string)$check['error'],
                                'current_version' => $check['current_version'],
                                'remote_version'  => $check['remote_version'],
                            ];
                        }

                    if (!$force && !($check['has_update'] ?? false))
                        {
                            return [
                                'plugins'         => $pluginDirectory,
                                'status'          => 'skipped',
                                'message'         => 'Already up to date.',
                                'current_version' => $check['current_version'],
                                'remote_version'  => $check['remote_version'],
                            ];
                        }

                    if ($dryRun)
                        {
                            return [
                                'plugins'         => $pluginDirectory,
                                'status'          => 'pending',
                                'message'         => 'Update available (dry run).',
                                'current_version' => $check['current_version'],
                                'remote_version'  => $check['remote_version'],
                            ];
                        }

                    $config = $this->watcher->configurationFor($plugin);
                    if ($config === null)
                        {
                            throw new RuntimeException("Plugin [{$pluginDirectory}] has no valid GitHub source configuration.");
                        }

                    $zipPath     = null;
                    $extractPath = null;

                    try
                        {
                            $zipPath     = $this->downloadArchive((string)$check['archive_url'], $config['token']);
                            $extractPath = $this->extractArchive($zipPath);
                            $sourcePath  = $this->resolvePluginSourcePath($extractPath, (string)$config['path']);
                            $backupPath  = $this->replacePlugin($plugin, $sourcePath);
                        }
                    finally
                        {
                            if ($zipPath !== null && File::exists($zipPath))
                                {
                                    File::delete($zipPath);
                                }

                            if ($extractPath !== null && File::isDirectory($extractPath))
                                {
                                    File::deleteDirectory($extractPath);
                                }
                        }

                    return [
                        'plugins'         => $pluginDirectory,
                        'status'          => 'updated',
                        'message'         => 'Plugin updated successfully.',
                        'current_version' => $check['current_version'],
                        'remote_version'  => $check['remote_version'],
                        'backup_path'     => $backupPath,
                    ];
                }

            protected function findPlugin(string $pluginName)
            : array
                {
                    $needle = trim($pluginName);
                    if ($needle === '')
                        {
                            throw new InvalidArgumentException('Plugin name is required.');
                        }

                    foreach ($this->pluginManager->all() as $plugin)
                        {
                            $directory = (string)($plugin['directory'] ?? '');
                            $name      = (string)($plugin['name'] ?? '');

                            if ($directory === $needle || $name === $needle)
                                {
                                    return $plugin;
                                }
                        }

                    throw new InvalidArgumentException("Plugin [{$needle}] does not exist.");
                }

            protected function downloadArchive(string $archiveUrl, ?string $token)
            : string
                {
                    $headers = [
                        'Accept'               => 'application/vnd.github+json',
                        'X-GitHub-Api-Version' => '2022-11-28',
                        'User-Agent'           => (string)config('app.name', 'Laravel') . ' Plugin Updater',
                    ];

                    if ($token !== null)
                        {
                            $headers['Authorization'] = "Bearer {$token}";
                        }

                    $response = Http::withHeaders($headers)
                                    ->connectTimeout(10)
                                    ->timeout(120)
                                    ->get($archiveUrl);

                    if (!$response->successful())
                        {
                            throw new RuntimeException("Failed to download plugins archive (HTTP {$response->status()}).");
                        }

                    $directory = storage_path('app/plugins-updates');
                    File::ensureDirectoryExists($directory);

                    $zipPath = $directory . '/' . Str::uuid() . '.zip';
                    File::put($zipPath, $response->body());

                    return $zipPath;
                }

            protected function extractArchive(string $zipPath)
            : string
                {
                    $extractPath = storage_path('app/plugins-updates/tmp/' . Str::uuid());
                    File::ensureDirectoryExists($extractPath);

                    $zip    = new ZipArchive;
                    $status = $zip->open($zipPath);
                    if ($status !== true)
                        {
                            throw new RuntimeException('Downloaded archive is not a valid ZIP file.');
                        }

                    $ok = $zip->extractTo($extractPath);
                    $zip->close();

                    if ($ok !== true)
                        {
                            throw new RuntimeException('Could not extract plugins archive.');
                        }

                    return $extractPath;
                }

            protected function resolvePluginSourcePath(string $extractPath, string $pluginSubPath)
            : string
                {
                    $roots = File::directories($extractPath);
                    if ($roots === [])
                        {
                            throw new RuntimeException('Plugin archive does not contain a root directory.');
                        }

                    $root      = $roots[0];
                    $candidate = $pluginSubPath !== ''
                        ? rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($pluginSubPath, DIRECTORY_SEPARATOR)
                        : $root;

                    $realCandidate = realpath($candidate);
                    $realExtract   = realpath($extractPath);

                    if ($realCandidate === false || $realExtract === false)
                        {
                            throw new RuntimeException('Plugin archive contains invalid paths.');
                        }

                    if (!str_starts_with($realCandidate, rtrim($realExtract, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR))
                        {
                            throw new RuntimeException('Plugin source path is outside the archive root.');
                        }

                    if (!File::exists($realCandidate . '/plugins.json'))
                        {
                            throw new RuntimeException('plugins.json was not found in the plugins source path.');
                        }

                    return $realCandidate;
                }

            protected function replacePlugin(array $plugin, string $sourcePath)
            : string
                {
                    $directory = (string)($plugin['directory'] ?? '');
                    if ($directory === '')
                        {
                            throw new RuntimeException('Plugin directory is missing.');
                        }

                    $targetPath = base_path("plugins/{$directory}");
                    $backupPath = storage_path('app/plugins-backups/' . $directory . '-' . now()->format('YmdHis'));
                    $enabled    = (bool)($plugin['enabled'] ?? false);

                    if (File::isDirectory($targetPath))
                        {
                            File::ensureDirectoryExists(dirname($backupPath));
                            if (!File::copyDirectory($targetPath, $backupPath))
                                {
                                    throw new RuntimeException("Could not create backup for plugins [{$directory}].");
                                }
                        }

                    try
                        {
                            if (File::isDirectory($targetPath))
                                {
                                    File::deleteDirectory($targetPath);
                                }

                            if (!File::copyDirectory($sourcePath, $targetPath))
                                {
                                    throw new RuntimeException("Could not copy updated files for plugins [{$directory}].");
                                }

                            $manifestPath = $targetPath . '/plugins.json';
                            $manifest     = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
                            if (!is_array($manifest))
                                {
                                    throw new RuntimeException("Updated plugins [{$directory}] has an invalid plugins.json.");
                                }

                            $manifest['enabled'] = $enabled;
                            File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

                            if ($enabled)
                                {
                                    $this->pluginManager->enable($directory);
                                }
                        }
                    catch (Throwable $e)
                        {
                            $this->restoreBackup($targetPath, $backupPath);
                            throw new RuntimeException("Update failed for plugins [{$directory}] and was rolled back: {$e->getMessage()}", 0, $e);
                        }

                    return $backupPath;
                }

            protected function restoreBackup(string $targetPath, string $backupPath)
            : void
                {
                    if (!File::isDirectory($backupPath))
                        {
                            return;
                        }

                    if (File::isDirectory($targetPath))
                        {
                            File::deleteDirectory($targetPath);
                        }

                    File::copyDirectory($backupPath, $targetPath);
                }
        }
