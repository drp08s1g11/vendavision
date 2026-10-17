<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and apply hardened security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Previne Clickjacking (impedindo embutimento em iframes externos maliciosos)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Impede que navegadores tentem adivinhar o MIME type de arquivos (MIME-sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Previne vazamento de URLs internas em requisições de referência
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Desativa APIs de hardware desnecessárias no painel administrativo
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Proteção contra XSS em navegadores legados
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
