<?php

namespace App\Console\Commands;

use App\Core\Plugins\GitHubPluginWatcher;
use Illuminate\Console\Command;

class PluginsWatchUpdatesCommand extends Command
{
    protected $signature = 'plugins:watch-updates
        {--all : Include disabled plugins as well}';

    protected $description = 'Check GitHub-managed plugins for available updates';

    public function handle(GitHubPluginWatcher $watcher): int
    {
        $results = $watcher->watchAll(enabledOnly: ! $this->option('all'));

        if ($results === []) {
            $this->info('No GitHub-managed plugins found.');

            return self::SUCCESS;
        }

        $rows = [];
        $updates = 0;
        $errors = 0;

        foreach ($results as $result) {
            $status = 'ok';
            $message = '';

            if (isset($result['error'])) {
                $status = 'error';
                $message = (string) $result['error'];
                $errors++;
            } elseif (($result['has_update'] ?? false) === true) {
                $status = 'update';
                $updates++;
            }

            $rows[] = [
                'Plugin' => (string) ($result['plugins'] ?? '-'),
                'Current' => (string) ($result['current_version'] ?? '-'),
                'Remote' => (string) ($result['remote_version'] ?? '-'),
                'Status' => $status,
                'Repository' => (string) ($result['repository'] ?? '-'),
                'Message' => $message,
            ];
        }

        $this->table(array_keys($rows[0]), $rows);
        $this->line("Updates available: {$updates}");

        if ($errors > 0) {
            $this->warn("Errors: {$errors}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
