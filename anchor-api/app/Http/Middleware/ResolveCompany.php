<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Company;

class ResolveCompany
{
    public function handle(Request $request, Closure $next)
    {
        $companyId = $request->header('X-Company-Id');

        if (!$companyId) {
            return response()->json(['message' => 'X-Company-Id header is required'], 400);
        }

        $company = Company::find($companyId);
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        $user = $request->user();
        if (!$user)
            return response()->json(['message' => 'Unauthorized'], 401);

        $membership = $user->companies()
            ->where('companies.id', $company->id)
            ->wherePivot('is_active', true)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Forbidden (not in this company)'], 403);
        }

        // “inyectamos” tenant al request
        $request->attributes->set('company', $company);
        $request->attributes->set('company_role', $membership->pivot->role);

        return $next($request);
    }
}