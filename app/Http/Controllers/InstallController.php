<?php

namespace App\Http\Controllers;

use App\Core\Installer\InstallerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Throwable;

class InstallController extends Controller
{
    public function __construct(private readonly InstallerService $installer) {}

    public function welcome()
    {
        return view('install.welcome', [
            'databaseConfigured' => session('installer.database_configured', false),
            'adminConfigured' => session('installer.admin_configured', false),
            'installed' => $this->installer->isInstalled(),
        ]);
    }

    public function database(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'db_connection' => ['required', 'string', 'in:mysql,pgsql,sqlite,sqlsrv'],
            'db_host' => ['nullable', 'string', 'max:255'],
            'db_port' => ['nullable', 'integer'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['nullable', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        $payload['db_host'] = $payload['db_host'] ?? '127.0.0.1';
        $payload['db_port'] = $payload['db_port'] ?? 3306;
        $payload['db_username'] = $payload['db_username'] ?? '';
        $payload['db_password'] = $payload['db_password'] ?? '';

        try {
            $this->installer->testDatabaseConnection($payload);
            $this->installer->saveDatabaseConfiguration($payload);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors([
                'database' => $e->getMessage(),
            ]);
        }

        session(['installer.database_configured' => true]);

        return redirect()->route('install.welcome')->with('status', 'Database configuration saved.');
    }

    public function admin(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            $this->installer->runMigrations();
            $this->installer->createSuperAdmin($payload);
        } catch (Throwable $e) {
            return back()->withInput()->withErrors([
                'admin' => 'Failed to create super admin account.',
            ]);
        }

        session(['installer.admin_configured' => true]);

        return redirect()->route('install.welcome')->with('status', 'Super admin account created.');
    }

    public function finish(): RedirectResponse
    {
        if (! session('installer.database_configured') || ! session('installer.admin_configured')) {
            return redirect()->route('install.welcome')
                ->withErrors(['finish' => 'Complete database and admin steps before finishing installation.']);
        }

        $this->installer->markInstalled();
        session()->forget('installer');

        return redirect('/login')->with('status', 'Installation complete. You can now log in.');
    }
}
