<?php

namespace App\Core\Installer;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

class InstallerService
{
    public function __construct(private readonly EnvWriter $envWriter) {}

    public function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }

    public function testDatabaseConnection(array $config): void
    {
        $driver = $config['db_connection'] ?? 'mysql';
        $connectionName = 'installer_check';

        config([
            "database.connections.{$connectionName}" => [
                'driver' => $driver,
                'host' => $config['db_host'] ?? '127.0.0.1',
                'port' => (string) ($config['db_port'] ?? '3306'),
                'database' => $config['db_database'] ?? '',
                'username' => $config['db_username'] ?? '',
                'password' => $config['db_password'] ?? '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ]);

        DB::purge($connectionName);

        try {
            DB::connection($connectionName)->getPdo();
        } catch (Throwable $e) {
            throw new RuntimeException('Could not connect to the database with the provided credentials.');
        } finally {
            DB::disconnect($connectionName);
            config()->offsetUnset("database.connections.{$connectionName}");
        }
    }

    public function saveDatabaseConfiguration(array $config): void
    {
        $this->envWriter->set([
            'DB_CONNECTION' => $config['db_connection'],
            'DB_HOST' => $config['db_host'],
            'DB_PORT' => (string) $config['db_port'],
            'DB_DATABASE' => $config['db_database'],
            'DB_USERNAME' => $config['db_username'],
            'DB_PASSWORD' => $config['db_password'] ?? '',
        ]);

        config([
            'database.default' => $config['db_connection'],
            "database.connections.{$config['db_connection']}.host" => $config['db_host'],
            "database.connections.{$config['db_connection']}.port" => (string) $config['db_port'],
            "database.connections.{$config['db_connection']}.database" => $config['db_database'],
            "database.connections.{$config['db_connection']}.username" => $config['db_username'],
            "database.connections.{$config['db_connection']}.password" => $config['db_password'] ?? '',
        ]);

        DB::purge($config['db_connection']);
        DB::reconnect($config['db_connection']);
    }

    public function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    public function createSuperAdmin(array $data): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        if (class_exists(Role::class) && Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
            try {
                $role = Role::findOrCreate('super-admin', 'web');
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole($role);
                }
            } catch (Throwable) {
                // Keep installer flow resilient if roles tables are not ready.
            }
        }

        return $user;
    }

    public function markInstalled(): void
    {
        if (! File::isDirectory(storage_path())) {
            File::makeDirectory(storage_path(), 0755, true);
        }

        File::put(storage_path('installed'), now()->toDateTimeString());
    }
}
