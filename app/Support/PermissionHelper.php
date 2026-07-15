<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class PermissionHelper
{
    /**
     * @var array<string, string>
     */
    private const ACTION_MAP = [
        'store' => 'create',
        'show' => 'view',
        'index' => 'view',
        'uploadform' => 'create',
        'uploads' => 'create',
        'export' => 'view',
    ];

    public static function canAccess(string $permission, ?Authenticatable $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->can($permission)) {
            return true;
        }

        if ($user->getAllPermissions()->contains('name', $permission)) {
            return true;
        }

        $mapped = self::mapRoutePermission($permission);

        return $mapped !== null && $user->can($mapped);
    }

    public static function mapRoutePermission(string $routePermission): ?string
    {
        $parts = explode('.', $routePermission);

        if (count($parts) < 2) {
            return null;
        }

        $action = end($parts);
        $resourcePart = $parts[count($parts) - 2];

        if (str_contains($action, '_')) {
            $segments = explode('_', $action, 2);

            if (count($segments) === 2) {
                [$resource, $mappedAction] = $segments;
                $mappedAction = self::ACTION_MAP[$mappedAction] ?? $mappedAction;

                return "{$mappedAction}_{$resource}";
            }
        }

        $mappedAction = self::ACTION_MAP[$action] ?? $action;
        $resource = self::singularize($resourcePart);

        return "{$mappedAction}_{$resource}";
    }

    private static function singularize(string $resource): string
    {
        if (str_ends_with($resource, 's') && ! str_ends_with($resource, 'ss')) {
            return rtrim($resource, 's');
        }

        return $resource;
    }
}
