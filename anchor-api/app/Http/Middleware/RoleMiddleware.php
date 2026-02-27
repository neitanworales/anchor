<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
  public function handle($request, Closure $next, ...$roles)
  {
    $role = $request->attributes->get('company_role');
    if (!$role || !in_array($role, $roles, true)) {
      return response()->json(['message' => 'Forbidden'], 403);
    }
    return $next($request);
  }
}