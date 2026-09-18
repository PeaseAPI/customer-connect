<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Services\Company\ApiKeyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function __construct(protected ApiKeyService $apiKeyService) {}

    /**
     * Authenticate via API Key.
     * Supports two methods:
     * 1. Header: X-API-Key: cc_xxxxx
     * 2. Query: ?api_key=cc_xxxxx
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $plainKey = $request->header('X-API-Key')
            ?? $request->query('api_key');

        if (!$plainKey) {
            return response()->json(['message' => 'Missing API Key'], 401);
        }

        $apiKey = $this->apiKeyService->validate($plainKey);

        if (!$apiKey) {
            return response()->json(['message' => 'Invalid or expired API Key'], 401);
        }

        // Check permissions
        foreach ($permissions as $permission) {
            if (!$apiKey->hasPermission($permission)) {
                return response()->json([
                    'message' => "API Key has no access to module [{$permission}]",
                ], 403);
            }
        }

        // Set company context
        $request->attributes->set('company_id', $apiKey->company_id);
        $request->attributes->set('api_key_id', $apiKey->id);

        // Set user context (for audit logs etc.)
        $request->setUserResolver(fn () => $apiKey->user);

        return $next($request);
    }
}
