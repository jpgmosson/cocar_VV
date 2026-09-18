<?php

namespace App\Http\Middleware;

use App\Enums\TipoUsuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarTipo
{
    /**
     * @param  Closure(): void  $next
     */
    public function handle(Request $request, Closure $next, string ...$tipos): Response
    {
        $tiposPermitidos = array_map(fn ($p) => TipoUsuario::tryFrom($p), $tipos);

        $user = $request->user();
        if (! in_array($user->tipo, $tiposPermitidos, true)) {
            return redirect()
                ->route($user->homeUrl())
                ->with('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }

    public static function sendo(TipoUsuario ...$tipos): string
    {
        return 'tipo:'.implode(',', array_map(fn ($t) => $t->value, $tipos));
    }
}
