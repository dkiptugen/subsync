<?php

namespace App\Http\Controllers;

use App\Core\Plugins\PluginManager;
use App\Traits\Meta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;

class PluginInstallerController extends Controller
{
    use Meta;

    public function __construct(protected array $data = [])
    {
        $this->data = self::site_def();
    }

    public function index(PluginManager $manager): View
    {
        $plugins = collect($manager->all())
            ->map(function (array $plugin) use ($manager): array {
                $plugin['licensed'] = $manager->isLicensed($plugin);

                return $plugin;
            })
            ->sortBy('name')
            ->values()
            ->all();

        return view('modules.plugins.index', [
            'plugins' => $plugins,
        ]);
    }

    public function upload(Request $request, PluginManager $manager): JsonResponse|RedirectResponse
    {
        [$package, $enable] = $this->validatedUploadPayload($request);

        try {
            $plugin = $manager->installFromUploadedArchive($package, $enable);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->errorResponse($request, $e->getMessage());
        }

        $message = $enable
            ? "Plugin [{$plugin['directory']}] uploaded and installed."
            : "Plugin [{$plugin['directory']}] uploaded.";

        return $this->successResponse($request, $message, [
            'data' => $plugin,
        ], 201);
    }

    public function install(Request $request, PluginManager $manager): JsonResponse|RedirectResponse
    {
        $pluginName = $this->validatedPluginName($request);

        try {
            $manager->install($pluginName);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->errorResponse($request, $e->getMessage());
        }

        return $this->successResponse($request, "Plugin [{$pluginName}] installed.");
    }

    public function enable(Request $request, PluginManager $manager): JsonResponse|RedirectResponse
    {
        $pluginName = $this->validatedPluginName($request);

        try {
            $manager->enable($pluginName);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->errorResponse($request, $e->getMessage());
        }

        return $this->successResponse($request, "Plugin [{$pluginName}] enabled.");
    }

    public function disable(Request $request, PluginManager $manager): JsonResponse|RedirectResponse
    {
        $pluginName = $this->validatedPluginName($request);

        try {
            $manager->disable($pluginName);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->errorResponse($request, $e->getMessage());
        }

        return $this->successResponse($request, "Plugin [{$pluginName}] disabled.");
    }

    protected function validatedPluginName(Request $request): string
    {
        try {
            $validated = $request->validate([
                'plugins' => ['required', 'string', 'max:190'],
            ]);
        } catch (ValidationException) {
            throw new InvalidArgumentException('A valid plugins name is required.');
        }

        return $validated['plugins'];
    }

    /**
     * @return array{0: UploadedFile, 1: bool}
     */
    protected function validatedUploadPayload(Request $request): array
    {
        try {
            $validated = $request->validate([
                'package' => ['required', 'file', 'max:102400', 'extensions:zip,tar'],
                'enable' => ['nullable', 'boolean'],
            ]);
        } catch (ValidationException) {
            throw new InvalidArgumentException('A valid ZIP or TAR package is required.');
        }

        return [
            $validated['package'],
            (bool) ($validated['enable'] ?? true),
        ];
    }

    protected function successResponse(
        Request $request,
        string $message,
        array $payload = [],
        int $status = 200,
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(array_merge(['message' => $message], $payload), $status);
        }

        return redirect()
            ->route('dashboard.plugins.index')
            ->with('status', $message);
    }

    protected function errorResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return redirect()
            ->route('dashboard.plugins.index')
            ->withErrors(['plugins' => $message]);
    }
}
