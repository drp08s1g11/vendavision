<?php

namespace App\Http\Middleware;

use App\Filament\Pages\Auth\ChangeInitialPassword;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Filament::auth()->user() ?? auth()->user();

        if ($user && $user->must_change_password) {
            $currentRoute = $request->route()?->getName();

            // Rotas permitidas quando a senha ainda não foi alterada
            $allowedRoutes = [
                'filament.admin.pages.redefinir-senha-inicial',
                'filament.admin.auth.logout',
            ];

            if (! in_array($currentRoute, $allowedRoutes, true)) {
                return redirect()->to(ChangeInitialPassword::getUrl());
            }
        }

        return $next($request);
    }
}
