<?php

    namespace App\Core\Plugins;

    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Str;
    use Throwable;

    class GitHubPluginWatcher
        {
            public function __construct(protected PluginManager $pluginManager)
                {
                }

            public function watchAll(bool $enabledOnly = true)
            : array
                {
                    $plugins = $enabledOnly ? $this->pluginManager->allEnabled() : $this->pluginManager->all();
                    $results = [];

                    foreach ($plugins as $plugin)
                        {
                            $result = $this->check($plugin);
                            if ($result !== null)
                                {
                                    $results[] = $result;
                                }
                        }

                    return $results;
                }

            public function supports(array $plugin)
            : bool
                {
                    return $this->configurationFor($plugin) !== null;
                }

            public function check(array $plugin)
            : ?array
                {
                    $config = $this->configurationFor($plugin);
                    if ($config === null)
                        {
                            return null;
                        }

                    $pluginLabel = (string)($plugin['directory'] ?? $plugin['name'] ?? 'unknown');
                    $result      = [
                        'plugins'         => $pluginLabel,
                        'repository'      => $config['repository'],
                        'ref'             => $config['ref'],
                        'current_version' => $this->normalizeVersion($plugin['version'] ?? null),
                        'remote_version'  => null,
                        'remote_sha'      => null,
                        'archive_url'     => null,
                        'has_update'      => false,
                    ];

                    try
                        {
                            $response = $this->githubClient($config['token'])->get(
                                "https://api.github.com/repos/{$config['repository']}/contents/{$config['manifest_path']}",
                                ['ref' => $config['ref']]
                            );

                            if (!$response->successful())
                                {
                                    $result['error'] = $this->formatGitHubError($response->status(), (string)$response->body());

                                    return $result;
                                }

                            $payload = $response->json();
                            $content = $this->decodeManifestContent($payload['content'] ?? '', $payload['encoding'] ?? '');
                            if ($content === null)
                                {
                                    $result['error'] = 'Could not decode remote plugins manifest.';

                                    return $result;
                                }

                            $remoteManifest = json_decode($content, true);
                            if (!is_array($remoteManifest))
                                {
                                    $result['error'] = 'Remote plugins manifest is invalid JSON.';

                                    return $result;
                                }

                            $remoteVersion = $this->normalizeVersion($remoteManifest['version'] ?? null);
                            $remoteSha     = is_string($payload['sha'] ?? null) ? $payload['sha'] : null;
                            $archiveRef    = $remoteSha ?: $config['ref'];

                            $result['remote_version'] = $remoteVersion;
                            $result['remote_sha']     = $remoteSha;
                            $result['archive_url']    = "https://api.github.com/repos/{$config['repository']}/zipball/{$archiveRef}";
                            $result['has_update']     = $this->hasUpdate($result['current_version'], $remoteVersion);
                        }
                    catch (Throwable $e)
                        {
                            $result['error'] = 'GitHub check failed: ' . $e->getMessage();
                        }

                    return $result;
                }

            public function configurationFor(array $plugin)
            : ?array
                {
                    $source = $this->extractGitHubSource($plugin);
                    if ($source === null)
                        {
                            return null;
                        }

                    $repository = trim((string)($source['repository'] ?? ''));
                    if ($repository === '' || !preg_match('/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/', $repository))
                        {
                            return null;
                        }

                    $ref = trim((string)($source['ref'] ?? $source['branch'] ?? 'main'));
                    if ($ref === '')
                        {
                            $ref = 'main';
                        }

                    $manifest = trim((string)($source['manifest'] ?? 'plugins.json'), '/');
                    if ($manifest === '')
                        {
                            $manifest = 'plugins.json';
                        }

                    $basePath     = trim((string)($source['path'] ?? ''), '/');
                    $manifestPath = $basePath !== '' ? "{$basePath}/{$manifest}" : $manifest;

                    $tokenEnv = trim((string)($source['token_env'] ?? 'GITHUB_TOKEN'));
                    $token    = $tokenEnv !== '' ? env($tokenEnv) : null;
                    $token    = is_string($token) && $token !== '' ? $token : null;

                    return [
                        'repository'    => $repository,
                        'ref'           => $ref,
                        'path'          => $basePath,
                        'manifest'      => $manifest,
                        'manifest_path' => $manifestPath,
                        'token_env'     => $tokenEnv,
                        'token'         => $token,
                    ];
                }

            protected function extractGitHubSource(array $plugin)
            : ?array
                {
                    $source = [];

                    if (isset($plugin['github']) && is_array($plugin['github']))
                        {
                            $source = array_merge($source, $plugin['github']);
                        }

                    if (isset($plugin['update']) && is_array($plugin['update']))
                        {
                            if (isset($plugin['update']['github']) && is_array($plugin['update']['github']))
                                {
                                    $source = array_merge($source, $plugin['update']['github']);
                                }

                            if (Str::lower((string)($plugin['update']['provider'] ?? '')) === 'github')
                                {
                                    $source = array_merge($source, $this->extractShallowUpdateFields($plugin['update']));
                                }
                        }

                    if (isset($plugin['source']) && is_array($plugin['source']))
                        {
                            if (isset($plugin['source']['github']) && is_array($plugin['source']['github']))
                                {
                                    $source = array_merge($source, $plugin['source']['github']);
                                }

                            if (Str::lower((string)($plugin['source']['type'] ?? '')) === 'github')
                                {
                                    $source = array_merge($source, $this->extractShallowUpdateFields($plugin['source']));
                                }
                        }

                    return $source === [] ? null : $source;
                }

            protected function extractShallowUpdateFields(array $data)
            : array
                {
                    $fields = [];
                    foreach (['repository', 'ref', 'branch', 'path', 'manifest', 'token_env'] as $field)
                        {
                            if (isset($data[$field]))
                                {
                                    $fields[$field] = $data[$field];
                                }
                        }

                    return $fields;
                }

            protected function normalizeVersion(mixed $value)
            : ?string
                {
                    return is_string($value) && trim($value) !== '' ? trim($value) : null;
                }

            protected function hasUpdate(?string $currentVersion, ?string $remoteVersion)
            : bool
                {
                    if ($remoteVersion === null)
                        {
                            return false;
                        }

                    if ($currentVersion === null)
                        {
                            return true;
                        }

                    if ($remoteVersion === $currentVersion)
                        {
                            return false;
                        }

                    if (preg_match('/\d/', $currentVersion) && preg_match('/\d/', $remoteVersion))
                        {
                            return version_compare($remoteVersion, $currentVersion, '>');
                        }

                    return $remoteVersion !== $currentVersion;
                }

            protected function decodeManifestContent(string $content, string $encoding)
            : ?string
                {
                    if ($content === '')
                        {
                            return null;
                        }

                    if (strtolower($encoding) === 'base64')
                        {
                            return base64_decode(str_replace("\n", '', $content), true) ?: null;
                        }

                    return $content;
                }

            protected function githubClient(?string $token)
                {
                    $headers = [
                        'Accept'               => 'application/vnd.github+json',
                        'X-GitHub-Api-Version' => '2022-11-28',
                        'User-Agent'           => (string)config('app.name', 'Laravel') . ' Plugin Watcher',
                    ];

                    if ($token !== null)
                        {
                            $headers['Authorization'] = "Bearer {$token}";
                        }

                    return Http::withHeaders($headers)
                               ->connectTimeout(10)
                               ->timeout(25);
                }

            protected function formatGitHubError(int $status, string $body)
            : string
                {
                    $summary = trim((string)Str::of($body)->limit(180));

                    return $summary !== ''
                        ? "GitHub API returned {$status}: {$summary}"
                        : "GitHub API returned {$status}.";
                }
        }
