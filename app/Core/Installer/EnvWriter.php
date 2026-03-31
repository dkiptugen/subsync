<?php

namespace App\Core\Installer;

use Illuminate\Support\Facades\File;

class EnvWriter
{
    public function ensureEnvFileExists(): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            File::copy(base_path('.env.example'), $envPath);
        }
    }

    public function set(array $values): void
    {
        $this->ensureEnvFileExists();

        $envPath = base_path('.env');
        $content = File::get($envPath);

        foreach ($values as $key => $value) {
            $escaped = $this->escape((string) $value);
            $pattern = '/^'.preg_quote($key, '/').'=.*/m';
            $line = "{$key}={$escaped}";

            if (preg_match($pattern, $content) === 1) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content .= PHP_EOL.$line;
            }
        }

        File::put($envPath, $content);
    }

    protected function escape(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        if (preg_match('/\s|#|=|"/', $value)) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }
}
