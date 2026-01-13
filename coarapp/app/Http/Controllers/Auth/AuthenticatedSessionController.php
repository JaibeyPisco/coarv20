<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        // Para aplicaciones web, retornar vista
        // Para API, retornar JSON indicando que el login debe hacerse via POST
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Please use POST /login to authenticate',
            ], 405);
        }

        // Si es una petición web, retornar vista si existe, sino retornar respuesta simple
        if (view()->exists('auth.login')) {
            return view('auth.login');
        }

        // Si no hay vista, retornar respuesta simple para tests
        return response('Login page', 200);
    }

    /**
     * Handle an incoming authentication request.
     * Para API: usa tokens de Sanctum en lugar de sesiones.
     * Para Web: usa sesiones y redirige.
     */
    public function store(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        // Si es una petición API, retornar JSON con token
        if ($request->expectsJson() || $request->is('api/*')) {
            $token = $user->createToken('api-token', ['*'])->plainTextToken;

            return response()->json([
                'message' => 'Sesión iniciada correctamente',
                'user' => $user,
                'token' => $token,
            ]);
        }

        // Si es una petición web, usar sesiones y redirigir
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     * Para API: revoca el token actual de Sanctum.
     * Para Web: cierra la sesión y redirige.
     */
    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        // Si es una petición API, revocar token
        if ($request->expectsJson() || $request->is('api/*')) {
            $request->user()->currentAccessToken()?->delete();

            return response()->json([
                'message' => 'Sesión cerrada correctamente',
            ]);
        }

        // Si es una petición web, cerrar sesión y redirigir
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
