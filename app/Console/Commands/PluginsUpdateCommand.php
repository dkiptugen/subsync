<?php

namespace App\Console\Commands;

use App\Core\Plugins\GitHubPluginUpdater;
use Illuminate\Console\Command;
use Throwable;

class PluginsUpdateCommand extends Command
{
    protected $signature = 'plugins:update
        {plugins? : Plugin directory or plugins name}
        {--all : Update all GitHub-managed plugins}
        {--all-status : Include disabled plugins when using --all}
        {--force : Force update even if versions match}
        {--dry-run : Check what would be updated without applying changes}';

    protected $description = 'Update plugins from GitHub';

    public function handle(GitHubPluginUpdater $updater): int
    {
        $plugin = $this->argument('plugins');
        $all = (bool) $this->option('all');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');
        $enabledOnly = ! $this->option('all-status');

        if (is_string($plugin) && $plugin !== '' && $all) {
            $this->error('Use either a plugins argument or --all, not both.');

            return self::INVALID;
        }

        if (is_string($plugin) && $plugin !== '') {
            try {
                $result = $updater->update($plugin, force: $force, dryRun: $dryRun);
            } catch (Throwable $e) {
                $result = [
                    'plugins' => $plugin,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }

            $this->renderResults([$result]);

            return ($result['status'] ?? null) === 'error' ? self::FAILURE : self::SUCCESS;
        }

        $results = $updater->updateAll(
            force: $force,
            dryRun: $dryRun,
            enabledOnly: $all ? $enabledOnly : true
        );

        if ($results === []) {
            $this->info('No GitHub-managed plugins found.');

            return self::SUCCESS;
        }

        $this->renderResults($results);

        foreach ($results as $result) {
            if (($result['status'] ?? null) === 'error') {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    protected function renderResults(array $results): void
    {
        $rows = [];
        $updated = 0;
        $skipped = 0;
        $pending = 0;
        $errors = 0;

        foreach ($results as $result) {
            $status = (string) ($result['status'] ?? 'unknown');
            if ($status === 'updated') {
                $updated++;
            } elseif ($status === 'skipped') {
                $skipped++;
            } elseif ($status === 'pending') {
                $pending++;
            } elseif ($status === 'error') {
                $errors++;
            }

            $rows[] = [
                'Plugin' => (string) ($result['plugins'] ?? '-'),
                'Status' => $status,
                'Current' => (string) ($result['current_version'] ?? '-'),
                'Remote' => (string) ($result['remote_version'] ?? '-'),
                'Message' => (string) ($result['message'] ?? ''),
            ];
        }

        $this->table(array_keys($rows[0]), $rows);
        $this->line("Updated: {$updated}");
        $this->line("Skipped: {$skipped}");
        $this->line("Pending: {$pending}");

        if ($errors > 0) {
            $this->warn("Errors: {$errors}");
        }
    }
}
