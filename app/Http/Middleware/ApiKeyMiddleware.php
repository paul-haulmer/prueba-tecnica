<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Método que valida la API Key antes de procesar la solicitud.
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @return Response
     * @author paul quezada (paul.quezada[at]haulmer.com)
     * @version 2025-10-20
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('services.packages.api_key');
        $providedKey = $request->header('X-API-Key');

        if (! $configuredKey || ! $providedKey || ! hash_equals($configuredKey, (string) $providedKey)) {
            return response()->json([
                'message' => 'Invalid or missing API key.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
