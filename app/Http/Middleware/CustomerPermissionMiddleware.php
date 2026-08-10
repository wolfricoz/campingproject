<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the publicly reachable routes with a permission.
 *
 * The booking funnel has to stay open to visitors without an account, so those
 * routes cannot use the regular `permission` middleware. Instead a guest is
 * judged by the permissions of the `customer` role: the route only stays open
 * as long as that role actually holds the permission. Visitors who are logged
 * in are judged by their own roles, so an employee keeps their own rights.
 */
class CustomerPermissionMiddleware
{
    /**
     * The role a visitor without an account is treated as.
     */
    public const GUEST_ROLE = 'customer';

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        $isAllowed = $user
            ? $user->hasPermissionTo($permission)
            : Role::findByName(self::GUEST_ROLE)->hasPermissionTo($permission, null);

        if (! $isAllowed) {
            abort(403, __('Je hebt geen toegang tot deze pagina.'));
        }

        return $next($request);
    }
}
